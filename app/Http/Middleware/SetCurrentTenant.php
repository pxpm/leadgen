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
 *  1. Session impersonation (super-admin impersonating a tenant)
 *  2. Route model binding ({tenant:slug} or {lead:session_token} → lead → tenant)
 *  3. Authenticated user's tenant
 *
 * Sets the resolved tenant as a singleton in the container so the global
 * tenant() helper and TenantScope can access it anywhere.
 */
class SetCurrentTenant
{
    public function handle(Request $request, Closure $next): mixed
    {
        $tenant = $this->resolveFromImpersonation($request)
               ?? $this->resolveFromRoute($request)
               ?? $this->resolveFromUser($request);

        if ($tenant) {
            app()->instance('current_tenant', $tenant);
        }

        return $next($request);
    }

    /**
     * Resolve tenant from impersonation cookie.
     * Super-admins can impersonate a tenant to see their data.
     *
     * Uses an encrypted cookie (not session) so impersonation works
     * reliably on Livewire AJAX requests as well as full page loads.
     *
     * Sets the tenant_id on the user model in memory so that all code
     * paths (auth()->user()->tenant_id, auth()->user()->tenant,
     * widgets, Livewire components) see the impersonated tenant.
     */
    private function resolveFromImpersonation(Request $request): ?Tenant
    {
        $tenantId = $request->cookie('impersonating_tenant_id');

        if (! $tenantId) {
            return null;
        }

        $user = $request->user();

        if (! $user?->isSuperAdmin()) {
            return null;
        }

        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            return null;
        }

        // Set tenant_id in memory so all code that reads
        // auth()->user()->tenant_id gets the impersonated tenant.
        $user->tenant_id = $tenant->id;
        $user->setRelation('tenant', $tenant);

        return $tenant;
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
