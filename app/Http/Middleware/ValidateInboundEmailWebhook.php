<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Resend\Exceptions\WebhookSignatureVerificationException;
use Resend\WebhookSignature;

class ValidateInboundEmailWebhook
{
    /**
     * Verify the webhook signature for the detected email provider.
     *
     * Supported providers (auto-detected from headers):
     *  - Resend  → Svix signature (svix-id, svix-timestamp, svix-signature)
     *  - Mailgun → HMAC-SHA256 (X-Mailgun-Signature header)
     *
     * Unknown providers are rejected (fail-closed).
     * Set INBOUND_EMAIL_SKIP_VERIFICATION=true for local dev.
     *
     * To add a new provider in the future, add it to detectProvider()
     * and add a private validate{Provider}() method.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Dev escape hatch — never skip in production
        if (config('services.inbound_email.skip_verification', false)) {
            Log::debug('Inbound email webhook verification skipped (dev mode)');

            return $next($request);
        }

        $provider = $this->detectProvider($request);

        if (! $provider) {
            Log::warning('Inbound email webhook: unknown provider — request rejected', [
                'ip' => $request->ip(),
                'headers' => $this->redactHeaders($request),
            ]);

            return response()->json([
                'error' => 'unknown_provider',
                'message' => 'Cannot verify webhook — unknown email provider.',
            ], 401);
        }

        return match ($provider) {
            'resend' => $this->validateResend($request, $next),
            'mailgun' => $this->validateMailgun($request, $next),
            default => $this->reject("Inbound email webhook: unhandled provider '{$provider}'"),
        };
    }

    // ─── Provider detection ─────────────────────────────────────

    /**
     * Detect the email provider from the request headers and payload structure.
     * Returns null if the provider cannot be determined.
     *
     * @return string|null Provider identifier ('resend', 'mailgun', etc.) or null
     */
    private function detectProvider(Request $request): ?string
    {
        // Resend → Svix headers
        if ($request->header('svix-id') && $request->header('svix-signature')) {
            return 'resend';
        }

        // Resend Svix envelope structure (fallback if headers stripped by proxy)
        $data = $request->all();
        if (isset($data['type'], $data['data']) && is_array($data['data'])) {
            return 'resend';
        }

        // Mailgun → X-Mailgun-Signature header
        if ($request->header('X-Mailgun-Signature')) {
            return 'mailgun';
        }

        // Future providers — add detection here:
        // SendGrid → X-Twilio-Email-Event-Webhook-Signature
        // Postmark → X-Postmark-Signature

        return null;
    }

    // ─── Resend (Svix) ──────────────────────────────────────────

    /**
     * Validate a Resend webhook using Svix signature verification.
     */
    private function validateResend(Request $request, Closure $next): mixed
    {
        $secret = config('services.resend.webhook_secret');

        if (empty($secret)) {
            Log::warning('Inbound email webhook: Resend secret not configured — rejecting');

            return $this->reject('Resend webhook secret not configured.');
        }

        try {
            WebhookSignature::verify(
                payload: $request->getContent(),
                headers: [
                    'svix-id' => $request->header('svix-id'),
                    'svix-timestamp' => $request->header('svix-timestamp'),
                    'svix-signature' => $request->header('svix-signature'),
                ],
                secret: $secret,
            );
        } catch (WebhookSignatureVerificationException $e) {
            Log::warning('Inbound email webhook: Resend signature verification failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return $this->reject('Invalid Resend webhook signature.');
        }

        return $next($request);
    }

    // ─── Mailgun ────────────────────────────────────────────────

    /**
     * Validate a Mailgun webhook using HMAC-SHA256.
     *
     * Mailgun signs with: hash_hmac('sha256', $timestamp . $token, $signingKey)
     */
    private function validateMailgun(Request $request, Closure $next): mixed
    {
        $signingKey = config('services.mailgun.webhook_signing_key');

        if (empty($signingKey)) {
            Log::warning('Inbound email webhook: Mailgun signing key not configured — rejecting');

            return $this->reject('Mailgun webhook signing key not configured.');
        }

        $signature = $request->header('X-Mailgun-Signature');
        $token = $request->input('token');
        $timestamp = $request->input('timestamp');

        if (empty($signature) || empty($token) || empty($timestamp)) {
            Log::warning('Inbound email webhook: Mailgun — missing signature components', [
                'has_signature' => ! empty($signature),
                'has_token' => ! empty($token),
                'has_timestamp' => ! empty($timestamp),
            ]);

            return $this->reject('Missing Mailgun webhook signature components.');
        }

        // Mailgun signature = HMAC-SHA256 of timestamp + token
        $expected = hash_hmac('sha256', $timestamp.$token, $signingKey);

        if (! hash_equals($expected, $signature)) {
            Log::warning('Inbound email webhook: Mailgun signature verification failed', [
                'ip' => $request->ip(),
            ]);

            return $this->reject('Invalid Mailgun webhook signature.');
        }

        return $next($request);
    }

    // ─── Helpers ────────────────────────────────────────────────

    /**
     * Return a consistent rejection response and bail.
     */
    private function reject(string $logMessage): mixed
    {
        Log::warning($logMessage);

        return response()->json([
            'error' => 'invalid_signature',
            'message' => 'Webhook signature verification failed.',
        ], 401);
    }

    /**
     * Redact sensitive header values for logging.
     *
     * @return array<string, string>
     */
    private function redactHeaders(Request $request): array
    {
        return collect($request->headers->all())
            ->map(fn ($v) => is_array($v) ? $v[0] : $v)
            ->only(['svix-id', 'svix-timestamp', 'content-type', 'user-agent'])
            ->toArray();
    }
}
