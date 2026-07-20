<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LeadStatus;
use App\Jobs\GenerateSummaryJob;
use App\Models\Lead;

class QualificationEngine
{
    public function __construct(private IndustryConfigEngine $config) {}

    /**
     * Determine if a lead is fully qualified (all required fields collected).
     */
    public function isComplete(Lead $lead): bool
    {
        $config = $this->config->resolve($lead->tenant, $lead->services[0] ?? null);
        $requiredFields = $this->resolveRequiredFields($lead, $config);
        $definitions = $config['field_definitions'] ?? [];
        $collected = $lead->fields->pluck('field_value', 'field_key')->toArray();

        foreach ($requiredFields as $field) {
            if (! $this->isFieldCollected($lead, $field, $definitions, $collected)) {
                return false;
            }
        }

        // Contact fields are always required (name, phone, email, address, postal_code)
        foreach ($config['contact_fields'] ?? [] as $field) {
            if (! $this->isFieldCollected($lead, $field, $definitions, $collected)) {
                return false;
            }
        }

        // Check conditional fields (field definitions with a "when" trigger):
        // if triggered and required, they must be collected.
        foreach ($config['field_definitions'] ?? [] as $fieldKey => $def) {
            if (empty($def['when'])) {
                continue;
            }
            if (isset($def['enabled']) && ! $def['enabled']) {
                continue;
            }
            if (! empty($def['required']) && $this->conditionsMatch($def['when'], $collected)) {
                if (! $this->isFieldCollected($lead, $fieldKey, $definitions, $collected)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the list of fields still missing for this lead.
     * Order: service required → optional → conditional → contact LAST.
     */
    public function getMissingFields(Lead $lead): array
    {
        $config = $this->config->resolve($lead->tenant, $lead->services[0] ?? null);
        $requiredFields = $this->resolveRequiredFields($lead, $config);
        $definitions = $config['field_definitions'] ?? [];
        $collected = $lead->fields->pluck('field_value', 'field_key')->toArray();

        // Only count fields that belong to the current service or are shared (null lead_service_id).
        // This prevents service A's fields from being treated as "collected" for service B.
        $currentServiceId = $lead->leadServices()
            ->where('service_key', $lead->services[0] ?? null)
            ->latest('order')
            ->value('id');

        $collectedKeys = $lead->fields()
            ->where(function ($q) use ($currentServiceId) {
                $q->where('lead_service_id', $currentServiceId)
                    ->orWhereNull('lead_service_id');
            })
            ->pluck('field_key')
            ->toArray();

        // Include file fields that have media uploaded as "collected"
        foreach ($definitions as $fKey => $fDef) {
            if (($fDef['type'] ?? null) === 'file' && $lead->getMedia($fKey)->isNotEmpty()) {
                $collectedKeys[] = $fKey;
            }
        }

        $missing = array_values(array_diff($requiredFields, $collectedKeys));

        // Add optional fields that haven't been collected yet
        $optionals = $config['optional_fields'] ?? [];
        foreach ($optionals as $opt) {
            if (! in_array($opt, $collectedKeys) && ! in_array($opt, $missing)) {
                $missing[] = $opt;
            }
        }

        // Add conditional fields (definitions with a "when" trigger) whose conditions are met
        $collected = $lead->fields->pluck('field_value', 'field_key')->toArray();
        foreach ($config['field_definitions'] ?? [] as $fieldKey => $def) {
            if (empty($def['when'])) {
                continue;
            }
            if (isset($def['enabled']) && ! $def['enabled']) {
                continue;
            }
            if (in_array($fieldKey, $collectedKeys) || in_array($fieldKey, $missing)) {
                continue;
            }
            if ($this->conditionsMatch($def['when'], $collected)) {
                $missing[] = $fieldKey;
            }
        }

        // Add contact fields LAST (name, phone, email, address, postal_code)
        $contactFields = $config['contact_fields'] ?? [];
        foreach ($contactFields as $c) {
            if (! in_array($c, $collectedKeys) && ! in_array($c, $missing)) {
                $missing[] = $c;
            }
        }

        return $missing;
    }

    /**
     * Find conditional child fields triggered by a specific parent field's value.
     * Returns field definitions for children whose "when" condition references
     * the parent and whose condition is currently met.
     *
     * @return array<array{key: string, type: string, options?: array, prompt?: string, required?: bool}>
     */
    public function getTriggeredChildren(Lead $lead, string $parentFieldKey): array
    {
        $config = $this->config->resolve($lead->tenant, $lead->services[0] ?? null);
        $definitions = $this->config->getFieldDefinitions($lead->tenant, $lead->services[0] ?? null);
        $locale = $this->config->getLocale($lead->tenant);
        $collected = $lead->fields->pluck('field_value', 'field_key')->toArray();

        $children = [];

        foreach ($config['field_definitions'] ?? [] as $fieldKey => $def) {
            if (empty($def['when'])) {
                continue;
            }
            if (isset($def['enabled']) && ! $def['enabled']) {
                continue;
            }
            // Only consider children whose "when" references the parent
            if (! array_key_exists($parentFieldKey, $def['when'])) {
                continue;
            }
            // Already collected? (check media for file fields, fields table for others)
            if (($definitions[$fieldKey]['type'] ?? null) === 'file') {
                if ($lead->getMedia($fieldKey)->isNotEmpty()) {
                    continue;
                }
            } elseif (isset($collected[$fieldKey]) && $collected[$fieldKey] !== Lead::DECLINED) {
                continue;
            }
            // Declined (skipped)?
            if (isset($collected[$fieldKey]) && $collected[$fieldKey] === Lead::DECLINED) {
                continue;
            }
            // Condition must be met
            if (! $this->conditionsMatch($def['when'], $collected)) {
                continue;
            }

            $child = [
                'key' => $fieldKey,
                'type' => $definitions[$fieldKey]['type'] ?? 'text',
                'prompt' => $config['locales'][$locale]['field_prompts'][$fieldKey] ?? null,
                'required' => ! empty($def['required']),
            ];

            if (($definitions[$fieldKey]['type'] ?? null) === 'select') {
                $child['options'] = collect($definitions[$fieldKey]['options'] ?? [])
                    ->map(fn ($val) => [
                        'value' => $val,
                        'label' => $config['locales'][$locale]['field_options'][$fieldKey][$val] ?? $val,
                    ])->values()->toArray();
                $child['multi'] = $definitions[$fieldKey]['multi'] ?? false;
                $child['has_other'] = in_array('other', $definitions[$fieldKey]['options'] ?? []);
            }

            $children[] = $child;
        }

        return $children;
    }

    /**
     * Get the next field the AI should ask about.
     *
     * When $afterField is provided (the field that was just answered), triggered
     * conditional children of that field are prioritized over the default order.
     * This ensures follow-up questions (e.g. house_type after property_type=house)
     * are asked immediately, before unrelated optional fields.
     */
    public function getNextField(Lead $lead, ?string $afterField = null): ?array
    {
        // If a field was just answered, check for triggered conditional children first.
        // These take priority over the static field order so follow-up questions
        // appear immediately after their parent.
        if ($afterField) {
            $children = $this->getTriggeredChildren($lead, $afterField);
            if (! empty($children)) {
                return $children[0];
            }
        }

        $missing = $this->getMissingFields($lead);
        if (empty($missing)) {
            return null;
        }

        $definitions = $this->config->getFieldDefinitions($lead->tenant, $lead->services[0] ?? null);
        $locale = $this->config->getLocale($lead->tenant);
        $config = $this->config->resolve($lead->tenant, $lead->services[0] ?? null);
        $fieldKey = $missing[0];

        $result = ['key' => $fieldKey, 'type' => $definitions[$fieldKey]['type'] ?? 'text'];
        $result['prompt'] = $config['locales'][$locale]['field_prompts'][$fieldKey] ?? null;
        $result['required'] = in_array($fieldKey, $config['required_fields'] ?? []);

        if (($definitions[$fieldKey]['type'] ?? null) === 'select') {
            $result['options'] = collect($definitions[$fieldKey]['options'])
                ->map(fn ($val) => [
                    'value' => $val,
                    'label' => $config['locales'][$locale]['field_options'][$fieldKey][$val] ?? $val,
                ])->values()->toArray();
            $result['multi'] = $definitions[$fieldKey]['multi'] ?? false;
            $result['has_other'] = in_array('other', $definitions[$fieldKey]['options'] ?? []);
        }

        return $result;
    }

    /**
     * Mark a lead as qualified if complete.
     */
    public function maybeComplete(Lead $lead): void
    {
        if ($this->isComplete($lead) && $lead->status !== LeadStatus::Qualified) {
            $lead->update([
                'status' => LeadStatus::Qualified,
                'qualified_at' => now(),
            ]);

            GenerateSummaryJob::dispatch($lead);
        }
    }

    /**
     * Resolve required fields including conditional rules.
     *
     * Rule format:
     * - "when": {"field": "value"} → exact match (string)
     * - "when": {"field": ["a","b"]} → matches a OR b (array)
     * - Multiple fields in "when" → ALL must match (AND)
     * - Multiple rules in the array → ANY rule can trigger (OR)
     */
    private function resolveRequiredFields(Lead $lead, array $config): array
    {
        $required = $config['required_fields'] ?? [];
        $collected = $lead->fields->pluck('field_value', 'field_key')->toArray();

        foreach ($config['conditional_requirements'] ?? [] as $rule) {
            $allMatch = true;

            foreach ($rule['when'] as $fieldKey => $expected) {
                $actual = $collected[$fieldKey] ?? null;
                $values = (array) $expected; // normalizes "crack" → ["crack"]

                if (! in_array($actual, $values, true)) {
                    $allMatch = false;
                    break;
                }
            }

            if ($allMatch) {
                $required = array_merge($required, $rule['require']);
            }
        }

        return array_unique($required);
    }

    /**
     * Check if a single field has been collected.
     * Regular fields: check lead.fields table (value must be non-null, non-empty, non-declined).
     * File fields: check the Spatie MediaLibrary collection with the same key name.
     *
     * @param  array<string, array>  $definitions
     * @param  array<string, ?string>  $collected
     */
    private function isFieldCollected(Lead $lead, string $fieldKey, array $definitions, array $collected): bool
    {
        $type = $definitions[$fieldKey]['type'] ?? null;

        if ($type === 'file') {
            return $lead->getMedia($fieldKey)->isNotEmpty();
        }

        $value = $collected[$fieldKey] ?? null;

        return $value !== null && $value !== '' && $value !== Lead::DECLINED;
    }

    /**
     * Check if all conditions in a 'when' block are met by collected values.
     *
     * @param  array<string, string|array<string>>  $when
     */
    private function conditionsMatch(array $when, array $collected): bool
    {
        foreach ($when as $fieldKey => $expected) {
            $actual = $collected[$fieldKey] ?? null;
            $values = (array) $expected; // normalize to array

            if (! in_array($actual, $values, true)) {
                return false;
            }
        }

        return true;
    }
}
