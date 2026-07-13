<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\PollInboxJob;
use App\Models\TenantEmailAccount;
use Illuminate\Console\Command;

class EmailPollCommand extends Command
{
    protected $signature = 'email:poll';

    protected $description = 'Poll all active email accounts for new messages via IMAP';

    public function handle(): int
    {
        $accounts = TenantEmailAccount::active()->get();

        if ($accounts->isEmpty()) {
            $this->info(__('app.console.email_poll_no_accounts'));

            return self::SUCCESS;
        }

        $delay = 0;
        foreach ($accounts as $account) {
            // Stagger by 5 seconds to avoid connection bursts
            PollInboxJob::dispatch($account)->delay(now()->addSeconds($delay));
            $delay += 5;
        }

        $this->info(__('app.console.email_poll_dispatched', ['count' => $accounts->count()]));

        return self::SUCCESS;
    }
}
