<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\CalendarEventStatus;
use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Services\CalendarEventService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    public function __construct(
        private CalendarEventService $service,
    ) {}

    /**
     * List events for a date range. Tenant-scoped.
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = tenant();
        if (! $tenant) {
            abort(403);
        }

        $start = $request->query('start', now()->startOfMonth()->toIso8601String());
        $end = $request->query('end', now()->endOfMonth()->toIso8601String());

        $events = CalendarEvent::forTenant($tenant->id)
            ->between($start, $end)
            ->whereNotIn('status', [CalendarEventStatus::Cancelled->value])
            ->get()
            ->map(fn (CalendarEvent $e) => $this->formatEvent($e));

        return response()->json($events);
    }

    /**
     * Create a new event. Handles both master and one-off events.
     */
    public function store(Request $request): JsonResponse
    {
        $tenant = tenant();
        if (! $tenant) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:50'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'all_day' => ['boolean'],
            'location' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7'],
            'is_recurring' => ['boolean'],
            'recurrence_rule' => ['nullable', 'string', 'max:500'],
            'recurrence_ends_at' => ['nullable', 'date'],
            'lead_id' => ['nullable', 'integer', 'exists:leads,id'],
            'timetables' => ['nullable', 'array'],
            'timetables.*.day_of_week' => ['required_with:timetables', 'string', 'in:MO,TU,WE,TH,FR,SA,SU'],
            'timetables.*.start_time' => ['required_with:timetables', 'date_format:H:i'],
            'timetables.*.end_time' => ['required_with:timetables', 'date_format:H:i'],
        ]);

        $event = CalendarEvent::create([
            'tenant_id' => $tenant->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'],
            'all_day' => (bool) ($validated['all_day'] ?? false),
            'location' => $validated['location'] ?? null,
            'status' => CalendarEventStatus::Scheduled,
            'color' => $validated['color'] ?? null,
            'is_recurring' => (bool) ($validated['is_recurring'] ?? false),
            'recurrence_rule' => $validated['recurrence_rule'] ?? null,
            'recurrence_ends_at' => $validated['recurrence_ends_at'] ?? null,
            'eventable_type' => isset($validated['lead_id']) ? 'App\\Models\\Lead' : null,
            'eventable_id' => $validated['lead_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Save timetable entries for recurring events
        if (! empty($validated['timetables'])) {
            $this->service->saveTimetables($event, $validated['timetables']);
        }

        // Generate initial instances for recurring events
        if ($event->is_recurring) {
            $this->service->generateInstances($event, Carbon::parse($validated['start_at']));
        }

        return response()->json($this->formatEvent($event), 201);
    }

    /**
     * Update an event (move, resize, edit fields).
     */
    public function update(Request $request, int $event): JsonResponse
    {
        $tenant = tenant();
        abort_if(! $tenant, 403);

        $event = CalendarEvent::where('tenant_id', $tenant->id)->findOrFail($event);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['sometimes', 'string', 'max:50'],
            'start_at' => ['sometimes', 'date'],
            'end_at' => ['sometimes', 'date', 'after:start_at'],
            'all_day' => ['boolean'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:scheduled,completed,cancelled'],
            'color' => ['nullable', 'string', 'max:7'],
            'lead_id' => ['nullable', 'integer', 'exists:leads,id'],
            'update_future' => ['boolean'], // for recurring masters
        ]);

        // Recurring master → update all future or just this
        if ($event->isMaster() && $event->is_recurring && ($validated['update_future'] ?? false)) {
            CalendarEvent::where('parent_event_id', $event->id)
                ->where('start_at', '>=', now())
                ->whereNotIn('status', [CalendarEventStatus::Cancelled->value])
                ->update([
                    'title' => $validated['title'] ?? $event->title,
                    'category' => $validated['category'] ?? $event->category,
                    'location' => $validated['location'] ?? $event->location,
                ]);
        }

        // Detect conflicts on move
        if (isset($validated['start_at']) || isset($validated['end_at'])) {
            $newStart = isset($validated['start_at']) ? Carbon::parse($validated['start_at']) : $event->start_at;
            $newEnd = isset($validated['end_at']) ? Carbon::parse($validated['end_at']) : $event->end_at;

            if ($this->service->detectConflicts($event, $newStart, $newEnd)) {
                return response()->json(['error' => 'conflict', 'message' => 'This time slot overlaps with another event.'], 409);
            }
        }

        $updateData = array_intersect_key($validated, array_flip([
            'title', 'description', 'category', 'start_at', 'end_at',
            'all_day', 'location', 'status', 'color',
        ]));

        if (isset($validated['lead_id'])) {
            $updateData['eventable_type'] = 'App\\Models\\Lead';
            $updateData['eventable_id'] = $validated['lead_id'];
        }

        $event->update($updateData);

        return response()->json($this->formatEvent($event));
    }

    /**
     * Delete an event.
     */
    public function destroy(int $event): JsonResponse
    {
        $tenant = tenant();
        abort_if(! $tenant, 403);

        $event = CalendarEvent::where('tenant_id', $tenant->id)->findOrFail($event);

        $event->delete();

        return response()->json(null, 204);
    }

    private function formatEvent(CalendarEvent $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'start' => $event->start_at->toIso8601String(),
            'end' => $event->end_at->toIso8601String(),
            'allDay' => $event->all_day,
            'color' => $event->color ?? $event->category->color(),
            'extendedProps' => [
                'category' => $event->category->value,
                'categoryLabel' => $event->category->label(),
                'status' => $event->status->value,
                'location' => $event->location,
                'description' => $event->description,
                'isRecurring' => $event->is_recurring,
                'isMaster' => $event->isMaster(),
                'leadId' => $event->eventable_type === 'App\\Models\\Lead' ? $event->eventable_id : null,
            ],
        ];
    }
}
