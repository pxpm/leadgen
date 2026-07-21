<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class ExpireTrials extends Command
{
    protected $signature = 'trials:expire';

    protected $description = 'Cancel subscriptions where the trial period has ended.';

    public function handle(): int
    {
        $expired = Subscription::where('status', 'trialing')
            ->where('trial_ends_at', '<', now())
            ->update(['status' => 'canceled']);

        $this->info("Expired {$expired} trial subscription(s).");

        return self::SUCCESS;
    }
}
