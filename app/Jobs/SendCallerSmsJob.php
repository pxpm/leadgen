<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\SmsProvider;
use App\Models\MissedCall;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class SendCallerSmsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private MissedCall $missedCall,
    ) {}

    public function handle(): void
    {
        if ($this->missedCall->caller_sms_sent_at) {
            return;
        }

        $tenant = $this->missedCall->tenant;

        $intakeUrl = URL::temporarySignedRoute(
            'missed-call.intake',
            now()->addHours(48),
            ['missedCall' => $this->missedCall->id]
        );

        $message = $tenant->notification_config['missed_call_sms_template']
            ?? 'Desculpe, não podemos atender agora. Toque aqui para nos deixar uma mensagem: {intake_url}';

        $message = str_replace(
            ['{company_name}', '{intake_url}'],
            [$tenant->name, $intakeUrl],
            $message
        );

        try {
            $result = app(SmsProvider::class)->send($this->missedCall->caller_number, $message);

            if ($result->success) {
                $this->missedCall->update(['caller_sms_sent_at' => now(), 'sms_sent' => true]);
                Log::info('Caller SMS sent', ['missed_call_id' => $this->missedCall->id]);
            } else {
                Log::error('Caller SMS failed', ['missed_call_id' => $this->missedCall->id, 'error' => $result->error]);
            }
        } catch (\Exception $e) {
            Log::error('Caller SMS exception', ['missed_call_id' => $this->missedCall->id, 'error' => $e->getMessage()]);
        }
    }
}
