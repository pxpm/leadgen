<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantTranslation;
use App\Models\TranslationDefault;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    /**
     * Resolve a translation key for a given tenant + locale.
     *
     * Resolution order: tenant_translations → translation_defaults → lang file (via __())
     *
     * @param  array<string, string>  $replace
     */
    public function get(string $key, string $locale, ?Tenant $tenant = null, array $replace = []): mixed
    {
        [$group, $item] = $this->parseKey($key);

        // 1. Tenant override (highest priority)
        if ($tenant) {
            $override = $this->loadTenantOverride($tenant->id, $locale, $group, $item);
            if ($override !== null) {
                return $this->applyReplacements($override, $replace);
            }
        }

        // 2. Database default
        $default = $this->loadDefault($locale, $group, $item);
        if ($default !== null) {
            return $this->applyReplacements($default, $replace);
        }

        // 3. Lang file fallback
        $fileValue = __("{$group}.{$item}", $replace, $locale);

        // __() returns the key back if not found — treat as missing
        if ($fileValue === "{$group}.{$item}") {
            return null;
        }

        return $fileValue;
    }

    /**
     * Check if a translation exists at any level.
     */
    public function has(string $key, string $locale, ?Tenant $tenant = null): bool
    {
        return $this->get($key, $locale, $tenant) !== null;
    }

    /**
     * Load all translations for a group, merged with tenant overrides.
     *
     * @return array<string, mixed>
     */
    public function loadGroup(string $group, string $locale, ?Tenant $tenant = null): array
    {
        $cacheKey = "translations:{$locale}:{$group}";
        $base = Cache::remember($cacheKey, now()->addHour(), function () use ($locale, $group) {
            return TranslationDefault::where('locale', $locale)
                ->where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });

        if ($tenant) {
            $overrides = Cache::remember(
                "translations:{$locale}:{$group}:tenant:{$tenant->id}",
                now()->addMinutes(10),
                function () use ($tenant, $locale, $group) {
                    return TenantTranslation::where('tenant_id', $tenant->id)
                        ->where('locale', $locale)
                        ->where('group', $group)
                        ->pluck('value', 'key')
                        ->toArray();
                }
            );

            $base = array_merge($base, $overrides);
        }

        // Fall back to lang file for any keys not in DB
        $langFile = trans($group, [], $locale);
        if (is_array($langFile)) {
            $base = array_merge($langFile, $base); // DB wins over file
        }

        return $base;
    }

    /**
     * Store or update a default translation.
     */
    public function setDefault(string $locale, string $group, string $key, mixed $value): void
    {
        TranslationDefault::updateOrCreate(
            ['locale' => $locale, 'group' => $group, 'key' => $key],
            ['value' => $value]
        );

        Cache::forget("translations:{$locale}:{$group}");
    }

    /**
     * Store or update a tenant translation override.
     */
    public function setTenantOverride(Tenant $tenant, string $locale, string $group, string $key, mixed $value): void
    {
        TenantTranslation::updateOrCreate(
            ['tenant_id' => $tenant->id, 'locale' => $locale, 'group' => $group, 'key' => $key],
            ['value' => $value]
        );

        Cache::forget("translations:{$locale}:{$group}:tenant:{$tenant->id}");
    }

    /**
     * Remove a tenant override (reverts to default).
     */
    public function removeTenantOverride(Tenant $tenant, string $locale, string $group, string $key): void
    {
        TenantTranslation::where('tenant_id', $tenant->id)
            ->where('locale', $locale)
            ->where('group', $group)
            ->where('key', $key)
            ->delete();

        Cache::forget("translations:{$locale}:{$group}:tenant:{$tenant->id}");
    }

    /**
     * Seed defaults from a PHP lang file.
     *
     * @param  array<string, mixed>  $translations
     */
    public function seedFromFile(string $locale, string $group, array $translations): void
    {
        foreach (Arr::dot($translations) as $key => $value) {
            $this->setDefault($locale, $group, $key, $value);
        }
    }

    // ─── Internals ────────────────────────────────────────────────

    /**
     * @return array{string, string}
     */
    private function parseKey(string $key): array
    {
        $pos = strpos($key, '.');

        if ($pos === false) {
            return ['*', $key];
        }

        return [substr($key, 0, $pos), substr($key, $pos + 1)];
    }

    private function loadTenantOverride(int $tenantId, string $locale, string $group, string $item): mixed
    {
        $data = Cache::remember(
            "translations:{$locale}:{$group}:tenant:{$tenantId}",
            now()->addMinutes(10),
            function () use ($tenantId, $locale, $group) {
                return TenantTranslation::where('tenant_id', $tenantId)
                    ->where('locale', $locale)
                    ->where('group', $group)
                    ->pluck('value', 'key')
                    ->toArray();
            }
        );

        return $data[$item] ?? null;
    }

    private function loadDefault(string $locale, string $group, string $item): mixed
    {
        $data = Cache::remember(
            "translations:{$locale}:{$group}",
            now()->addHour(),
            function () use ($locale, $group) {
                return TranslationDefault::where('locale', $locale)
                    ->where('group', $group)
                    ->pluck('value', 'key')
                    ->toArray();
            }
        );

        return $data[$item] ?? null;
    }

    /**
     * @param  array<string, string>  $replace
     */
    private function applyReplacements(mixed $value, array $replace): mixed
    {
        if (! is_string($value) || empty($replace)) {
            return $value;
        }

        return str_replace(
            array_map(fn (string $k): string => ":{$k}", array_keys($replace)),
            array_values($replace),
            $value
        );
    }
}
