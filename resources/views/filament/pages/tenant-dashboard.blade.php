<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        @foreach ($this->getWidgets() as $widget)
            @livewire($widget)
        @endforeach
    </div>
</x-filament-panels::page>
