<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\CalendarEventStatus;
use App\Models\CalendarEvent;
use App\Services\CalendarEventService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;

class CalendarPage extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Calendário';

    protected static ?string $title = 'Calendário';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.calendar';

    public static function canAccess(array $parameters = []): bool
    {
        return ! auth()->user()?->isSuperAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return ! auth()->user()?->isSuperAdmin();
    }

    /**
     * Fetch events for FullCalendar.
     */
    public function fetchEvents(string $start, string $end): array
    {
        $tenant = tenant();

        return CalendarEvent::forTenant($tenant->id)
            ->between($start, $end)
            ->whereNotIn('status', [CalendarEventStatus::Cancelled->value])
            ->get()
            ->map(fn (CalendarEvent $e) => [
                'id' => $e->id,
                'title' => $e->title,
                'start' => $e->start_at->toIso8601String(),
                'end' => $e->end_at->toIso8601String(),
                'allDay' => $e->all_day,
                'color' => $e->color ?? $e->category->color(),
                'extendedProps' => [
                    'description' => $e->description,
                    'category' => $e->category->label(),
                    'status' => $e->status->value,
                    'isRecurring' => $e->is_recurring,
                    'isMaster' => $e->isMaster(),
                ],
            ])
            ->toArray();
    }

    /**
     * Handle drag/drop or resize.
     */
    public function moveEvent(int $id, string $start, string $end): array
    {
        $event = CalendarEvent::findOrFail($id);

        $newStart = Carbon::parse($start);
        $newEnd = Carbon::parse($end);

        if ($newStart->isPast()) {
            return ['error' => 'past_date', 'message' => 'Cannot move events to the past.'];
        }

        if (app(CalendarEventService::class)->detectConflicts($event, $newStart, $newEnd)) {
            return ['error' => 'conflict', 'message' => 'Time slot overlaps another event.'];
        }

        $event->update(['start_at' => $newStart, 'end_at' => $newEnd]);

        return ['ok' => true];
    }

    /**
     * Open the create modal from calendar selection.
     */
    public function openCreateModal(string $start, ?string $end): void
    {
        $this->dispatch('open-create-modal', start: $start, end: $end);
    }

    /**
     * Open the edit modal on event click.
     */
    public function openEditModal(int $id): void
    {
        $this->dispatch('open-edit-modal', id: $id);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('new_event')
                ->label('Novo Evento')
                ->icon('heroicon-o-plus')
                ->form([
                    TextInput::make('title')->label('Título')->required()->maxLength(255),
                    Textarea::make('description')->label('Descrição')->rows(2),
                    Select::make('category')->label('Categoria')->options([
                        'visit' => 'Visita',
                        'task' => 'Tarefa',
                        'meeting' => 'Reunião',
                        'follow_up' => 'Follow-up',
                        'other' => 'Outro',
                    ])->required(),
                    DateTimePicker::make('start_at')->label('Início')->required()->native(false),
                    DateTimePicker::make('end_at')->label('Fim')->required()->native(false),
                    Toggle::make('all_day')->label('Dia inteiro'),
                    TextInput::make('location')->label('Local')->maxLength(255),
                ])
                ->action(function (array $data): void {
                    $tenant = tenant();

                    CalendarEvent::create([
                        'tenant_id' => $tenant->id,
                        'title' => $data['title'],
                        'description' => $data['description'] ?? null,
                        'category' => $data['category'],
                        'start_at' => $data['start_at'],
                        'end_at' => $data['end_at'],
                        'all_day' => (bool) ($data['all_day'] ?? false),
                        'location' => $data['location'] ?? null,
                        'status' => CalendarEventStatus::Scheduled,
                        'created_by' => auth()->id(),
                    ]);

                    $this->dispatch('refresh-calendar');
                }),
        ];
    }
}
