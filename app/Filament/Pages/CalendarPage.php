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
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class CalendarPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Calendário';

    protected static ?string $title = 'Calendário';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.calendar';

    public ?int $editingEventId = null;

    /** @var array<string, mixed> */
    public array $createData = [
        'title' => '',
        'description' => '',
        'category' => 'visit',
        'start_at' => null,
        'end_at' => null,
        'all_day' => false,
        'location' => '',
    ];

    /** @var array<string, mixed> */
    public array $editData = [
        'title' => '',
        'description' => '',
        'category' => 'visit',
        'start_at' => null,
        'end_at' => null,
        'all_day' => false,
        'location' => '',
    ];

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin() && request()->cookie('impersonating_tenant_id')) {
            return true;
        }

        return ! $user->isSuperAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin() && request()->cookie('impersonating_tenant_id')) {
            return true;
        }

        return ! $user->isSuperAdmin();
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
        $this->createData = [
            'start_at' => $start,
            'end_at' => $end ?? $start,
        ];
        $this->dispatch('open-modal', id: 'create-event-modal');
    }

    /**
     * Open the edit modal on event click.
     */
    public function openEditModal(int $id): void
    {
        $event = CalendarEvent::findOrFail($id);

        $this->editingEventId = $id;
        $this->editData = [
            'title' => $event->title,
            'description' => $event->description,
            'category' => $event->category->value,
            'start_at' => $event->start_at,
            'end_at' => $event->end_at,
            'all_day' => $event->all_day,
            'location' => $event->location,
        ];

        $this->dispatch('open-modal', id: 'edit-event-modal');
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

    // ── Inline forms (rendered in Blade modals) ──

    protected function getForms(): array
    {
        return ['editForm', 'createForm'];
    }

    protected function createForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('createData')
            ->schema([
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
            ]);
    }

    public function saveNewEvent(): void
    {
        $this->createForm->validate();

        $tenant = tenant();

        CalendarEvent::create([
            'tenant_id' => $tenant->id,
            'title' => $this->createData['title'],
            'description' => $this->createData['description'] ?? null,
            'category' => $this->createData['category'],
            'start_at' => $this->createData['start_at'],
            'end_at' => $this->createData['end_at'],
            'all_day' => (bool) ($this->createData['all_day'] ?? false),
            'location' => $this->createData['location'] ?? null,
            'status' => CalendarEventStatus::Scheduled,
            'created_by' => auth()->id(),
        ]);

        $this->createData = [];
        $this->dispatch('refresh-calendar');
        $this->dispatch('close-modal', id: 'create-event-modal');
    }

    protected function editForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('editData')
            ->schema([
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
            ]);
    }

    public function saveEvent(): void
    {
        $this->editForm->validate();

        $event = CalendarEvent::findOrFail($this->editingEventId);

        $event->update([
            'title' => $this->editData['title'],
            'description' => $this->editData['description'] ?? null,
            'category' => $this->editData['category'],
            'start_at' => $this->editData['start_at'],
            'end_at' => $this->editData['end_at'],
            'all_day' => (bool) ($this->editData['all_day'] ?? false),
            'location' => $this->editData['location'] ?? null,
        ]);

        $this->editingEventId = null;
        $this->editData = [];
        $this->dispatch('refresh-calendar');
        $this->dispatch('close-modal', id: 'edit-event-modal');
    }
}
