<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\SmsProvider;
use App\Contracts\SmsResult;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class TwilioSmsProvider implements SmsProvider
{
    public function send(string $to, string $message): SmsResult
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (! $sid || ! $token || ! $from) {
            Log::warning('Twilio not configured — SMS not sent', ['to' => $to]);

            return new SmsResult(false, error: 'Twilio not configured');
        }

        try {
            $client = new Client($sid, $token);
            $msg = $client->messages->create($to, [
                'from' => $from,
                'body' => $message,
            ]);

            Log::info('SMS sent', ['to' => $to, 'sid' => $msg->sid]);

            return new SmsResult(true, messageId: $msg->sid);
        } catch (\Exception $e) {
            Log::error('SMS failed', ['to' => $to, 'error' => $e->getMessage()]);

            return new SmsResult(false, error: $e->getMessage());
        }
    }
}
