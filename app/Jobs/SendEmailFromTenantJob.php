<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\GenericEmail;
use App\Models\Lead;
use App\Models\LeadEmailMessage;
use App\Models\TenantEmailAccount;
use App\Services\GoogleSendService;
use App\Services\MicrosoftSendService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailFromTenantJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Lead $lead,
        private string $subject,
        private string $bodyText,
        private ?string $bodyHtml = null,
        private ?string $inReplyTo = null,
        private ?string $references = null,
    ) {}

    public function handle(): void
    {
        $account = TenantEmailAccount::where('tenant_id', $this->lead->tenant_id)
            ->active()
            ->first();

        if (! $account) {
            Log::warning('SendEmailFromTenantJob: No active email account for tenant — email will not be sent', [
                'tenant_id' => $this->lead->tenant_id,
                'lead_id' => $this->lead->id,
                'subject' => $this->subject,
            ]);

            return;
        }

        $this->sendViaTenant($account);
    }

    private function storeSentMessage(TenantEmailAccount $account, string $leadEmail, string $messageId): void
    {
        LeadEmailMessage::create([
            'lead_id' => $this->lead->id,
            'tenant_email_account_id' => $account->id,
            'direction' => 'outbound',
            'message_id_header' => $messageId,
            'in_reply_to_header' => $this->inReplyTo,
            'references_header' => $this->references,
            'subject' => $this->subject,
            'body_text' => $this->bodyText,
            'body_html' => $this->bodyHtml,
            'from_address' => $account->email,
            'from_name' => $account->name ?? $account->email,
            'to_addresses' => [$leadEmail],
            'received_at' => now(),
        ]);
    }

    private function sendViaTenant(TenantEmailAccount $account): void
    {
        $leadEmail = $this->lead->fields()
            ->where('field_key', 'email')
            ->value('field_value');

        if (! $leadEmail) {
            Log::warning('Cannot send email — lead has no email field', [
                'lead_id' => $this->lead->id,
            ]);

            return;
        }

        // Prefer Gmail API if Google OAuth is configured
        if ($account->isGoogleOAuth()) {
            try {
                $gmailMessageId = app(GoogleSendService::class)->send(
                    account: $account,
                    to: $leadEmail,
                    subject: $this->subject,
                    bodyText: $this->bodyText,
                    bodyHtml: $this->bodyHtml,
                    inReplyTo: $this->inReplyTo,
                    references: $this->references,
                );

                $this->storeSentMessage($account, $leadEmail, $gmailMessageId);

                Log::info('Email sent via Gmail API (Google OAuth)', [
                    'lead_id' => $this->lead->id,
                    'account_id' => $account->id,
                ]);

                return;
            } catch (\Throwable $e) {
                Log::warning('Gmail API send failed, falling back to SMTP', [
                    'account_id' => $account->id,
                    'error' => $e->getMessage(),
                ]);
                // Fall through to SMTP
            }
        }

        // Prefer Microsoft Graph if Microsoft OAuth is configured
        if ($account->isMicrosoftOAuth()) {
            try {
                $graphMessageId = app(MicrosoftSendService::class)->send(
                    account: $account,
                    to: $leadEmail,
                    subject: $this->subject,
                    bodyText: $this->bodyText,
                    bodyHtml: $this->bodyHtml,
                );

                $this->storeSentMessage($account, $leadEmail, $graphMessageId);

                Log::info('Email sent via Microsoft Graph (Microsoft OAuth)', [
                    'lead_id' => $this->lead->id,
                    'account_id' => $account->id,
                ]);

                return;
            } catch (\Throwable $e) {
                Log::warning('Microsoft Graph send failed, falling back to SMTP', [
                    'account_id' => $account->id,
                    'error' => $e->getMessage(),
                ]);
                // Fall through to SMTP
            }
        }

        // SMTP path (app password or custom)
        $smtpConfig = $account->smtp_config;
        $password = $account->app_password ? Crypt::decryptString($account->app_password) : '';

        $mailerName = 'tenant_'.$account->id;

        Config::set('mail.mailers.'.$mailerName, [
            'transport' => 'smtp',
            'host' => $smtpConfig['host'],
            'port' => $smtpConfig['port'],
            'encryption' => $smtpConfig['encryption'] ?? 'tls',
            'username' => $account->email,
            'password' => $password,
            'timeout' => 30,
        ]);

        $leadEmail = $this->lead->fields()
            ->where('field_key', 'email')
            ->value('field_value');

        if (! $leadEmail) {
            Log::warning('Cannot send email — lead has no email field', [
                'lead_id' => $this->lead->id,
            ]);

            return;
        }

        try {
            $messageId = '<'.bin2hex(random_bytes(16)).'@'.$account->email.'>';

            Mail::mailer($mailerName)
                ->to($leadEmail)
                ->send(new GenericEmail(
                    subject: $this->subject,
                    bodyText: $this->bodyText,
                    bodyHtml: $this->bodyHtml,
                    fromAddress: $account->email,
                    fromName: $account->name ?? $account->email,
                    messageId: $messageId,
                    inReplyTo: $this->inReplyTo,
                    references: $this->references,
                ));

            // Store sent message
            $this->storeSentMessage($account, $leadEmail, $messageId);

            Log::info('Email sent from tenant account', [
                'lead_id' => $this->lead->id,
                'account_id' => $account->id,
                'from' => $account->email,
                'to' => $leadEmail,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send email via tenant account', [
                'lead_id' => $this->lead->id,
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            // Fall back to system default
            $this->sendViaSystem($account);
        }
    }

    private function sendViaSystem(?TenantEmailAccount $account): void
    {
        $leadEmail = $this->lead->fields()
            ->where('field_key', 'email')
            ->value('field_value');

        if (! $leadEmail) {
            return;
        }

        try {
            Mail::to($leadEmail)->send(new GenericEmail(
                subject: $this->subject,
                bodyText: $this->bodyText,
                bodyHtml: $this->bodyHtml,
            ));

            Log::info('Email sent via system mailer (fallback)', [
                'lead_id' => $this->lead->id,
                'to' => $leadEmail,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send email via system mailer', [
                'lead_id' => $this->lead->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
