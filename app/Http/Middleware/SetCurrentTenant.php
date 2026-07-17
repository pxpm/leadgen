<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Lead;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

/**
 * Single source of truth for resolving the current tenant from a request.
 *
 * Resolution order:
 *  1. Route model binding ({tenant:slug} or {lead:session_token} → lead → tenant)
 *  2. Authenticated user's tenant
 *
 * Sets the resolved tenant as a singleton in the container so the global
 * tenant() helper and TenantScope can access it anywhere.
 */
class SetCurrentTenant
{
    public function handle(Request $request, Closure $next): mixed
    {
        $tenant = $this->resolveFromRoute($request)
               ?? $this->resolveFromUser($request);

        if ($tenant) {
            app()->instance('current_tenant', $tenant);
        }

        return $next($request);
    }

    /**
     * Resolve tenant from route model binding.
     * Widget API uses {tenant:slug} and {lead:session_token}.
     */
    private function resolveFromRoute(Request $request): ?Tenant
    {
        $tenant = $request->route('tenant');
        if ($tenant instanceof Tenant) {
            return $tenant;
        }

        $lead = $request->route('lead');
        if ($lead instanceof Lead) {
            $lead->loadMissing('tenant');

            return $lead->tenant;
        }

        return null;
    }

    /**
     * Resolve tenant from the authenticated user.
     * Used by Filament admin routes.
     */
    private function resolveFromUser(Request $request): ?Tenant
    {
        $user = $request->user();

        if ($user?->isSuperAdmin()) {
            return null; // super-admins see all tenants
        }

        return $user?->tenant;
    }
}
