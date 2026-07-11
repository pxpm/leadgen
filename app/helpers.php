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
