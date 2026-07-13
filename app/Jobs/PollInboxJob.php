<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\TenantEmailAccount;
use App\Services\ImapService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PollInboxJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private TenantEmailAccount $account,
    ) {}

    public function handle(ImapService $imap): void
    {
        if (! $this->account->isActive()) {
            return;
        }

        try {
            $connection = $imap->connect($this->account);

            // 1. Poll inbox — never auto-create leads, just match known senders
            $inboxMessages = $imap->fetchNew($connection, $this->account->last_synced_uid);

            foreach ($inboxMessages as $message) {
                ProcessIncomingEmailJob::dispatch($this->account, $message, isFromWatchFolder: false);
            }

            $inboxMaxUid = $imap->getMaxUid($connection);
            $this->account->update([
                'last_synced_uid' => $inboxMaxUid ?: $this->account->last_synced_uid,
                'last_synced_at' => now(),
                'last_error' => null,
                'status' => 'active',
            ]);

            // 2. Poll watch folder (if configured) — may auto-create leads from unknown senders
            if ($this->account->watch_folder) {
                $imap->selectFolder($connection, $this->account->watch_folder);
                $watchMessages = $imap->fetchNew($connection, null); // no UID tracking for watch folder

                foreach ($watchMessages as $message) {
                    ProcessIncomingEmailJob::dispatch($this->account, $message, isFromWatchFolder: true);
                }
            }

            \imap_close($connection);
        } catch (\Throwable $e) {
            Log::error('PollInboxJob failed', [
                'account_id' => $this->account->id,
                'email' => $this->account->email,
                'error' => $e->getMessage(),
            ]);

            $this->account->update([
                'last_error' => $e->getMessage(),
                'last_synced_at' => now(),
            ]);

            if ($this->account->last_error && \str_contains($this->account->last_error, $e->getMessage())) {
                $this->account->update(['status' => 'error']);
            }
        }
    }
}
