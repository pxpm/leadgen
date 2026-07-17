<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;

class LeadScoringService
{
    public function __construct(private IndustryConfigEngine $config) {}

    /**
     * Score a lead based on industry config scoring factors.
     * Scoring rules are defined per-industry in scoring.factors.
     */
    public function score(Lead $lead): int
    {
        $config = $this->config->resolve($lead->tenant, $lead->services[0] ?? null);
        $factors = $config['scoring']['factors'] ?? [];
        $collected = $lead->fields->pluck('field_value', 'field_key')->toArray();
        $hasPhotos = $lead->getMedia('photos')->isNotEmpty();

        $score = 0;

        // Config-driven scoring: each factor maps a field_key to points
        foreach ($factors as $factorKey => $points) {
            if ($factorKey === 'photos_uploaded' && $hasPhotos) {
                $score += $points;
            } elseif (isset($collected[$factorKey]) && $collected[$factorKey] !== '' && $collected[$factorKey] !== Lead::DECLINED) {
                $score += $points;
            }
        }

        $score = max(1, min(10, $score));

        $lead->update(['qualification_score' => $score]);

        return $score;
    }
}
