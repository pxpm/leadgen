<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        @if ($this->shortLinkUrl)
            <div
                x-data="{
                    copied: false,
                    copy(text) {
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(text).then(() => {
                                this.copied = true;
                                setTimeout(() => this.copied = false, 2000);
                            });
                        } else {
                            const ta = document.createElement('textarea');
                            ta.value = text;
                            ta.style.position = 'fixed';
                            ta.style.opacity = '0';
                            document.body.appendChild(ta);
                            ta.select();
                            document.execCommand('copy');
                            document.body.removeChild(ta);
                            this.copied = true;
                            setTimeout(() => this.copied = false, 2000);
                        }
                    }
                }"
                class="fi-ta-card rounded-xl border border-gray-200 bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-gray-900 dark:ring-white/10"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-link" class="size-5 text-primary-500" />
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ __('admin.dashboard.link_generated') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('admin.dashboard.link_expires') }}
                            </p>
                        </div>
                    </div>
                    <x-filament::icon-button
                        icon="heroicon-o-x-mark"
                        color="gray"
                        size="sm"
                        wire:click="dismissLink"
                        :tooltip="__('Fechar')"
                    />
                </div>
                <div class="mt-3 inline-flex items-center gap-3">
                    <div
                        x-ref="urlText"
                        class="ml-0.5 max-w-lg truncate rounded-lg bg-gray-50 px-3 py-2 font-mono text-sm text-gray-700 select-all dark:bg-gray-800 dark:text-gray-200"
                    >{{ $this->shortLinkUrl }}</div>
                    <x-filament::button
                        color="primary"
                        size="sm"
                        x-on:click="copy($refs.urlText.textContent.trim())"
                    >
                        <span x-show="!copied">{{ __('admin.dashboard.copy') }}</span>
                        <span x-show="copied" x-cloak>{{ __('admin.dashboard.copied') }}</span>
                    </x-filament::button>
                </div>
            </div>
        @endif

        @foreach ($this->getWidgets() as $widget)
            @livewire($widget)
        @endforeach
    </div>
</x-filament-panels::page>
