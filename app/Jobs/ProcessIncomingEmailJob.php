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
     * Uses reflection to create instance without calling the IMAP constructor,
     * since webhooks don't have a TenantEmailAccount.
     */
    public static function dispatchFromWebhook(Tenant $tenant, array $messageData): void
    {
        $reflection = new \ReflectionClass(self::class);

        /** @var self $instance */
        $instance = $reflection->newInstanceWithoutConstructor();

        $instance->account = null;
        $instance->tenant = $tenant;
        $instance->messageData = $messageData;
        $instance->isFromWatchFolder = true; // webhook emails are always eligible for lead creation
        $instance->isWebhook = true;

        $instance->process();
    }

    public function handle(): void
    {
        $this->process();
    }

    private function process(): void
    {
        $fromAddress = $this->messageData['from_address'];

        Log::channel('email_webhook')->info('🔍 ProcessIncomingEmailJob: processing', [
            'tenant_id' => $this->tenant->id,
            'from' => $fromAddress,
            'subject' => $this->messageData['subject'] ?? '',
            'is_webhook' => $this->isWebhook,
            'is_watch_folder' => $this->isFromWatchFolder,
        ]);

        // Dedup
        if ($this->isDuplicate()) {
            Log::channel('email_webhook')->info('⏭️ Skipped — duplicate message', [
                'message_id' => $this->messageData['message_id'] ?? 'none',
            ]);

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
            Log::channel('email_webhook')->info('✅ Known lead — storing message', [
                'lead_id' => $lead->id,
                'from' => $fromAddress,
            ]);

            $this->storeMessage($lead);

            return;
        }

        // Rule: Unknown sender in general inbox → discard (never auto-create)
        if (! $this->isFromWatchFolder) {
            Log::channel('email_webhook')->info('🗑️ Unknown sender, not watch folder — discarded', [
                'from' => $fromAddress,
            ]);

            return;
        }

        // Rule: Unknown sender in watch folder or webhook → create lead if auto_create is enabled
        $autoCreate = $this->account
            ? $this->account->auto_create_leads
            : true; // webhook emails always auto-create by default

        if ($autoCreate) {
            Log::channel('email_webhook')->info('🤖 Unknown sender — dispatching AI lead creation', [
                'from' => $fromAddress,
                'subject' => $this->messageData['subject'] ?? '',
            ]);

            AiParseEmailForLeadCreationJob::dispatchFromWebhook($this->tenant, $this->messageData);

            return;
        }

        Log::channel('email_webhook')->info('⏸️ Auto-create disabled — ignored', [
            'from' => $fromAddress,
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
