<?php

declare(strict_types=1);

namespace App\Enums;

enum CalendarEventCategory: string
{
    case Visit = 'visit';
    case Task = 'task';
    case Meeting = 'meeting';
    case FollowUp = 'follow_up';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Visit => __('enums.calendar_event_category.visit'),
            self::Task => __('enums.calendar_event_category.task'),
            self::Meeting => __('enums.calendar_event_category.meeting'),
            self::FollowUp => __('enums.calendar_event_category.follow_up'),
            self::Other => __('enums.calendar_event_category.other'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Visit => '#3b82f6',     // blue
            self::Task => '#f59e0b',       // amber
            self::Meeting => '#8b5cf6',    // violet
            self::FollowUp => '#22c55e',   // green
            self::Other => '#6b7280',      // gray
        };
    }
}
