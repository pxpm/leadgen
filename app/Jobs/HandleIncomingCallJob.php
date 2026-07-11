<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\MissedCall;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class HandleIncomingCallJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $callerNumber,
        private string $toNumber,
        private string $callSid,
        private ?string $forwardedFrom,
    ) {}

    public function handle(): void
    {
        // Idempotency: skip if already processed
        if (MissedCall::where('twilio_call_sid', $this->callSid)->exists()) {
            return;
        }

        // Try dedicated number match first
        $tenant = Tenant::where('twilio_phone_number', $this->toNumber)->first();
        $matchedBy = 'dedicated_number';

        // Fallback: ForwardedFrom matching
        if (! $tenant && $this->forwardedFrom) {
            $tenant = Tenant::whereHas('phoneNumbers', fn ($q) => $q->where('phone_number', $this->forwardedFrom))->first();
            $matchedBy = 'forwarded_from';
        }

        if (! $tenant) {
            Log::info('Unmatched missed call', ['from' => $this->callerNumber, 'to' => $this->toNumber]);

            return;
        }

        // Check excluded numbers
        $isExcluded = $tenant->excludedNumbers()
            ->where('phone_number', $this->callerNumber)
            ->exists();

        if ($isExcluded) {
            return;
        }

        // Create missed call record
        $missedCall = MissedCall::create([
            'tenant_id' => $tenant->id,
            'caller_number' => $this->callerNumber,
            'tenant_phone' => $this->toNumber,
            'twilio_call_sid' => $this->callSid,
            'matched_by' => $matchedBy,
        ]);

        // Check if auto-send is enabled and within schedule
        if ($this->isWithinAutoSendSchedule($tenant)) {
            SendCallerSmsJob::dispatch($missedCall);
        } else {
            NotifyTenantOfMissedCallJob::dispatch($missedCall);
        }
    }

    /**
     * Check if the current time falls within the tenant's auto-send schedule.
     */
    private function isWithinAutoSendSchedule(Tenant $tenant): bool
    {
        $schedule = $tenant->notification_config['auto_send_schedule'] ?? null;

        if (! $schedule || ! ($schedule['enabled'] ?? false)) {
            return false;
        }

        $timezone = $schedule['timezone'] ?? 'Europe/Lisbon';
        $now = Carbon::now($timezone);

        $allowedDays = $schedule['days'] ?? [];
        if (! in_array(strtolower($now->englishDayOfWeek), $allowedDays)) {
            return false;
        }

        $startTime = $schedule['start_time'] ?? null;
        $endTime = $schedule['end_time'] ?? null;

        if ($startTime && $endTime) {
            $currentTime = $now->format('H:i');

            return $currentTime >= $startTime && $currentTime <= $endTime;
        }

        return true;
    }
}
