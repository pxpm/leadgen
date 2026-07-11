<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SmsProvider;
use App\Mail\LeadQualifiedMail;
use App\Models\Lead;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function __construct(private MagicLinkService $magicLinks) {}

    /**
     * Send all configured notifications for a qualified lead.
     */
    public function notifyTenant(Lead $lead): void
    {
        $tenant = $lead->tenant;
        $notifConfig = $tenant->notification_config ?? [];

        if (! empty($notifConfig['email']['enabled'])) {
            $this->sendEmail($lead, $notifConfig['email']['recipients'] ?? []);
        }

        if (! empty($notifConfig['sms']['enabled'])) {
            $this->sendSms($lead, $notifConfig['sms']['recipients'] ?? []);
        }
    }

    private function sendEmail(Lead $lead, array $recipients): void
    {
        foreach ($recipients as $email) {
            try {
                Mail::to($email)
                    ->send(new LeadQualifiedMail($lead));

                $lead->notifications()->create([
                    'tenant_id' => $lead->tenant_id,
                    'channel' => 'email',
                    'recipient' => $email,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } catch (\Exception $e) {
                $lead->notifications()->create([
                    'tenant_id' => $lead->tenant_id,
                    'channel' => 'email',
                    'recipient' => $email,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }

    private function sendSms(Lead $lead, array $recipients): void
    {
        $fields = $lead->fields->pluck('field_value', 'field_key');
        $name = $fields['contact_name'] ?? 'Lead';
        $urgency = $fields['urgency'] ?? 'normal';
        $score = $lead->qualification_score ?? '?';

        $magicLink = $this->magicLinks->createForLead($lead);
        $message = "{$name}, {$urgency}. Score: {$score}/10. {$magicLink}";

        $smsProvider = app(SmsProvider::class);

        foreach ($recipients as $phone) {
            $result = $smsProvider->send($phone, $message);

            $lead->notifications()->create([
                'tenant_id' => $lead->tenant_id,
                'channel' => 'sms',
                'recipient' => $phone,
                'status' => $result->success ? 'sent' : 'failed',
                'sent_at' => $result->success ? now() : null,
                'error_message' => $result->error,
            ]);
        }
    }
}
