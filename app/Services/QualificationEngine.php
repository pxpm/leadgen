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
        $config = $this->config->resolve($lead->tenant, $lead->service_type);
        $requiredFields = $this->resolveRequiredFields($lead, $config);
        $collected = $lead->fields->pluck('field_value', 'field_key')->toArray();

        foreach ($requiredFields as $field) {
            $value = $collected[$field] ?? null;

            // Missing key or declined/empty value → not complete
            if ($value === null || $value === '' || $value === Lead::DECLINED) {
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
                $condValue = $collected[$fieldKey] ?? null;
                if ($condValue === null || $condValue === '' || $condValue === Lead::DECLINED) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the list of fields still missing for this lead.
     * Includes required fields first, then optional fields — so the AI asks
     * about everything, but only required fields block completion.
     */
    public function getMissingFields(Lead $lead): array
    {
        $config = $this->config->resolve($lead->tenant, $lead->service_type);
        $requiredFields = $this->resolveRequiredFields($lead, $config);

        // Only count fields that belong to the current service or are shared (null lead_service_id).
        // This prevents service A's fields from being treated as "collected" for service B.
        $currentServiceId = $lead->leadServices()
            ->where('service_key', $lead->service_type)
            ->latest('order')
            ->value('id');

        $collectedKeys = $lead->fields()
            ->where(function ($q) use ($currentServiceId) {
                $q->where('lead_service_id', $currentServiceId)
                    ->orWhereNull('lead_service_id');
            })
            ->pluck('field_key')
            ->toArray();

        $missing = array_values(array_diff($requiredFields, $collectedKeys));

        // Add optional fields that haven't been collected yet (asked after requireds)
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

        return $missing;
    }

    /**
     * Get the next field the AI should ask about.
     * Returns the first uncollected field — we control the order, the AI follows.
     */
    public function getNextField(Lead $lead): ?array
    {
        $missing = $this->getMissingFields($lead);
        if (empty($missing)) {
            return null;
        }

        $definitions = $this->config->getFieldDefinitions($lead->tenant, $lead->service_type);
        $locale = $this->config->getLocale($lead->tenant);
        $config = $this->config->resolve($lead->tenant, $lead->service_type);
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
