<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Canceled = 'canceled';
    case PastDue = 'past_due';
    case Trialing = 'trialing';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('enums.subscription_status.active'),
            self::Canceled => __('enums.subscription_status.canceled'),
            self::PastDue => __('enums.subscription_status.past_due'),
            self::Trialing => __('enums.subscription_status.trial'),
        };
    }
}
