<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Models\LeadEmailMessage;
use App\Models\Tenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Queued version of the webhook email processing pipeline.
 * Runs async so the webhook controller responds to Resend immediately,
 * preventing duplicate webhook deliveries due to slow AI calls.
 */
class ProcessWebhookEmailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Tenant $tenant,
        private array $messageData,
    ) {}

    public function handle(): void
    {
        $fromAddress = $this->messageData['from_address'];

        Log::channel('email_webhook')->info('🔍 ProcessWebhookEmailJob: processing', [
            'tenant_id' => $this->tenant->id,
            'from' => $fromAddress,
            'subject' => $this->messageData['subject'] ?? '',
        ]);

        // Dedup by Message-ID
        $messageId = $this->messageData['message_id'] ?? null;
        if ($messageId && LeadEmailMessage::where('message_id_header', $messageId)->exists()) {
            Log::channel('email_webhook')->info('⏭️ Skipped — duplicate message (webhook queue)', [
                'message_id' => $messageId,
            ]);

            return;
        }

        // Try to find a lead by email within this tenant
        $lead = Lead::where('tenant_id', $this->tenant->id)
            ->whereHas('fields', function ($q) use ($fromAddress) {
                $q->where('field_key', 'email')->where('field_value', $fromAddress);
            })
            ->first();

        // Known lead → store the message
        if ($lead) {
            Log::channel('email_webhook')->info('✅ Known lead — storing message (webhook queue)', [
                'lead_id' => $lead->id,
                'from' => $fromAddress,
            ]);

            $this->storeMessage($lead);

            return;
        }

        // Unknown sender → AI parse + create lead (webhooks always auto-create)
        Log::channel('email_webhook')->info('🤖 Unknown sender — dispatching AI lead creation (webhook queue)', [
            'from' => $fromAddress,
            'subject' => $this->messageData['subject'] ?? '',
        ]);

        AiParseEmailForLeadCreationJob::dispatchFromWebhook($this->tenant, $this->messageData);
    }

    private function storeMessage(Lead $lead): LeadEmailMessage
    {
        return LeadEmailMessage::create([
            'lead_id' => $lead->id,
            'tenant_email_account_id' => null,
            'direction' => 'inbound',
            'message_uid' => null,
            'message_id_header' => $this->messageData['message_id'] ?? null,
            'in_reply_to_header' => $this->messageData['in_reply_to'] ?? null,
            'references_header' => $this->messageData['references'] ?? null,
            'subject' => $this->messageData['subject'] ?? null,
            'body_text' => $this->messageData['body_text'] ?? null,
            'from_address' => $this->messageData['from_address'],
            'from_name' => $this->messageData['from_name'] ?? null,
            'to_addresses' => $this->messageData['to'] ?? [],
            'cc_addresses' => $this->messageData['cc'] ?? [],
            'raw_headers' => $this->messageData['headers'] ?? [],
            'received_at' => $this->messageData['received_at'] ?? now(),
        ]);
    }
}
