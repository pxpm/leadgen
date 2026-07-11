<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Services\LeadScoringService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScoreLeadJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private Lead $lead) {}

    public function handle(LeadScoringService $scorer): void
    {
        $scorer->score($this->lead);
    }
}
