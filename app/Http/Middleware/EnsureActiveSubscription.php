<?php

declare(strict_types=1);

namespace App\Http\Middleware;

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
        $user = $request->user();

        // Super-admins always pass through
        if ($user?->isSuperAdmin()) {
            return $next($request);
        }

        // If user has no tenant, allow (they might be on a generic page)
        if (! $user?->tenant) {
            return $next($request);
        }

        // Check active subscription
        if (! $this->tenantService->isServiceActive($user->tenant)) {
            // API requests get a 402
            if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
                return response()->json([
                    'message' => 'Subscription required.',
                ], 402);
            }

            // Web requests redirect to inactive subscription page
            return redirect()->route('filament.admin.pages.subscription-inactive');
        }

        return $next($request);
    }
}
