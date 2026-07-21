<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CalendarEventStatus;
use App\Models\CalendarEvent;
use App\Models\CalendarEventTimetable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RRule\RRule;

class CalendarEventService
{
    /**
     * Generate child event instances from a recurring master event.
     *
     * @return array{created: int, updated: int, skipped: int}
     */
    public function generateInstances(CalendarEvent $master, CarbonInterface $from, int $monthsAhead = 3): array
    {
        if (! $master->is_recurring || ! $master->recurrence_rule) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 0];
        }

        $to = $from->copy()->addMonths($monthsAhead);
        if ($master->recurrence_ends_at && $master->recurrence_ends_at->lt($to)) {
            $to = $master->recurrence_ends_at;
        }

        try {
            $rrule = new RRule($master->recurrence_rule, $master->start_at);
        } catch (\Exception) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 0];
        }

        $occurrences = $rrule->getOccurrencesBetween($from, $to);
        $timetables = $master->timetables()->get()->keyBy('day_of_week');

        $dayMap = ['MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6, 'SU' => 7];

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($master, $occurrences, $timetables, $dayMap, &$created, &$updated, &$skipped) {
            foreach ($occurrences as $date) {
                $dayCode = array_search((int) $date->format('N'), $dayMap, true);

                $startTime = $master->start_at->format('H:i');
                $endTime = $master->end_at->format('H:i');

                if ($timetable = $timetables->get($dayCode)) {
                    $startTime = substr((string) $timetable->start_time, 0, 5);
                    $endTime = substr((string) $timetable->end_time, 0, 5);
                }

                $startAt = $date->copy()->setTimeFromTimeString($startTime);
                $endAt = $date->copy()->setTimeFromTimeString($endTime);

                $existing = CalendarEvent::where('parent_event_id', $master->id)
                    ->whereDate('start_at', $date)
                    ->first();

                if ($existing) {
                    if ($existing->status !== CalendarEventStatus::Cancelled) {
                        $existing->update([
                            'start_at' => $startAt,
                            'end_at' => $endAt,
                            'title' => $master->title,
                            'category' => $master->category,
                            'location' => $master->location,
                        ]);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    CalendarEvent::create([
                        'tenant_id' => $master->tenant_id,
                        'parent_event_id' => $master->id,
                        'title' => $master->title,
                        'description' => $master->description,
                        'category' => $master->category,
                        'start_at' => $startAt,
                        'end_at' => $endAt,
                        'all_day' => $master->all_day,
                        'location' => $master->location,
                        'status' => CalendarEventStatus::Scheduled,
                        'color' => $master->color,
                        'eventable_type' => $master->eventable_type,
                        'eventable_id' => $master->eventable_id,
                        'created_by' => $master->created_by,
                        'meta' => $master->meta,
                    ]);
                    $created++;
                }
            }
        });

        return compact('created', 'updated', 'skipped');
    }

    /**
     * Check if moving an event to a new time would conflict.
     */
    public function detectConflicts(CalendarEvent $event, CarbonInterface $newStart, CarbonInterface $newEnd): bool
    {
        return CalendarEvent::where('tenant_id', $event->tenant_id)
            ->where('id', '!=', $event->id)
            ->whereNotIn('status', [CalendarEventStatus::Cancelled->value])
            ->where('start_at', '<', $newEnd)
            ->where('end_at', '>', $newStart)
            ->exists();
    }

    /**
     * Save timetable entries for a recurring master event.
     *
     * @param  array<int, array{day_of_week: string, start_time: string, end_time: string}>  $entries
     */
    public function saveTimetables(CalendarEvent $event, array $entries): void
    {
        $event->timetables()->delete();

        foreach ($entries as $entry) {
            CalendarEventTimetable::create([
                'calendar_event_id' => $event->id,
                'day_of_week' => $entry['day_of_week'],
                'start_time' => $entry['start_time'],
                'end_time' => $entry['end_time'],
            ]);
        }
    }
}
