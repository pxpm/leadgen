<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use App\Models\UsageLog;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PlanLimitService
{
    /**
     * Atomically check if the tenant is under the limit AND increment usage.
     * Uses SELECT ... FOR UPDATE inside a transaction to prevent TOCTOU races.
     * Returns true if the operation is allowed (and usage was recorded).
     */
    public function tryUse(Tenant $tenant, string $limitKey): bool
    {
        return DB::transaction(function () use ($tenant, $limitKey) {
            $plan = $tenant->plan;

            if (! $plan) {
                throw new RuntimeException("Tenant [{$tenant->id}] has no active subscription.");
            }

            $limit = $plan->getLimit($limitKey);

            if ($limit === 0) {
                return false;
            }

            // Lock the usage row for this tenant+type+period.
            // If no row exists yet, the lock has no effect and count is 0 — safe.
            $period = now()->format('Y-m');
            $usage = UsageLog::where('tenant_id', $tenant->id)
                ->where('type', $limitKey)
                ->where('period', $period)
                ->lockForUpdate()
                ->first();

            $currentCount = $usage?->count ?? 0;

            if ($currentCount >= $limit) {
                return false;
            }

            UsageLog::incrementUsage($tenant, $limitKey);

            return true;
        });
    }

    /**
     * Check if the tenant can send an SMS based on their plan limits and current usage.
     * Convenience wrapper around tryUse().
     */
    public function canSendSms(Tenant $tenant): bool
    {
        return $this->canUse($tenant, 'sms_monthly');
    }

    /**
     * Check if the tenant can send an email based on their plan limits and current usage.
     * Convenience wrapper around tryUse().
     */
    public function canSendEmail(Tenant $tenant): bool
    {
        return $this->canUse($tenant, 'email_monthly');
    }

    /**
     * Check if the tenant can perform an AI ingestion based on their plan limits and current usage.
     * Convenience wrapper around tryUse().
     */
    public function canIngestAi(Tenant $tenant): bool
    {
        return $this->canUse($tenant, 'ai_ingestion_monthly');
    }

    /**
     * Atomically check + record: SMS send.
     */
    public function trySendSms(Tenant $tenant): bool
    {
        return $this->tryUse($tenant, 'sms_monthly');
    }

    /**
     * Atomically check + record: email send.
     */
    public function trySendEmail(Tenant $tenant): bool
    {
        return $this->tryUse($tenant, 'email_monthly');
    }

    /**
     * Atomically check + record: AI ingestion.
     */
    public function tryIngestAi(Tenant $tenant): bool
    {
        return $this->tryUse($tenant, 'ai_ingestion_monthly');
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
