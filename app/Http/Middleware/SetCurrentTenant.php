<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetCurrentTenant
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if ($user?->tenant_id) {
            app()->instance('current_tenant', $user->tenant);
        }

        return $next($request);
    }
}
