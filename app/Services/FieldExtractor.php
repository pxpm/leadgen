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
     *
     * @return string[] Rejected field keys (so callers can give validation-failure feedback)
     */
    public function applyExtracted(Lead $lead, array $extracted, array $config): array
    {
        $definitions = $config['field_definitions'] ?? [];
        $rejected = [];

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
                $rejected[] = $key;

                continue;
            }
            if ($key === 'email' && ! $this->isValidEmail($data['value'])) {
                Log::channel('ai')->debug('AI: field rejected (invalid email)', ['key' => $key, 'value' => $data['value']]);
                $rejected[] = $key;

                continue;
            }

            // Locale-specific pattern validation (e.g. postal_code format per country)
            $locale = $lead->tenant->locale ?: 'pt';
            $pattern = config("field_patterns.{$locale}.{$key}");
            if ($pattern && ! preg_match("/{$pattern}/", $data['value'])) {
                Log::channel('ai')->debug('AI: field rejected (pattern mismatch)', ['key' => $key, 'value' => $data['value'], 'pattern' => $pattern]);
                $rejected[] = $key;

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

        return $rejected;
    }

    /**
     * Try to map a short user answer to the field the AI most recently asked about.
     *
     * Handles: auto-detection of phone/email, synonym matching, and per-field-type
     * word limits (select fields limited to 5 words; text fields unlimited).
     *
     * Negative responses ("não tenho") are NOT handled here — the widget provides
     * a "Skip" chip for optional fields, and __skip__ is handled by the orchestrator.
     *
     * @return string[] Rejected field keys
     */
    public function smartExtract(Lead $lead, string $userMessage, array $config, string $locale): array
    {
        $msg = trim($userMessage);
        $wordCount = count(explode(' ', $msg));

        if (empty($msg)) {
            return [];
        }

        $missing = $this->qualification->getMissingFields($lead);
        $definitions = $this->config->getFieldDefinitions($lead->tenant, $lead->service_type);
        $prompts = $config['locales'][$locale]['field_prompts'] ?? [];
        $options = $config['locales'][$locale]['field_options'] ?? [];

        $lastAssistantMsg = $lead->messages()
            ->where('role', 'assistant')
            ->orderBy('created_at', 'desc')
            ->first()?->content;

        // --- MATCH AI QUESTION TO FIELD ---
        $bestField = $this->findBestField($lastAssistantMsg, $missing, $prompts);

        // --- WORD LIMIT (select fields only) ---
        $fieldDef = $bestField ? ($definitions[$bestField] ?? null) : null;
        if ($fieldDef && $fieldDef['type'] !== 'text' && $wordCount > 5) {
            return [];
        }
        if (! $fieldDef && $wordCount > 5) {
            return [];
        }

        // --- AUTO-DETECT EMAIL/PHONE ---
        if (! $bestField) {
            if (in_array('email', $missing) && $this->isValidEmail($msg)) {
                return $this->applyExtracted($lead, ['email' => ['value' => $msg, 'confidence' => 0.9, 'type' => 'text']], $config);
            }
            if (in_array('phone', $missing) && $this->isValidPortuguesePhone($msg)) {
                return $this->applyExtracted($lead, ['phone' => ['value' => $msg, 'confidence' => 0.9, 'type' => 'text']], $config);
            }

            return [];
        }

        $fieldDef = $definitions[$bestField] ?? null;
        if (! $fieldDef) {
            return [];
        }

        // --- SELECT FIELD MATCHING ---
        if ($fieldDef['type'] === 'select') {
            return $this->extractSelectField($lead, $bestField, $msg, $config, $options, $locale);
        }

        // --- TEXT FIELD ---
        return $this->applyExtracted($lead, [
            $bestField => ['value' => $msg, 'confidence' => 0.8, 'type' => $fieldDef['type']],
        ], $config);
    }

    // ─── Private helpers ───────────────────────────────────────────

    /**
     * Find which field the AI's last question is about.
     * Matches the question text against field prompts, or defaults to the
     * only missing field when there's exactly one.
     */
    private function findBestField(?string $lastAssistantMsg, array $missing, array $prompts): ?string
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

        return $bestField;
    }

    /**
     * Extract a select-type field value using option matching and synonyms.
     *
     * @return string[] Rejected field keys
     */
    private function extractSelectField(Lead $lead, string $fieldKey, string $msg, array $config, array $options, string $locale): array
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

            return $this->applyExtracted($lead, $extracted, $config);
        }

        // Synonym matching (locale-aware)
        $synonyms = $config['locales'][$locale]['synonyms'][$fieldKey] ?? [];
        foreach ($synonyms as $canonicalValue => $aliases) {
            foreach ($aliases as $alias) {
                if (mb_stripos($msg, mb_strtolower($alias)) !== false) {
                    return $this->applyExtracted($lead, [
                        $fieldKey => ['value' => $canonicalValue, 'confidence' => 0.8, 'type' => 'select'],
                    ], $config);
                }
            }
        }

        // No match — still capture raw value
        return $this->applyExtracted($lead, [
            $fieldKey => ['value' => $msg, 'confidence' => 0.7, 'type' => 'select'],
        ], $config);
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
