<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TurnstileVerifier;
use Closure;
use Illuminate\Http\Request;

class VerifyTurnstile
{
    public function __construct(private TurnstileVerifier $verifier) {}

    /**
     * Verify the Turnstile token on widget write endpoints.
     * Skips verification if Turnstile is not configured (dev mode).
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $this->verifier->isConfigured()) {
            return $next($request);
        }

        $token = $request->header('X-Turnstile-Token')
              ?? $request->input('turnstile_token');

        if (! $this->verifier->verify($token)) {
            return response()->json([
                'error' => 'turnstile_failed',
                'message' => 'Verificação de segurança falhou. Recarregue a página.',
            ], 403);
        }

        return $next($request);
    }
}
