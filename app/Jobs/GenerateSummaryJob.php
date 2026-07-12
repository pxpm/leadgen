<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Services\SummaryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateSummaryJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private Lead $lead) {}

    public function handle(SummaryService $summarizer): void
    {
        $summary = $summarizer->generate($this->lead);
        $this->lead->update(['notes' => $summary]);
    }
}
