<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;

class IndustryConfigEngine
{
    /**
     * Resolve the effective config for a tenant + optional service.
     * Merges: Industry base → Service config → Tenant overrides (_global + per-service).
     */
    public function resolve(Tenant $tenant, ?string $serviceType = null): array
    {
        $base = $this->loadIndustryBase($tenant);
        $serviceConfig = $tenant->service_config ?? [];
        $locale = $tenant->locale ?: ($base['default_locale'] ?? 'pt');

        // Merge service config if a service is selected
        if ($serviceType) {
            $service = $this->loadServiceConfig($serviceType);
            if ($service) {
                $base = $this->mergeService($base, $service, $locale);
            }
        }

        // Apply tenant overrides
        $overrides = $this->mergeTenantOverrides(
            $serviceConfig['_global'] ?? [],
            $serviceType ? ($serviceConfig[$serviceType] ?? []) : []
        );

        if (! empty($overrides['required_fields'])) {
            $base['required_fields'] = $overrides['required_fields'];
        }
        if (! empty($overrides['optional_fields'])) {
            $base['optional_fields'] = $overrides['optional_fields'];
        }
        if (! empty($overrides['greeting_message'])) {
            $base['locales'][$locale]['ai_prompt']['greeting_message'] = $overrides['greeting_message'];
        }
        if (! empty($overrides['conditional_requirements'])) {
            $base['conditional_requirements'] = array_merge(
                $base['conditional_requirements'] ?? [],
                $overrides['conditional_requirements']
            );
        }
        if (! empty($overrides['field_prompts'])) {
            foreach ($overrides['field_prompts'] as $key => $prompt) {
                $base['locales'][$locale]['field_prompts'][$key] = $prompt;
            }
        }
        if (! empty($overrides['conditional_fields'])) {
            foreach ($overrides['conditional_fields'] as $key => $override) {
                if (isset($base['conditional_fields'][$key])) {
                    $base['conditional_fields'][$key] = array_merge(
                        $base['conditional_fields'][$key],
                        $override
                    );
                }
            }
        }
        if (! empty($overrides['field_options'])) {
            foreach ($overrides['field_options'] as $fieldKey => $options) {
                $base['locales'][$locale]['field_options'][$fieldKey] = $options;
            }
        }

        // Apply qualification_overrides (global tenant-level overrides)
        $qualOverrides = $tenant->qualification_overrides ?? [];
        if (! empty($qualOverrides['greeting_message'])) {
            $base['locales'][$locale]['ai_prompt']['greeting_message'] = $qualOverrides['greeting_message'];
        }
        if (! empty($qualOverrides['additional_required_fields'])) {
            $base['required_fields'] = array_merge(
                $base['required_fields'] ?? [],
                $qualOverrides['additional_required_fields']
            );
        }

        return $base;
    }

    /**
     * Merge _global and per-service tenant overrides. Per-service wins.
     */
    private function mergeTenantOverrides(array $global, array $perService): array
    {
        $merged = $global;

        foreach (['required_fields', 'optional_fields', 'greeting_message', 'field_prompts', 'conditional_fields', 'field_options'] as $key) {
            if (! empty($perService[$key])) {
                $merged[$key] = $perService[$key];
            }
        }

        // conditional_requirements: merge, not replace
        if (! empty($perService['conditional_requirements'])) {
            $merged['conditional_requirements'] = array_merge(
                $merged['conditional_requirements'] ?? [],
                $perService['conditional_requirements']
            );
        }

        return $merged;
    }

