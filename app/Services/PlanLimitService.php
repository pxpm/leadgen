<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use App\Models\UsageLog;
use RuntimeException;

class PlanLimitService
{
    /**
     * Check if the tenant can send an SMS based on their plan limits and current usage.
     */
    public function canSendSms(Tenant $tenant): bool
    {
        return $this->canUse($tenant, 'sms_monthly');
    }

    /**
     * Check if the tenant can send an email based on their plan limits and current usage.
     */
    public function canSendEmail(Tenant $tenant): bool
    {
        return $this->canUse($tenant, 'email_monthly');
    }

    /**
     * Check if the tenant can perform an AI ingestion based on their plan limits and current usage.
     */
    public function canIngestAi(Tenant $tenant): bool
    {
        return $this->canUse($tenant, 'ai_ingestion_monthly');
    }

    /**
     * Get the current month's usage for a given type.
     */
    public function getUsage(Tenant $tenant, string $type): int
    {
        return UsageLog::getUsage($tenant, $type);
    }

    /**
     * Get the limit for a given type from the tenant's active plan.
     */
    public function getLimit(Tenant $tenant, string $type): int
    {
        $plan = $tenant->plan;

        if (! $plan) {
            throw new RuntimeException("Tenant [{$tenant->id}] has no active subscription.");
        }

        return $plan->getLimit($type);
    }

    /**
     * Record a usage event for the tenant.
     */
    public function recordUsage(Tenant $tenant, string $type): void
    {
        if (! $tenant->plan) {
            throw new RuntimeException("Tenant [{$tenant->id}] has no active subscription.");
        }

        UsageLog::incrementUsage($tenant, $type);
    }

    /**
     * Check if the tenant is under the limit for a given type.
     */
    private function canUse(Tenant $tenant, string $limitKey): bool
    {
        $plan = $tenant->plan;

        if (! $plan) {
            throw new RuntimeException("Tenant [{$tenant->id}] has no active subscription.");
        }

        $limit = $plan->getLimit($limitKey);

        // A limit of 0 means the feature is disabled for this plan
        if ($limit === 0) {
            return false;
        }

        $usage = UsageLog::getUsage($tenant, $limitKey);

        return $usage < $limit;
    }
}
