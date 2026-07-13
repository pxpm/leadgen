<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Models\LeadEmailMessage;
use App\Models\Tenant;
use App\Models\TenantEmailAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessIncomingEmailJob implements ShouldQueue
{
    use Queueable;

    private ?TenantEmailAccount $account;

    private Tenant $tenant;

    private array $messageData;

    private bool $isFromWatchFolder;

    private bool $isWebhook;

    /**
     * IMAP path — from PollInboxJob.
     */
    public function __construct(
        TenantEmailAccount $account,
        array $messageData,
        bool $isFromWatchFolder = false,
    ) {
        $this->account = $account;
        $this->tenant = $account->tenant;
        $this->messageData = $messageData;
        $this->isFromWatchFolder = $isFromWatchFolder;
        $this->isWebhook = false;
    }

    /**
     * Webhook path — from InboundEmailController.
     */
    public static function dispatchFromWebhook(Tenant $tenant, array $messageData): void
    {
        // We need to construct the job differently for webhook.
        // Using a static factory that creates and dispatches a dedicated internal instance.
        (new self)->handleWebhook($tenant, $messageData);
    }

    /**
     * Handle webhook-based email processing directly (not through queue handle()).
     * We use this approach so the constructor stays clean for both paths.
     */
    private function handleWebhook(Tenant $tenant, array $messageData): void
    {
        $this->account = null;
        $this->tenant = $tenant;
        $this->messageData = $messageData;
        $this->isFromWatchFolder = true; // webhook emails are always eligible for lead creation
        $this->isWebhook = true;

        $this->process();
    }

    public function handle(): void
    {
        $this->process();
    }

    private function process(): void
    {
        $fromAddress = $this->messageData['from_address'];

        // Dedup
        if ($this->isDuplicate()) {
            return;
        }

        // Try to find a lead by email within this tenant
        $lead = Lead::where('tenant_id', $this->tenant->id)
            ->whereHas('fields', function ($q) use ($fromAddress) {
                $q->where('field_key', 'email')->where('field_value', $fromAddress);
            })
            ->first();

        // Rule: Known lead → always store the message
        if ($lead) {
            $this->storeMessage($lead);

            return;
        }

        // Rule: Unknown sender in general inbox → discard (never auto-create)
        if (! $this->isFromWatchFolder) {
            Log::info('Incoming email from unknown sender discarded (general inbox)', [
                'account_id' => $this->account?->id,
                'from' => $fromAddress,
                'subject' => $this->messageData['subject'] ?? '',
            ]);

            return;
        }

        // Rule: Unknown sender in watch folder or webhook → create lead if auto_create is enabled
        $autoCreate = $this->account
            ? $this->account->auto_create_leads
            : true; // webhook emails always auto-create by default

        if ($autoCreate) {
            AiParseEmailForLeadCreationJob::dispatchFromWebhook($this->tenant, $this->messageData);

            return;
        }

        Log::info('Incoming email ignored (auto_create disabled)', [
            'account_id' => $this->account?->id,
            'from' => $fromAddress,
            'subject' => $this->messageData['subject'] ?? '',
        ]);
    }

    private function isDuplicate(): bool
    {
        if ($this->isWebhook) {
            // Dedup by RFC 2822 Message-ID header
            $messageId = $this->messageData['message_id'] ?? null;
            if ($messageId) {
                return LeadEmailMessage::where('message_id_header', $messageId)->exists();
            }

            return false;
        }

        // IMAP: dedup by account + UID
        $uid = $this->messageData['uid'] ?? null;
        if ($uid && $this->account) {
            return LeadEmailMessage::where('tenant_email_account_id', $this->account->id)
                ->where('message_uid', $uid)
                ->exists();
        }

        return false;
    }

    private function storeMessage(Lead $lead): LeadEmailMessage
    {
        return LeadEmailMessage::create([
            'lead_id' => $lead->id,
            'tenant_email_account_id' => $this->account?->id,
            'direction' => 'inbound',
            'message_uid' => $this->messageData['uid'] ?? null,
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
