<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CalendarEventCategory;
use App\Enums\CalendarEventStatus;
use Database\Factories\CalendarEventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CalendarEvent extends Model
{
    /** @use HasFactory<CalendarEventFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'title', 'description', 'category', 'start_at', 'end_at',
        'all_day', 'location', 'status', 'color', 'is_recurring',
        'recurrence_rule', 'recurrence_ends_at', 'parent_event_id',
        'eventable_type', 'eventable_id', 'created_by', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'category' => CalendarEventCategory::class,
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'all_day' => 'boolean',
            'status' => CalendarEventStatus::class,
            'is_recurring' => 'boolean',
            'recurrence_ends_at' => 'date',
            'meta' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_event_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_event_id');
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(CalendarEventTimetable::class);
    }

    public function eventable(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeMasters(Builder $query): Builder
    {
        return $query->whereNull('parent_event_id');
    }

    public function scopeBetween(Builder $query, string $start, string $end): Builder
    {
        return $query->where('start_at', '<', $end)
            ->where('end_at', '>', $start);
    }

    public function isMaster(): bool
    {
        return $this->parent_event_id === null;
    }
}
