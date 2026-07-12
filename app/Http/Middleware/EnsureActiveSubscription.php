<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Lead;
use App\Models\Tenant;
use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    public function __construct(
        private TenantService $tenantService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        // No tenant context — allow (landing page, etc.)
        if (! $tenant) {
            return $next($request);
        }

        // Check active subscription
        if (! $this->tenantService->isServiceActive($tenant)) {
            if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
                return response()->json([
                    'message' => 'Subscription required.',
                ], 402);
            }

            return redirect()->route('filament.admin.pages.subscription-inactive');
        }

        return $next($request);
    }

    /**
     * Resolve the tenant from the request context.
     * For widget API routes, the tenant comes from the route model binding
     * ({tenant:slug} or {lead:session_token} → lead → tenant).
     * For Filament routes, the tenant comes from the authenticated user.
     */
    private function resolveTenant(Request $request): ?Tenant
    {
        // Widget API: tenant from route parameter
        $tenant = $request->route('tenant');
        if ($tenant instanceof Tenant) {
            return $tenant;
        }

        // Widget API: tenant from lead (via session_token route binding)
        $lead = $request->route('lead');
        if ($lead instanceof Lead) {
            $lead->loadMissing('tenant');

            return $lead->tenant;
        }

        // Filament: tenant from authenticated user
        $user = $request->user();
        if ($user?->isSuperAdmin()) {
            return null; // super-admins always pass
        }

        return $user?->tenant;
    }
}
