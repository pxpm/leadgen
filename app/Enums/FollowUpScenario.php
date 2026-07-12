<?php

declare(strict_types=1);

namespace App\Enums;

enum FollowUpScenario: string
{
    case Decline = 'decline';
    case RequestInfo = 'request_info';
    case QuoteFollowUp = 'quote_followup';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::Decline => 'Rejeitar Lead',
            self::RequestInfo => 'Pedir Informações',
            self::QuoteFollowUp => 'Acompanhar Orçamento',
            self::General => 'Contacto Geral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Decline => 'heroicon-o-x-circle',
            self::RequestInfo => 'heroicon-o-question-mark-circle',
            self::QuoteFollowUp => 'heroicon-o-clock',
            self::General => 'heroicon-o-chat-bubble-left',
        };
    }
}
