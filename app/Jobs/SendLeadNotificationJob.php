<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendLeadNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private Lead $lead) {}

    public function handle(NotificationService $notifier): void
    {
        $notifier->notifyTenant($this->lead);
    }
}
