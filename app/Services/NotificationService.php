<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendLeadNotificationJob;
use App\Models\Lead;

class NotificationService
{
    public function __construct(private MagicLinkService $magicLinks) {}

    /**
     * Dispatch queued notifications for a qualified lead.
     * One job per recipient per channel — failures are retried independently.
     */
    public function notifyTenant(Lead $lead): void
    {
        $tenant = $lead->tenant;
        $notifConfig = $tenant->notification_config ?? [];

        $locale = $tenant->locale ?? 'pt';
        $fields = $lead->fields->pluck('field_value', 'field_key');
        $name = $fields['contact_name'] ?? ($locale === 'pt' ? 'Cliente' : 'Customer');
        $magicLinkUrl = $this->magicLinks->createForLead($lead);
        $smsMessage = $locale === 'pt'
            ? "Novo lead: {$name}. Veja os detalhes: {$magicLinkUrl}"
            : "New lead: {$name}. View details: {$magicLinkUrl}";

        // Email notifications
        if (! empty($notifConfig['email']['enabled'])) {
            foreach ($notifConfig['email']['recipients'] ?? [] as $email) {
                SendLeadNotificationJob::dispatch(
                    lead: $lead,
                    channel: 'email',
                    recipient: $email,
                    magicLinkUrl: $magicLinkUrl,
                    message: '',
                    name: $name,
                );
            }
        }

        // SMS notifications
        if (! empty($notifConfig['sms']['enabled'])) {
            foreach ($notifConfig['sms']['recipients'] ?? [] as $phone) {
                SendLeadNotificationJob::dispatch(
                    lead: $lead,
                    channel: 'sms',
                    recipient: $phone,
                    magicLinkUrl: $magicLinkUrl,
                    message: $smsMessage,
                    name: $name,
                );
            }
        }
    }
}
