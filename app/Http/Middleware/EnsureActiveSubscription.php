<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access to subscription-gated features when the tenant
 * has no active or trialing subscription. Super-admins bypass.
 *
 * Uses the unified tenant() helper (set by SetCurrentTenant middleware).
 */
class EnsureActiveSubscription
{
    public function __construct(
        private TenantService $tenantService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // tenant() is set by SetCurrentTenant middleware. Fall back to user's
        // tenant when the middleware runs in isolation (tests, CLI, etc.).
        $tenant = tenant() ?? $request->user()?->tenant;

        if (! $tenant || $request->user()?->isSuperAdmin()) {
            return $next($request);
        }

        if (! $this->tenantService->isServiceActive($tenant)) {
            if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
                return response()->json([
                    'error' => 'subscription_required',
                    'message' => 'Subscription required.',
                ], 402);
            }

            return redirect()->route('filament.admin.pages.subscription-inactive');
        }

        return $next($request);
    }
}
