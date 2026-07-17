<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\SmsProvider;
use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Mail\LeadQualifiedMail;
use App\Models\Lead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendLeadNotificationJob implements ShouldQueue
{
    use Queueable;

    /** Retry failed notifications up to 3 times with exponential backoff. */
    public int $tries = 3;

    /**
     * Backoff: 30s, 60s, 120s between retries.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function __construct(
        private Lead $lead,
        private string $channel,
        private string $recipient,
        private string $magicLinkUrl,
        private string $message,
        private string $name,
    ) {}

    public function handle(): void
    {
        match ($this->channel) {
            'email' => $this->sendEmail(),
            'sms' => $this->sendSms(),
            default => Log::warning('Unknown notification channel', ['channel' => $this->channel]),
        };
    }

    private function sendEmail(): void
    {
        Mail::to($this->recipient)
            ->send(new LeadQualifiedMail($this->lead));

        $this->lead->notifications()->create([
            'tenant_id' => $this->lead->tenant_id,
            'channel' => NotificationChannel::Email,
            'recipient' => $this->recipient,
            'status' => NotificationStatus::Sent,
            'sent_at' => now(),
        ]);

        Log::info('Lead notification email sent', [
            'lead_id' => $this->lead->id,
            'recipient' => $this->recipient,
        ]);
    }

    private function sendSms(): void
    {
        $smsProvider = app(SmsProvider::class);
        $result = $smsProvider->send($this->recipient, $this->message);

        if (! $result->success) {
            // Throw so Laravel's retry mechanism kicks in
            throw new \RuntimeException("SMS send failed: {$result->error}");
        }

        $this->lead->notifications()->create([
            'tenant_id' => $this->lead->tenant_id,
            'channel' => NotificationChannel::Sms,
            'recipient' => $this->recipient,
            'status' => NotificationStatus::Sent,
            'sent_at' => now(),
        ]);

        Log::info('Lead notification SMS sent', [
            'lead_id' => $this->lead->id,
            'recipient' => $this->recipient,
        ]);
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(\Throwable $e): void
    {
        $this->lead->notifications()->create([
            'tenant_id' => $this->lead->tenant_id,
            'channel' => $this->channel,
            'recipient' => $this->recipient,
            'status' => NotificationStatus::Failed,
            'error_message' => $e->getMessage(),
        ]);

        Log::error('Lead notification permanently failed after retries', [
            'lead_id' => $this->lead->id,
            'channel' => $this->channel,
            'recipient' => $this->recipient,
            'error' => $e->getMessage(),
        ]);
    }
}
