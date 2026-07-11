<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileVerifier
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Verify a Turnstile token with Cloudflare.
     * Returns true if the token is valid for a human user.
     */
    public function verify(string $token, ?string $ip = null): bool
    {
        $secret = config('services.turnstile.secret_key');

        if (empty($secret)) {
            // Not configured — skip verification (dev mode)
            return true;
        }

        if (empty($token)) {
            Log::warning('Turnstile: missing token');

            return false;
        }

        try {
            $response = Http::asForm()->post(self::VERIFY_URL, [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $ip ?? request()->ip(),
            ]);

            $data = $response->json();

            if (! ($data['success'] ?? false)) {
                Log::warning('Turnstile: verification failed', [
                    'error_codes' => $data['error-codes'] ?? [],
                    'ip' => $ip,
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Turnstile: verification error', ['error' => $e->getMessage()]);

            // Fail open — don't block real users if Cloudflare is down
            return true;
        }
    }

    /**
     * Check if Turnstile is configured.
     */
    public function isConfigured(): bool
    {
        return ! empty(config('services.turnstile.secret_key'));
    }
}
