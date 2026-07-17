<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Resend\Exceptions\WebhookSignatureVerificationException;
use Resend\WebhookSignature;

class ResendWebhookController extends Controller
{
    /**
     * Handle Resend email event webhooks.
     * Events: email.sent, email.delivered, email.bounced, email.opened, email.clicked, email.complained
     *
     * POST /api/webhooks/resend-events
     */
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $secret = config('services.resend.webhook_secret');

        // Verify webhook signature (Svix-based)
        if ($secret) {
            try {
                WebhookSignature::verify(
                    payload: $payload,
                    headers: [
                        'svix-id' => $request->header('svix-id'),
                        'svix-timestamp' => $request->header('svix-timestamp'),
                        'svix-signature' => $request->header('svix-signature'),
                    ],
                    secret: $secret,
                );
            } catch (WebhookSignatureVerificationException $e) {
                Log::warning('Resend webhook signature verification failed', [
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'error' => 'invalid_signature',
                    'message' => 'Invalid signature.',
                ], 401);
            }
        }

        $event = $request->input('type', 'unknown');
        $data = $request->input('data', []);

        Log::info('Resend webhook event received', [
            'type' => $event,
            'email_id' => $data['id'] ?? null,
            'from' => $data['from'] ?? null,
            'to' => $data['to'] ?? [],
            'subject' => $data['subject'] ?? null,
        ]);

        // Handle specific events
        match ($event) {
            'email.bounced' => $this->handleBounce($data),
            'email.complained' => $this->handleComplaint($data),
            'email.delivered' => $this->handleDelivered($data),
            'email.sent', 'email.opened', 'email.clicked' => null, // log only
            default => Log::debug('Unhandled Resend event type', ['type' => $event]),
        };

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle a bounce — the email was rejected by the recipient's server.
     * We log it; future enhancement: mark the tenant email account as problematic.
     *
     * @param  array<string, mixed>  $data
     */
    private function handleBounce(array $data): void
    {
        $to = $data['to'][0] ?? 'unknown';

        Log::warning('Resend bounce detected', [
            'to' => $to,
            'from' => $data['from'] ?? null,
            'subject' => $data['subject'] ?? null,
        ]);

        // Future: find LeadEmailMessage by Resend email_id and mark as bounced
        // Future: if bounce rate exceeds threshold, flag tenant_email_account
    }

    /**
     * Handle a spam complaint — the recipient marked the email as spam.
     *
     * @param  array<string, mixed>  $data
     */
    private function handleComplaint(array $data): void
    {
        $to = $data['to'][0] ?? 'unknown';

        Log::warning('Resend spam complaint received', [
            'to' => $to,
            'from' => $data['from'] ?? null,
        ]);
    }

    /**
     * Handle a successful delivery.
     *
     * @param  array<string, mixed>  $data
     */
    private function handleDelivered(array $data): void
    {
        Log::info('Resend email delivered', [
            'to' => $data['to'][0] ?? 'unknown',
            'subject' => $data['subject'] ?? null,
        ]);

        // Future: update LeadEmailMessage delivery status
    }
}
