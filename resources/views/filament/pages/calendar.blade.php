<x-filament-panels::page>
    @push('scripts')
    @vite('resources/js/calendar.js')
    @endpush

    <div wire:ignore x-data="calendar($wire)" id="calendar"></div>

    {{-- Edit Modal --}}
    <x-filament::modal id="edit-event-modal" width="lg">
        <x-slot name="heading">Editar Evento</x-slot>
        <form wire:submit="saveEvent" class="space-y-4">
            {{ $this->editForm }}
        </form>
    </x-filament::modal>

    {{-- Create Modal --}}
    <x-filament::modal id="create-event-modal" width="md">
        <x-slot name="heading">Novo Evento</x-slot>
        <form wire:submit="saveNewEvent" class="space-y-4">
            {{ $this->createForm }}
        </form>
    </x-filament::modal>
</x-filament-panels::page>
