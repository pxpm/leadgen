<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\DemoRequest;
use Closure;
use Illuminate\Http\Request;

class LiveModeMiddleware
{
    /**
     * When IS_LIVE_MODE=false, trial signups become demo requests
     * instead of creating real tenant accounts.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (config('app.is_live_mode')) {
            return $next($request);
        }

        // Only intercept trial signup endpoint
        if ($request->is('trial-signup') && $request->isMethod('POST')) {
            return $this->handleAsDemoRequest($request);
        }

        // Also intercept trial completion (post social auth)
        if ($request->is('trial/complete') && $request->isMethod('POST')) {
            return $this->handleAsDemoRequest($request, isSocial: true);
        }

        return $next($request);
    }

    private function handleAsDemoRequest(Request $request, bool $isSocial = false): mixed
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company' => ['required', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:50'],
            'industry_other' => ['nullable', 'string', 'max:100'],
        ]);

        DemoRequest::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'],
            'industry' => $validated['industry_other'] ?? $validated['industry'] ?? 'não especificada',
            'message' => $isSocial
                ? 'Demo request (social auth) — modo de teste.'
                : 'Demo request — modo de teste.',
            'status' => 'new',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'demo' => true], 201);
        }

        return redirect('/')->with('success', 'demo');
    }
}
