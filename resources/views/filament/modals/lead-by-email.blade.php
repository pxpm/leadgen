<div class="space-y-6">
    {{-- Success state --}}
    @if ($this->leadCreated)
        <div class="rounded-lg border border-success-200 bg-success-50 p-6 text-center dark:border-success-800 dark:bg-success-950">
            <x-filament::icon icon="heroicon-o-check-circle" class="mx-auto size-12 text-success-500" />
            <h3 class="mt-3 text-lg font-semibold text-success-700 dark:text-success-300">
                {{ __('admin.manual_lead_intake.lead_created') }}
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('admin.manual_lead_intake.send_link_description') }}
            </p>
            <div class="mt-4">
                <x-filament::button wire:click="resetLeadForm" color="gray">
                    {{ __('admin.manual_lead_intake.new_lead') }}
                </x-filament::button>
            </div>
        </div>
    @elseif ($this->leadExtractedFields)
        {{-- Results: Extracted fields --}}
        <div>
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ __('admin.manual_lead_intake.section_extracted') }}
            </h4>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ count($this->leadExtractedFields) }} campos encontrados pela IA
            </p>

            <div class="mt-3 grid grid-cols-2 gap-3">
                @foreach ($this->leadExtractedFields as $key => $value)
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $key }}</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $value ?: '—' }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Missing fields --}}
        @if ($this->leadMissingFields)
            <div class="rounded-lg border border-danger-200 bg-danger-50 p-4 dark:border-danger-800 dark:bg-danger-950">
                <h4 class="text-sm font-semibold text-danger-700 dark:text-danger-300">
                    {{ __('admin.manual_lead_intake.section_missing') }}
                </h4>
                <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">
                    {{ __('admin.manual_lead_intake.missing_description') }}
                </p>
                <ul class="mt-2 list-disc pl-5 text-sm text-danger-600 dark:text-danger-400">
                    @foreach ($this->leadMissingFields as $field)
                        <li>{{ $field }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Action buttons --}}
        <div class="flex items-center justify-between gap-3">
            <x-filament::button wire:click="resetLeadForm" color="gray">
                {{ __('admin.common.cancel') }}
            </x-filament::button>
            <x-filament::button wire:click="createLeadFromEmail" color="success">
                {{ __('admin.manual_lead_intake.create_lead_send_link') }}
            </x-filament::button>
        </div>
    @else
        {{-- Form: Email text only, service is auto-detected --}}
        <form wire:submit="extractLeadData" class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('admin.manual_lead_intake.email_content') }}
                </label>
                <textarea wire:model="leadEmailText" rows="12"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    placeholder="{{ __('admin.manual_lead_intake.email_placeholder') }}"></textarea>
                @error('leadEmailText')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <x-filament::button type="submit" color="primary" class="w-full">
                {{ __('admin.manual_lead_intake.extract_data') }}
            </x-filament::button>
        </form>
    @endif
</div>
