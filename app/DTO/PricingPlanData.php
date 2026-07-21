<?php

declare(strict_types=1);

namespace App\DTO;

use App\Models\Plan;

final readonly class PricingPlanData
{
    public string $name;
    public string $slug;
    public string $description;
    public bool $isPopular;

    /** Formatted monthly price (e.g. "39") */
    public string $monthlyPrice;
    /** Formatted yearly price per month (e.g. "29") */
    public string $yearlyPrice;
    /** Raw monthly price (integer, for strikethrough on yearly view) */
    public string $monthlyPriceRaw;
    /** Yearly total in cents-equivalent (e.g. 34800 for €348/year) */
    public int $yearlyTotal;
    /** Monthly total in cents-equivalent */
    public int $monthlyTotal;
    /** Savings percentage when billed yearly (e.g. 25 for 25%) */
    public int $savingsPercent;

    public int $smsLimit;
    public int $emailLimit;
    public int $ingestionLimit;
    public bool $hasRecoveryCall;

    /** Whether currency symbol goes before the amount (English locale) */
    public bool $currencyBefore;

    public function __construct(Plan $plan)
    {
        $this->name = $plan->name;
        $this->slug = $plan->slug;
        $this->description = (string) $plan->description;
        $this->isPopular = (bool) $plan->is_popular;

        $monthly = (int) $plan->monthly_price;
        $yearly = (int) $plan->yearly_price_per_month;

        $this->monthlyPrice = number_format($monthly, 0);
        $this->monthlyPriceRaw = (string) $monthly;
        $this->yearlyPrice = number_format($yearly, 0);
        $this->yearlyTotal = $yearly * 12;
        $this->monthlyTotal = $monthly * 12;
        $this->savingsPercent = $monthly > 0
            ? (int) round((1 - $yearly / $monthly) * 100)
            : 0;

        $this->smsLimit = $plan->getLimit('sms_monthly');
        $this->emailLimit = $plan->getLimit('email_monthly');
        $this->ingestionLimit = $plan->getLimit('email_ingestion_monthly');
        $this->hasRecoveryCall = (bool) ($plan->limits['recovery_call'] ?? false);

        $this->currencyBefore = app()->getLocale() === 'en';
    }

    /** @param \Illuminate\Support\Collection<int, Plan> $plans */
    public static function collect(\Illuminate\Support\Collection $plans): \Illuminate\Support\Collection
    {
        return $plans->map(fn (Plan $plan) => new self($plan));
    }

    /**
     * Compute the savings badge percentage for the billing toggle.
     * Uses the first plan to calculate the discount.
     *
     * @param \Illuminate\Support\Collection<int, self> $plans
     */
    public static function badgePercent(\Illuminate\Support\Collection $plans): int
    {
        return $plans->first()?->savingsPercent ?? 0;
    }

    /**
     * Whether there are plans ready to display.
     *
     * @param \Illuminate\Support\Collection<int, self> $plans
     */
    public static function hasPlans(\Illuminate\Support\Collection $plans): bool
    {
        return $plans->isNotEmpty();
    }
}
