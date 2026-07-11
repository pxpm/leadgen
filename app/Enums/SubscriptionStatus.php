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
            self::Active => 'Ativo',
            self::Canceled => 'Cancelado',
            self::PastDue => 'Pagamento em Atraso',
            self::Trialing => 'Trial',
        };
    }
}
