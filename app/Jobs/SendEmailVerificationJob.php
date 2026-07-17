<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\EmailVerificationCode;
use App\Models\TenantEmailAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailVerificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private TenantEmailAccount $account,
    ) {}

    public function handle(): void
    {
        $url = $this->account->getVerificationUrl();

        try {
            Mail::to($this->account->email)->send(
                new EmailVerificationCode($this->account, $url)
            );

            Log::info('Verification email sent', [
                'account_id' => $this->account->id,
                'email' => $this->account->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send verification email', [
                'account_id' => $this->account->id,
                'email' => $this->account->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
