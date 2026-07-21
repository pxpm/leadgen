<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEventTimetable extends Model
{
    protected $fillable = [
        'calendar_event_id', 'day_of_week', 'start_time', 'end_time',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class);
    }
}
