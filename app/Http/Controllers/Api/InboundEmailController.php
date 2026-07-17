<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookEmailJob;
use App\Services\TenantResolutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Resend\Emails\Attachment;

class InboundEmailController extends Controller
{
    /**
     * Handle inbound email webhook from mail provider (Resend, Mailgun, etc.).
     * POST /api/webhooks/inbound-email
     */
    public function __invoke(Request $request, TenantResolutionService $resolver): JsonResponse
    {
        $payload = $request->all();

        // ── Step 0: Log the raw incoming webhook ──
        Log::channel('email_webhook')->info('📩 Inbound email webhook received', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'content_type' => $request->header('Content-Type'),
            'raw_keys' => array_keys($payload),
            'raw_payload' => $this->redactSensitivePayload($payload),
        ]);

        // ── Step 1: Normalize payload ──
        $envelope = $this->normalizePayload($request);

        if (! $envelope) {
            Log::channel('email_webhook')->warning('❌ Normalization failed — unknown payload format', [
                'raw_keys' => array_keys($payload),
                'sample' => json_encode(array_slice($payload, 0, 5)),
            ]);

            return response()->json([
                'error' => 'invalid_payload',
                'message' => 'Invalid payload.',
            ], 400);
        }

        Log::channel('email_webhook')->info('✅ Payload normalized', [
            'from' => $envelope['from'],
            'to' => $envelope['to'] ?? [],
            'cc' => $envelope['cc'] ?? [],
            'subject' => $envelope['subject'] ?? '(no subject)',
            'has_body' => ! empty($envelope['body_text']),
            'attachment_count' => $envelope['attachment_count'] ?? 0,
            'message_id' => $envelope['message_id'] ?? 'none',
        ]);

        // ── Step 2: Resolve tenant FIRST — don't waste API calls on unknown tenants ──
        $tenant = $resolver->resolve([
            'from' => $envelope['from'],
            'to' => $envelope['to'] ?? [],
            'cc' => $envelope['cc'] ?? [],
        ]);

        if (! $tenant) {
            Log::channel('email_webhook')->warning('❌ No tenant identified — email rejected', [
                'from' => $envelope['from'] ?? 'unknown',
                'to' => implode(', ', $envelope['to'] ?? []),
                'cc' => implode(', ', $envelope['cc'] ?? []),
            ]);

            return response()->json([
                'error' => 'no_tenant',
                'message' => 'No tenant identified.',
            ], 422);
        }

        Log::channel('email_webhook')->info('🏢 Tenant resolved', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'tenant_slug' => $tenant->slug,
        ]);

        // ── Step 3: Fetch full email body + attachments from Resend API ──
        $resendEmailId = $this->extractResendEmailId($payload);

        if ($resendEmailId && empty($envelope['body_text'])) {
            $fullEmail = $this->fetchResendFullEmail($resendEmailId);

            $envelope['body_text'] = $fullEmail['body_text'] ?? null;
            $envelope['attachments'] = $fullEmail['attachments'] ?? [];
        }

        // ── Step 4: Dispatch to queued pipeline (fast response, no blocking AI calls) ──
        ProcessWebhookEmailJob::dispatch(
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
                'attachments' => $envelope['attachments'] ?? [],
                'resend_email_id' => $resendEmailId,
            ],
        );

        Log::channel('email_webhook')->info('✅ Webhook accepted — queued for background processing', [
            'tenant_id' => $tenant->id,
            'from' => $envelope['from'],
            'attachment_count' => count($envelope['attachments'] ?? []),
        ]);

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

        // Resend inbound webhook (Svix envelope): { type: "email.received", data: { from, to, ... }, created_at }
        if (isset($data['type'], $data['data']) && is_array($data['data'])) {
            Log::channel('email_webhook')->info('📨 Detected Resend Svix envelope', [
                'type' => $data['type'],
                'data_keys' => array_keys($data['data']),
            ]);

            $data = $data['data']; // unwrap the envelope
        }

        // Resend flat format: { from, to, cc, subject, html, text, headers }
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

    /**
     * Redact sensitive fields from the payload for safe logging.
     * Only shows metadata, keys, and truncated values — never full email bodies or PII.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function redactSensitivePayload(array $payload): array
    {
        $redacted = [];

        foreach ($payload as $key => $value) {
            if (is_string($value) && strlen($value) > 200) {
                $redacted[$key] = substr($value, 0, 200).'… ['.(strlen($value) - 200).' more chars]';
            } elseif (is_array($value) && count($value) > 10) {
                $redacted[$key] = '[array with '.count($value).' items]';
            } else {
                $redacted[$key] = $value;
            }
        }

        return $redacted;
    }

    /**
     * Extract the Resend email_id from the webhook payload (Svix envelope or flat).
     *
     * @param  array<string, mixed>  $payload
     */
    private function extractResendEmailId(array $payload): ?string
    {
        // Svix envelope: { data: { email_id: "..." } }
        if (isset($payload['data']['email_id'])) {
            return $payload['data']['email_id'];
        }

        // Flat format
        return $payload['email_id'] ?? null;
    }

    /**
     * Fetch the full email from Resend's API (body text + attachment metadata).
     * The inbound webhook only sends metadata — we need to retrieve content separately.
     *
     * @return array{body_text: ?string, attachments: array<int, array{id: string, filename: string, content_type: string, download_url: string}>}
     */
    private function fetchResendFullEmail(string $emailId): array
    {
        $result = ['body_text' => null, 'attachments' => []];

        try {
            $apiKey = config('services.resend.key');

            if (! $apiKey) {
                Log::channel('email_webhook')->warning('RESEND_API_KEY not configured — cannot fetch email content');

                return $result;
            }

            $resend = \Resend::client($apiKey);
            $email = $resend->emails->receiving->get($emailId);

            // Extract body text (prefer plain text, fall back to HTML stripped)
            $body = $email->text ?? null;
            if (! $body && isset($email->html)) {
                $body = strip_tags($email->html);
                $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
            $result['body_text'] = $body;

            // Collect attachment metadata (actual download happens later, per-attachment)
            if (! empty($email->attachments)) {
                foreach ($email->attachments as $att) {
                    if ($att instanceof Attachment) {
                        $result['attachments'][] = [
                            'id' => $att->id,
                            'filename' => $att->filename,
                            'content_type' => $att->content_type,
                            'download_url' => $att->download_url ?? null,
                        ];
                    }
                }
            }

            Log::channel('email_webhook')->info('📥 Fetched full email from Resend API', [
                'email_id' => $emailId,
                'body_length' => $body ? strlen($body) : 0,
                'attachment_count' => count($result['attachments']),
                'attachment_ids' => array_column($result['attachments'], 'id'),
            ]);
        } catch (\Throwable $e) {
            Log::channel('email_webhook')->error('Failed to fetch email from Resend API', [
                'email_id' => $emailId,
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }
}
