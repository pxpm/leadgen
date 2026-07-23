<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Plan;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class IndustriesWithinPlanLimit implements ValidationRule
{
    public function __construct(
        private readonly Plan $plan,
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $maxIndustries = $this->plan->getMaxIndustries();

        // null means unlimited
        if ($maxIndustries === null) {
            return;
        }

        $count = is_array($value) ? count($value) : 0;

        if ($count > $maxIndustries) {
            $fail(__('validation.max_industries', [
                'max' => $maxIndustries,
                'plan' => $this->plan->name,
            ]));
        }
    }
}
