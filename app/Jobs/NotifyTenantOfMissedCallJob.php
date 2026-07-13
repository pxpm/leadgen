<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\SmsProvider;
use App\Models\MissedCall;
use App\Models\ShortLink;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class NotifyTenantOfMissedCallJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private MissedCall $missedCall,
    ) {}

    public function handle(): void
    {
        if ($this->missedCall->tenant_notified_at) {
            return;
        }

        $tenant = $this->missedCall->tenant;
        $config = $tenant->notification_config['missed_call_notification'] ?? [];
        $method = $config['method'] ?? 'sms';

        $shortLink = ShortLink::forMissedCallSendSms($this->missedCall);
        $actionUrl = url('/s/'.$shortLink->hash);

        $message = "Perdeu uma chamada de {$this->missedCall->caller_number}. Clique para enviar SMS de follow-up: {$actionUrl}";

        if ($method === 'sms' || $method === 'both') {
            $recipients = $config['recipients'] ?? [];
            foreach ($recipients as $recipient) {
                try {
                    app(SmsProvider::class)->send($recipient, $message);
                } catch (\Exception $e) {
                    Log::error('Tenant notification SMS failed', [
                        'missed_call_id' => $this->missedCall->id,
                        'recipient' => $recipient,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        if ($method === 'email' || $method === 'both') {
            $emailRecipients = $config['email_recipients'] ?? [];
            foreach ($emailRecipients as $email) {
                try {
                    // TODO: implement email notification
                    Log::info('Tenant email notification would be sent', [
                        'missed_call_id' => $this->missedCall->id,
                        'email' => $email,
                        'message' => $message,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Tenant notification email failed', [
                        'missed_call_id' => $this->missedCall->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->missedCall->update(['tenant_notified_at' => now()]);
        Log::info('Tenant notified of missed call', ['missed_call_id' => $this->missedCall->id]);
    }
}
