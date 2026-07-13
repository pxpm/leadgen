<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessIncomingEmailJob;
use App\Services\TenantResolutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InboundEmailController extends Controller
{
    /**
     * Handle inbound email webhook from mail provider (Resend, Mailgun, etc.).
     * POST /api/webhooks/inbound-email
     */
    public function __invoke(Request $request, TenantResolutionService $resolver): JsonResponse
    {
        // Normalize the payload — providers have different formats
        $envelope = $this->normalizePayload($request);

        if (! $envelope) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $tenant = $resolver->resolve([
            'from' => $envelope['from'],
            'to' => $envelope['to'] ?? [],
            'cc' => $envelope['cc'] ?? [],
        ]);

        if (! $tenant) {
            Log::warning('Inbound email rejected — no tenant identified', [
                'from' => $envelope['from'] ?? 'unknown',
            ]);

            return response()->json(['error' => 'No tenant identified'], 422);
        }

        // Dispatch to the same pipeline as IMAP, with tenant context
        ProcessIncomingEmailJob::dispatchFromWebhook(
            tenant: $tenant,
            messageData: [
                'from_address' => $envelope['from'],
                'from_name' => $envelope['from_name'] ?? null,
                'to' => $envelope['to'] ?? [],
                'cc' => $envelope['cc'] ?? [],
                'subject' => $envelope['subject'] ?? '(sem assunto)',
                'body_text' => $envelope['body_text'] ?? null,
                'headers' => $envelope['headers'] ?? [],
                'message_id' => $envelope['message_id'] ?? null,
                'in_reply_to' => $envelope['in_reply_to'] ?? null,
                'references' => $envelope['references'] ?? null,
                'received_at' => $envelope['received_at'] ?? now()->toDateTimeString(),
            ],
        );

        return response()->json(['status' => 'queued']);
    }

    /**
     * Normalize the webhook payload from different mail providers.
     * Supports Resend format natively; extend for Mailgun/SendGrid as needed.
     *
     * @return array<string, mixed>|null
     */
    private function normalizePayload(Request $request): ?array
    {
        $data = $request->all();

        // Resend format: { from, to, cc, subject, html, text, headers }
        if (isset($data['from']) && isset($data['subject'])) {
            return [
                'from' => $data['from'],
                'from_name' => $data['from_name'] ?? null,
                'to' => $this->parseAddressList($data['to'] ?? []),
                'cc' => $this->parseAddressList($data['cc'] ?? []),
                'subject' => $data['subject'],
                'body_text' => $this->stripHtml($data['text'] ?? $data['body_text'] ?? $data['body-plain'] ?? $data['stripped-text'] ?? null),
                'headers' => $data['headers'] ?? $data['message-headers'] ?? [],
                'message_id' => $data['headers']['message_id'] ?? $data['message_id'] ?? null,
                'in_reply_to' => $data['headers']['in_reply_to'] ?? $data['in_reply_to'] ?? null,
                'references' => $data['headers']['references'] ?? $data['references'] ?? null,
                'received_at' => $data['received_at'] ?? $data['created_at'] ?? now()->toDateTimeString(),
            ];
        }

        // Mailgun format: { recipient, sender, subject, body-plain, body-html, ... }
        if (isset($data['sender']) && isset($data['subject'])) {
            return [
                'from' => $data['sender'],
                'from_name' => $data['from_name'] ?? null,
                'to' => [$data['recipient'] ?? ''],
                'cc' => $this->parseAddressList($data['cc'] ?? $data['Cc'] ?? []),
                'subject' => $data['subject'],
                'body_text' => $this->stripHtml($data['body-plain'] ?? $data['stripped-text'] ?? null),
                'headers' => $data['message-headers'] ?? [],
                'message_id' => $data['Message-Id'] ?? $data['message-id'] ?? null,
                'in_reply_to' => $data['In-Reply-To'] ?? null,
                'references' => $data['References'] ?? null,
                'received_at' => $data['timestamp'] ? date('Y-m-d H:i:s', (int) $data['timestamp']) : now()->toDateTimeString(),
            ];
        }

        return null;
    }

    /**
     * Parse a list of email addresses from various formats.
     *
     * @param  array<int, string>|string  $input
     * @return array<int, string>
     */
    private function parseAddressList(array|string $input): array
    {
        if (is_string($input)) {
            return array_filter(array_map('trim', explode(',', $input)));
        }

        return array_values(array_filter($input));
    }

    /**
     * Strip HTML tags from email body. Safety measure — we never process HTML.
     */
    private function stripHtml(?string $body): ?string
    {
        if ($body === null) {
            return null;
        }

        $text = strip_tags($body);

        // Decode common HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Collapse excessive whitespace
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
}
