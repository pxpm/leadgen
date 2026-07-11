<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;

class LeadScoringService
{
    public function __construct(private IndustryConfigEngine $config) {}

    /**
     * Score a lead based on industry config scoring factors.
     */
    public function score(Lead $lead): int
    {
        $config = $this->config->resolve($lead->tenant);
        $factors = $config['scoring']['factors'] ?? [];
        $collected = $lead->fields->pluck('field_value', 'field_key')->toArray();
        $hasPhotos = $lead->getMedia('photos')->isNotEmpty();

        $score = 0;

        if ($hasPhotos) {
            $score += $factors['photos_uploaded'] ?? 0;
        }

        if (! empty($collected['urgency'])) {
            $score += $factors['urgency_provided'] ?? 0;
        }

        if (! empty($collected['property_address'])) {
            $score += $factors['address_provided'] ?? 0;
        }

        if (! empty($collected['problem_type'])) {
            $score += $factors['project_type_known'] ?? 0;
        }

        if (! empty($collected['insurance_claim']) && $collected['insurance_claim'] === 'yes') {
            $score += $factors['insurance_claim'] ?? 0;
        }

        if (($collected['problem_type'] ?? '') === 'replacement') {
            $score += $factors['replacement_project'] ?? 0;
        }

        $score = max(1, min(10, $score));

        $lead->update(['qualification_score' => $score]);

        return $score;
    }
}
