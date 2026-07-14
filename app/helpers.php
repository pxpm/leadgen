<?php

declare(strict_types=1);

use App\Models\Tenant;

if (! function_exists('tenant')) {
    /**
     * Get the current tenant from the application context.
     * Returns null if no tenant is resolved (e.g., in a non-tenant context).
     */
    function tenant(): ?Tenant
    {
        return app()->bound('current_tenant')
            ? app('current_tenant')
            : null;
    }
}

if (! function_exists('industry_url')) {
    /**
     * Build a locale-aware URL for an industry landing page.
     * Uses the current locale's route prefix and industry slug.
     *
     * Example: industry_url('roofing') → '/solucoes-para/telhados' (pt) or '/solutions-for/roofing' (en)
     */
    function industry_url(string $industryKey): string
    {
        $prefix = __('landing.route_prefix');
        $slug = __('landing.industries_section.'.$industryKey.'.slug');

        return url('/'.$prefix.'/'.$slug);
    }
}

if (! function_exists('service_url')) {
    /**
     * Build a locale-aware URL for a service sub-page.
     *
     * Example: service_url('roofing', 'reparacao-telhados') → '/solucoes-para/telhados/reparacao-telhados' (pt)
     */
    function service_url(string $industryKey, string $serviceSlug): string
    {
        $prefix = __('landing.route_prefix');
        $industrySlug = __('landing.industries_section.'.$industryKey.'.slug');

        return url('/'.$prefix.'/'.$industrySlug.'/'.$serviceSlug);
    }
}

if (! function_exists('industries_url')) {
    /**
     * Build a locale-aware URL for the industries overview page.
     *
     * Example: industries_url() → '/industrias' (pt) or '/industries' (en)
     */
    function industries_url(): string
    {
        return url('/'.__('landing.industrias_slug'));
    }
}

if (! function_exists('how_it_works_url')) {
    /**
     * Build a locale-aware URL for the How It Works page.
     *
     * Example: how_it_works_url() → '/como-funciona' (pt) or '/how-it-works' (en)
     */
    function how_it_works_url(): string
    {
        return url('/'.__('landing.how_it_works_slug'));
    }
}
