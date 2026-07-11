<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Services\ConversationOrchestrator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMessageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Lead $lead,
        private string $userMessage,
    ) {}

    public function handle(ConversationOrchestrator $orchestrator): void
    {
        $orchestrator->process($this->lead, $this->userMessage);
    }
}
