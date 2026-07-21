<?php

declare(strict_types=1);

namespace App\Enums;

enum CalendarEventStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => __('enums.calendar_event_status.scheduled'),
            self::Completed => __('enums.calendar_event_status.completed'),
            self::Cancelled => __('enums.calendar_event_status.cancelled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Scheduled => '#f59e0b',   // amber
            self::Completed => '#22c55e',   // green
            self::Cancelled => '#ef4444',   // red
        };
    }
}