    /**
     * Get the list of services enabled for this tenant.
     * Only returns services that belong to the tenant's industry.
     *
     * @return array<int, array{key:string, name:string, icon:string}>
     */
    public function getAvailableServices(Tenant $tenant): array
    {
        $enabled = $tenant->active_services ?? [];
        $allServices = $this->allServiceKeys($tenant);
        $locale = $tenant->locale ?: 'pt';
        $result = [];

        foreach ($allServices as $key) {
            if (in_array($key, $enabled)) {
                $config = $this->loadServiceConfig($key);
                if ($config) {
                    $result[] = [
                        'key' => $config['key'] ?? $key,
                        'name' => $config['locales'][$locale]['name'] ?? $config['key'] ?? $key,
                        'icon' => $config['icon'] ?? '🔧',
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Get all service keys available for a tenant's industry.
     * When no tenant is given, returns all service files on disk (discovery fallback).
     *
     * @return list<string>
     */
    public function allServiceKeys(?Tenant $tenant = null): array
    {
        if ($tenant) {
            $base = $this->loadIndustryBase($tenant);

            return $base['services'] ?? [];
        }

        // Fallback: discover all .php files in the services directory
        $path = database_path('seeders/data/industries/services');
        $files = glob($path.'/*.php');

        return array_map(fn (string $file): string => basename($file, '.php'), $files);
    }

    /**
     * Load a service config by key. Returns null if service not found.
     */
    public function loadServiceConfig(string $key): ?array
    {
        $path = database_path("seeders/data/industries/services/{$key}.php");

        if (! file_exists($path)) {
            return null;
        }

        return require $path;
    }

    public function getLocale(Tenant $tenant): string
    {
        $base = $this->loadIndustryBase($tenant);

        return $tenant->locale ?: ($base['default_locale'] ?? 'pt');
    }

    /**
     * Get field definitions merged from base + service, including conditional fields.
     */
    public function getFieldDefinitions(Tenant $tenant, ?string $serviceType = null): array
    {
        $config = $this->resolve($tenant, $serviceType);

        return array_merge(
            $config['field_definitions'] ?? [],
            $config['conditional_fields'] ?? []
        );
    }

    /**
     * Load the industry base config from the tenant's industry record.
     */
    public function loadIndustryBase(Tenant $tenant): array
    {
        $tenant->loadMissing('industry');

        return $tenant->industry->config;
    }

    /**
     * Merge a service config into the base config.
     */
    private function mergeService(array $base, array $service, string $locale): array
    {
        // Merge required/optional fields
        $serviceRequired = $service['required_fields'] ?? [];
        $serviceOptional = $service['optional_fields'] ?? [];
        $baseRequired = $base['shared_fields']['required'] ?? [];
        $baseOptional = $base['shared_fields']['optional'] ?? [];

        $base['required_fields'] = array_merge($serviceRequired, $baseRequired);
        $base['optional_fields'] = array_merge($serviceOptional, $baseOptional);

        // Merge field definitions
        $base['field_definitions'] = array_merge(
            $base['field_definitions'] ?? [],
            $service['field_definitions'] ?? []
        );

        // Merge conditional requirements
        $base['conditional_requirements'] = $service['conditional_requirements'] ?? [];

        // Merge conditional fields
        $base['conditional_fields'] = array_merge(
            $base['conditional_fields'] ?? [],
            $service['conditional_fields'] ?? []
        );

        // Merge locale-aware service data into the base's locale block
        $svcLocale = $service['locales'][$locale] ?? [];

        // AI prompt
        if (! empty($svcLocale['ai_prompt'])) {
            $base['locales'][$locale]['ai_prompt'] = array_merge(
                $base['locales'][$locale]['ai_prompt'] ?? [],
                $svcLocale['ai_prompt']
            );
        }

        // Synonyms
        if (! empty($svcLocale['synonyms'])) {
            $base['locales'][$locale]['synonyms'] = $svcLocale['synonyms'];
        }

        // Field prompts
        if (! empty($svcLocale['field_prompts'])) {
            $base['locales'][$locale]['field_prompts'] = array_merge(
                $base['locales'][$locale]['field_prompts'] ?? [],
                $svcLocale['field_prompts']
            );
        }

        // Field options
        if (! empty($svcLocale['field_options'])) {
            $base['locales'][$locale]['field_options'] = array_merge(
                $base['locales'][$locale]['field_options'] ?? [],
                $svcLocale['field_options']
            );
        }

        // Carry over service key and display name for reference
        $base['service_key'] = $service['key'] ?? null;
        $base['service_name'] = $svcLocale['name'] ?? $service['key'] ?? null;

        return $base;
    }
}
