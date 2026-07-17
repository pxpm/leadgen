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
    public function applyExtracted(Lead $lead, array $extracted, array $config, ?int $leadServiceId = null): array
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

            // Shared fields (contact_name, phone, etc.) belong to the lead, not a specific service.
            $serviceId = $this->isSharedField($key, $config) ? null : $leadServiceId;

            $field = $lead->fields()->updateOrCreate(
                ['field_key' => $key, 'lead_service_id' => $serviceId],
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
    public function smartExtract(Lead $lead, string $userMessage, array $config, string $locale, ?int $leadServiceId = null): array
    {
        $msg = trim($userMessage);
        $wordCount = count(explode(' ', $msg));

        if (empty($msg)) {
            return [];
        }

        $missing = $this->qualification->getMissingFields($lead);
        $definitions = $this->config->getFieldDefinitions($lead->tenant, $lead->services[0] ?? null);
        $options = $config['locales'][$locale]['field_options'] ?? [];

        // The bot tracks which field it's asking via lead.current_field_key — no fragile prompt-matching.
        $bestField = in_array($lead->current_field_key, $missing) ? $lead->current_field_key : null;

        // --- WORD LIMIT (select fields only) ---
        // Skip the limit if the message exactly matches one of the field's option labels
        // (e.g. chip click for a long label like "Emergência — preciso de ajuda agora")
        $fieldDef = $bestField ? ($definitions[$bestField] ?? null) : null;
        $isOptionMatch = $fieldDef && $this->matchesOptionLabel($msg, $options[$bestField] ?? []);
        if (! $isOptionMatch && $fieldDef && $fieldDef['type'] !== 'text' && $wordCount > 5) {
            return [];
        }
        if (! $isOptionMatch && ! $fieldDef && $wordCount > 5) {
            return [];
        }

        // --- AUTO-DETECT EMAIL/PHONE, then fall back to first missing field ---
        if (! $bestField) {
            if (in_array('email', $missing) && $this->isValidEmail($msg)) {
                return $this->applyExtracted($lead, ['email' => ['value' => $msg, 'confidence' => 0.9, 'type' => 'text']], $config, $leadServiceId);
            }
            if (in_array('phone', $missing) && $this->isValidPortuguesePhone($msg)) {
                return $this->applyExtracted($lead, ['phone' => ['value' => $msg, 'confidence' => 0.9, 'type' => 'text']], $config, $leadServiceId);
            }

            // No prompt match and not an auto-detectable type — fall back to the
            // first missing field. Covers custom templates (e.g. name-first activation
            // uses "como te chamas?" instead of the standard field prompt).
            if (! empty($missing)) {
                $bestField = $missing[0];
            } else {
                return [];
            }
        }

        $fieldDef = $definitions[$bestField] ?? null;
        if (! $fieldDef) {
            return [];
        }

        // --- SELECT FIELD MATCHING ---
        if ($fieldDef['type'] === 'select') {
            return $this->extractSelectField($lead, $bestField, $msg, $config, $options, $locale, $leadServiceId);
        }

        // --- TEXT FIELD ---
        return $this->applyExtracted($lead, [
            $bestField => ['value' => $msg, 'confidence' => 0.8, 'type' => $fieldDef['type']],
        ], $config, $leadServiceId);
    }

    // ─── Private helpers ───────────────────────────────────────────

    /**
     * Extract a select-type field value using option matching and synonyms.
     *
     * @return string[] Rejected field keys
     */
    private function extractSelectField(Lead $lead, string $fieldKey, string $msg, array $config, array $options, string $locale, ?int $leadServiceId = null): array
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

            return $this->applyExtracted($lead, $extracted, $config, $leadServiceId);
        }

        // Synonym matching (locale-aware)
        $synonyms = $config['locales'][$locale]['synonyms'][$fieldKey] ?? [];
        foreach ($synonyms as $canonicalValue => $aliases) {
            foreach ($aliases as $alias) {
                if (mb_stripos($msg, mb_strtolower($alias)) !== false) {
                    return $this->applyExtracted($lead, [
                        $fieldKey => ['value' => $canonicalValue, 'confidence' => 0.8, 'type' => 'select'],
                    ], $config, $leadServiceId);
                }
            }
        }

        // No match — still capture raw value
        return $this->applyExtracted($lead, [
            $fieldKey => ['value' => $msg, 'confidence' => 0.7, 'type' => 'select'],
        ], $config, $leadServiceId);
    }

    /**
     * Check if the message is an exact match for one of the field's option labels.
     * Used to bypass the word limit for chip clicks on long labels.
     */
    private function matchesOptionLabel(string $msg, array $options): bool
    {
        $normalized = mb_strtolower(trim($msg));
        foreach ($options as $label) {
            if (mb_strtolower($label) === $normalized) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a field key is shared across all services (contact_name, phone, etc.)
     * vs service-specific (problem_type, roof_type, etc.).
     */
    private function isSharedField(string $key, array $config): bool
    {
        $qualification = $config['shared_fields']['qualification'] ?? [];
        $contact = $config['shared_fields']['contact'] ?? [];

        return in_array($key, $qualification) || in_array($key, $contact);
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
