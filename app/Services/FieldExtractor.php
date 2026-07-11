<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\Log;

/**
 * Extracts structured field values from user messages.
 *
 * Handles: short-answer mapping, negative response detection,
 * auto-detection of phone/email, garbage overwrite, synonym matching,
 * compound extraction (e.g. "moradia isolada" → property_type + house_type),
 * and per-field-type word limits.
 */
class FieldExtractor
{
    public function __construct(
        private IndustryConfigEngine $config,
        private QualificationEngine $qualification,
    ) {}

    /**
     * Store extracted fields on the lead. Validates phone and email before storing.
     */
    public function applyExtracted(Lead $lead, array $extracted, array $config): void
    {
        $definitions = $config['field_definitions'] ?? [];

        foreach ($extracted as $key => $data) {
            if (empty($data['value'])) {
                continue;
            }

            // Guard: only allow keys that exist in the resolved field definitions.
            // AI extraction runs against a prompt but could hallucinate arbitrary keys.
            if (! isset($definitions[$key])) {
                Log::channel('ai')->warning('AI: field rejected (unknown key)', ['key' => $key]);

                continue;
            }

            if ($key === 'phone' && ! $this->isValidPortuguesePhone($data['value'])) {
                Log::channel('ai')->debug('AI: field rejected (invalid phone)', ['key' => $key, 'value' => $data['value']]);

                continue;
            }
            if ($key === 'email' && ! $this->isValidEmail($data['value'])) {
                Log::channel('ai')->debug('AI: field rejected (invalid email)', ['key' => $key, 'value' => $data['value']]);

                continue;
            }

            // Generic pattern validation (e.g. postal_code: ^\\d{4}-\\d{3}$)
            $pattern = $definitions[$key]['pattern'] ?? null;
            if ($pattern && ! preg_match("/{$pattern}/", $data['value'])) {
                Log::channel('ai')->debug('AI: field rejected (pattern mismatch)', ['key' => $key, 'value' => $data['value'], 'pattern' => $pattern]);

                continue;
            }

            $field = $lead->fields()->updateOrCreate(
                ['field_key' => $key],
                [
                    'field_type' => $data['type'],
                    'field_value' => $data['value'],
                    'confidence' => $data['confidence'],
                    'is_required' => in_array($key, $config['required_fields'] ?? []),
                ]
            );

            Log::channel('ai')->debug('AI: field extracted', ['key' => $key, 'value' => $data['value'], 'confidence' => $data['confidence']]);

            // Sync the in-memory relationship so subsequent reads (buildReply, etc.)
            // see the new field without an extra DB query.
            if ($lead->relationLoaded('fields')) {
                $index = $lead->fields->search(fn ($f) => $f->field_key === $key);
                if ($index !== false) {
                    $lead->fields[$index] = $field;
                } else {
                    $lead->fields->push($field);
                }
            }
        }
    }

    /**
     * Try to map a short user answer to the field the AI most recently asked about.
     *
     * Handles: compound answers, negative response detection, auto-detection
     * of phone/email, garbage overwrite, synonym matching, and per-field-type
     * word limits (select fields limited to 5 words; text fields unlimited).
     */
    public function smartExtract(Lead $lead, string $userMessage, array $config, string $locale): void
    {
        $msg = trim($userMessage);
        $msgLower = mb_strtolower($msg);
        $wordCount = count(explode(' ', $msg));

        if (empty($msg)) {
            return;
        }

        $missing = $this->qualification->getMissingFields($lead);
        $definitions = $this->config->getFieldDefinitions($lead->tenant, $lead->service_type);
        $prompts = $config['locales'][$locale]['field_prompts'] ?? [];
        $options = $config['locales'][$locale]['field_options'] ?? [];

        $lastAssistantMsg = $lead->messages()
            ->where('role', 'assistant')
            ->orderBy('created_at', 'desc')
            ->first()?->content;

        // --- NEGATIVE RESPONSE DETECTION ---
        if ($this->handleNegativeResponse($lead, $msg, $msgLower, $lastAssistantMsg, $missing, $prompts, $config)) {
            return;
        }

        // --- MATCH AI QUESTION TO FIELD ---
        $bestField = $this->findBestField($lastAssistantMsg, $missing, $prompts, $lead, $definitions);

        // --- WORD LIMIT (select fields only) ---
        $fieldDef = $bestField ? ($definitions[$bestField] ?? null) : null;
        if ($fieldDef && $fieldDef['type'] !== 'text' && $wordCount > 5) {
            return;
        }
        if (! $fieldDef && $wordCount > 5) {
            return;
        }

        // --- GARBAGE OVERWRITE ---
        $bestField = $this->tryGarbageOverwrite($lead, $bestField, $lastAssistantMsg, $prompts);

        // --- AUTO-DETECT EMAIL/PHONE ---
        if (! $bestField) {
            if (in_array('email', $missing) && $this->isValidEmail($msg)) {
                $this->applyExtracted($lead, ['email' => ['value' => $msg, 'confidence' => 0.9, 'type' => 'text']], $config);

                return;
            }
            if (in_array('phone', $missing) && $this->isValidPortuguesePhone($msg)) {
                $this->applyExtracted($lead, ['phone' => ['value' => $msg, 'confidence' => 0.9, 'type' => 'text']], $config);

                return;
            }

            return;
        }

        $fieldDef = $definitions[$bestField] ?? null;
        if (! $fieldDef) {
            return;
        }

        // --- SELECT FIELD MATCHING ---
        if ($fieldDef['type'] === 'select') {
            $this->extractSelectField($lead, $bestField, $msg, $config, $options, $locale);

            return;
        }

        // --- TEXT FIELD ---
        $this->applyExtracted($lead, [
            $bestField => ['value' => $msg, 'confidence' => 0.8, 'type' => $fieldDef['type']],
        ], $config);
    }

