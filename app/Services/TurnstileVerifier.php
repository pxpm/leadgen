<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileVerifier
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * How long (in minutes) a successful verification is considered "fresh"
     * for the circuit breaker. If we haven't reached Cloudflare successfully
     * within this window, the circuit opens and we stop blocking on errors.
     */
    private const CIRCUIT_HEALTH_TTL = 15;

    /**
     * Cache key for the circuit breaker health check.
     */
    private const CIRCUIT_CACHE_KEY = 'turnstile:last_success';

    /**
     * Verify a Turnstile token with Cloudflare.
     *
     * Circuit breaker: successful verifications are cached. If a later call
     * throws an exception while the circuit is healthy (recent success),
     * we fail closed — this is likely an attack attempt, not an outage.
     * If the circuit is unhealthy (no recent success), we still fail closed
     * but log at CRITICAL so ops can investigate.
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

            // Record successful verification for circuit breaker health
            Cache::put(self::CIRCUIT_CACHE_KEY, now()->timestamp, now()->addMinutes(self::CIRCUIT_HEALTH_TTL));

            return true;
        } catch (\Throwable $e) {
            $circuitHealthy = Cache::has(self::CIRCUIT_CACHE_KEY);

            if ($circuitHealthy) {
                // Cloudflare was reachable recently — this exception is suspicious.
                // Fail closed: likely an attacker trying to trigger a bypass.
                Log::warning('Turnstile: verification error while circuit healthy — blocking', [
                    'error' => $e->getMessage(),
                    'ip' => $ip,
                ]);
            } else {
                // No recent successful verification — Cloudflare may genuinely be down.
                // Still fail closed, but log CRITICAL so ops can investigate.
                Log::critical('Turnstile: verification error while circuit unhealthy — possible Cloudflare outage', [
                    'error' => $e->getMessage(),
                    'ip' => $ip,
                ]);
            }

            return false;
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