    // ─── Private helpers ───────────────────────────────────────────

    /**
     * Detect and handle negative/deflection responses.
     * Returns true if message was handled as negative (caller should return).
     */
    private function handleNegativeResponse(
        Lead $lead, string $msg, string $msgLower,
        ?string $lastAssistantMsg, array &$missing, array $prompts, array $config
    ): bool {
        $negativePatterns = [
            '/\bnão\s+tenho\b/', '/\bnão\s+sei\b/', '/\bnada\b/', '/\bnenhum\b/',
            '/\bnão\b.*\bobrigado\b/', '/\bnão\b.*\btelefone\b/',
            '/\bn\b.*\btenho\b/', '/\bnao\s+tenho\b/', '/\bnao\s+sei\b/',
            '/\bpode\s+ser\b/', '/\bposso\s+dar\b/', '/\bprefere\b/',
        ];

        $isNegative = false;
        foreach ($negativePatterns as $pattern) {
            if (preg_match($pattern, $msgLower)) {
                $isNegative = true;
                break;
            }
        }

        // Guard: don't treat address-like messages as negative
        if ($isNegative && $this->looksLikeAddress($msg)) {
            $isNegative = false;
        }

        if (! $isNegative || ! $lastAssistantMsg) {
            return false;
        }

        foreach (array_merge($missing, $lead->fields->pluck('field_key')->toArray()) as $fieldKey) {
            $prompt = $prompts[$fieldKey] ?? '';
            if (! $prompt || mb_stripos($lastAssistantMsg, mb_substr($prompt, 0, 20)) === false) {
                continue;
            }

            $existing = $lead->fields()->where('field_key', $fieldKey)->first();

            // Path A: field exists with garbage → delete and re-ask
            if ($existing && $this->isGarbageValue($existing->field_value, $fieldKey)) {
                $oldValue = $existing->field_value;
                $existing->delete();
                $lead->unsetRelation('fields');
                $missing = $this->qualification->getMissingFields($lead);
                Log::channel('ai')->debug('AI: cleared garbage', ['key' => $fieldKey, 'old_value' => $oldValue]);
                break;
            }

            // Path B: field was never provided → store sentinel so we move on
            if (! $existing) {
                $field = $lead->fields()->create([
                    'field_key' => $fieldKey,
                    'field_type' => 'text',
                    'field_value' => '__declined__',
                    'confidence' => 0.0,
                    'is_required' => in_array($fieldKey, $config['required_fields'] ?? []),
                ]);

                if ($lead->relationLoaded('fields')) {
                    $lead->fields->push($field);
                }

                $missing = $this->qualification->getMissingFields($lead);
                Log::channel('ai')->debug('AI: field declined by user', ['key' => $fieldKey]);

                break;
            }

            break;
        }

        return true;
    }

    /**
     * Find which field the AI's last question is about.
     */
    private function findBestField(?string $lastAssistantMsg, array $missing, array $prompts, Lead $lead, array $definitions): ?string
    {
        $bestField = null;

        if ($lastAssistantMsg) {
            foreach ($missing as $fieldKey) {
                $prompt = $prompts[$fieldKey] ?? '';
                if ($prompt && mb_stripos($lastAssistantMsg, mb_substr($prompt, 0, 20)) !== false) {
                    $bestField = $fieldKey;
                    break;
                }
            }
        }

        if (! $bestField && count($missing) === 1) {
            return $missing[0];
        }

        // Garbage overwrite check
        if (! $bestField && $lastAssistantMsg) {
            foreach ($lead->fields as $existingField) {
                $fk = $existingField->field_key;
                $prompt = $prompts[$fk] ?? '';
                if (! $prompt || mb_stripos($lastAssistantMsg, mb_substr($prompt, 0, 20)) === false) {
                    continue;
                }
                if ($this->isGarbageValue($existingField->field_value, $fk)) {
                    $existingField->delete();
                    $lead->unsetRelation('fields');
                    $bestField = $fk;
                    Log::channel('ai')->debug('AI: overwriting garbage', ['key' => $fk, 'old' => $existingField->field_value]);
                    break;
                }
            }
        }

        return $bestField;
    }

    /**
     * Try to overwrite a field that has garbage data (previously set but clearly wrong).
     */
    private function tryGarbageOverwrite(Lead $lead, ?string $bestField, ?string $lastAssistantMsg, array $prompts): ?string
    {
        if ($bestField || ! $lastAssistantMsg) {
            return $bestField;
        }

        foreach ($lead->fields as $existingField) {
            $fk = $existingField->field_key;
            $prompt = $prompts[$fk] ?? '';
            if (! $prompt || mb_stripos($lastAssistantMsg, mb_substr($prompt, 0, 20)) === false) {
                continue;
            }
            if ($this->isGarbageValue($existingField->field_value, $fk)) {
                $existingField->delete();
                $lead->unsetRelation('fields');

                return $fk;
            }
        }

        return null;
    }

    /**
     * Extract a select-type field value using option matching and synonyms.
     */
    private function extractSelectField(Lead $lead, string $fieldKey, string $msg, array $config, array $options, string $locale): void
    {
        $matchedValue = null;

        // Exact match
        foreach (($options[$fieldKey] ?? []) as $value => $label) {
            if (mb_strtolower($msg) === mb_strtolower($label) || mb_strtolower($msg) === mb_strtolower($value)) {
                $matchedValue = $value;
                break;
            }
        }

        // Partial match
        if (! $matchedValue) {
            foreach (($options[$fieldKey] ?? []) as $value => $label) {
                if (mb_stripos($msg, mb_strtolower($label)) !== false
                    || mb_stripos($msg, mb_strtolower($value)) !== false) {
                    $matchedValue = $value;
                    break;
                }
            }
        }

        if ($matchedValue) {
            $extracted = [$fieldKey => ['value' => $matchedValue, 'confidence' => 0.85, 'type' => 'select']];

            // Compound: property_type=house → also extract house_type
            if ($fieldKey === 'property_type' && $matchedValue === 'house') {
                $houseTypeOptions = $options['house_type'] ?? [];
                foreach ($houseTypeOptions as $hv => $hl) {
                    if (mb_stripos($msg, mb_strtolower($hl)) !== false || mb_stripos($msg, mb_strtolower($hv)) !== false) {
                        $extracted['house_type'] = ['value' => $hv, 'confidence' => 0.85, 'type' => 'select'];
                        break;
                    }
                }
            }

            $this->applyExtracted($lead, $extracted, $config);

            return;
        }

        // Synonym matching (locale-aware)
        $synonyms = $config['locales'][$locale]['synonyms'][$fieldKey] ?? [];
        foreach ($synonyms as $canonicalValue => $aliases) {
            foreach ($aliases as $alias) {
                if (mb_stripos($msg, mb_strtolower($alias)) !== false) {
                    $this->applyExtracted($lead, [
                        $fieldKey => ['value' => $canonicalValue, 'confidence' => 0.8, 'type' => 'select'],
                    ], $config);

                    return;
                }
            }
        }

        // No match — still capture raw value
        $this->applyExtracted($lead, [
            $fieldKey => ['value' => $msg, 'confidence' => 0.7, 'type' => 'select'],
        ], $config);
    }

    /**
     * Check if a message contains address-like keywords.
     */
    private function looksLikeAddress(string $msg): bool
    {
        return (bool) preg_match(
            '/\b(?:rua|avenida|travessa|praça|praca|estrada|largo|alameda|n[º°]|numero|lote|andar|fração|fracao|código\s+postal|cep)\b/i',
            $msg
        );
    }

    /**
     * Check if a stored field value looks like garbage (negative, too short, wrong format).
     */
    private function isGarbageValue(string $value, string $fieldKey): bool
    {
        $lower = mb_strtolower($value);

        if (preg_match('/(não|nao)\s+(tenho|sei)|nada|nenhum/', $lower)) {
            return true;
        }

        if ($fieldKey === 'phone' && ! preg_match('/^\d/', $value)) {
            return true;
        }

        if ($fieldKey === 'email' && ! str_contains($value, '@')) {
            return true;
        }

        return strlen($value) < 2;
    }

    /**
     * Validate a Portuguese phone number (+351 optional, 9[1236] + 7 digits).
     */
    public function isValidPortuguesePhone(string $value): bool
    {
        $clean = preg_replace('/[\s\-]/', '', trim($value));

        return (bool) preg_match('/^(\+351)?9[1236]\d{7}$/', $clean);
    }

    /**
     * Validate an email address.
     */
    public function isValidEmail(string $value): bool
    {
        return filter_var(trim($value), FILTER_VALIDATE_EMAIL) !== false;
    }
}
