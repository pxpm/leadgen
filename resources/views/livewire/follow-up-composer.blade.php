<div wire:key="follow-up-composer-{{ $lead->id }}-{{ $scenario }}">
    @if ($emailSent)
        <div class="flex flex-col items-center py-12 text-center">
            <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-500 shadow-lg shadow-emerald-500/20">
                <x-heroicon-o-check class="h-8 w-8 text-white" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('emails.followup_sent.heading') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $lead->fields()->where('field_key', 'email')->first()?->field_value ?? 'Cliente' }}
            </p>
        </div>
        @return
    @endif

    {{-- Context --}}
    <div class="mb-6 flex items-center gap-3">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-sm font-bold text-white shadow-md shadow-blue-500/20">
            {{ strtoupper(mb_substr($lead->fields()->where('field_key', 'contact_name')->first()?->field_value ?? '?', 0, 2)) }}
        </div>
        <div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $lead->fields()->where('field_key', 'contact_name')->first()?->field_value ?? 'Cliente' }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">{{ implode(' + ', $lead->services ?: []) ?: '—' }}</p>
        </div>
    </div>

    {{-- Account selectors (only when tenant has sending accounts) --}}
    @if ($this->hasSendingAccounts)
        <div class="mb-6 grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Enviar de</label>
                <select wire:model.live="fromAccountId" class="block w-full rounded-xl border-gray-200 bg-white text-sm shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                    <option value="">Plataforma (noreply@liaweb.eu)</option>
                    @foreach ($this->accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->email }} ({{ $account->provider }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Responder para</label>
                <select wire:model.live="replyToAccountId" class="block w-full rounded-xl border-gray-200 bg-white text-sm shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                    <option value="">Notificação do tenant</option>
                    @foreach ($this->accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->email }} ({{ $account->provider }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    {{-- Grouped cards --}}
    @if ($this->hasGroups)
        <div class="mb-6 grid grid-cols-2 items-start gap-4">
            @foreach ($this->groups as $groupKey => $group)
                <div
                    @class([
                        'overflow-hidden rounded-3xl border transition-shadow duration-200',
                        'border-blue-200 bg-gradient-to-b from-blue-50 to-white shadow-md shadow-blue-100/50 dark:border-blue-800 dark:from-blue-500/10 dark:to-gray-800 dark:shadow-blue-500/5' => in_array($groupKey, $expandedGroups),
                        'border-gray-200 bg-white shadow-sm hover:shadow-md dark:border-gray-700 dark:bg-gray-800' => ! in_array($groupKey, $expandedGroups),
                    ])
                >
                    <button
                        type="button"
                        wire:click="toggleGroup('{{ $groupKey }}')"
                        class="flex w-full items-center justify-between gap-2 px-5 py-4 text-left"
                    >
                        <span @class([
                            'text-sm font-semibold',
                            'text-blue-700 dark:text-blue-300' => in_array($groupKey, $expandedGroups),
                            'text-gray-800 dark:text-gray-200' => ! in_array($groupKey, $expandedGroups),
                        ])>{{ $group['label'] }}</span>
                        <span @class([
                            'flex h-6 w-6 items-center justify-center rounded-full transition-all duration-200',
                            'bg-blue-500 text-white shadow-sm shadow-blue-500/30 rotate-180' => in_array($groupKey, $expandedGroups),
                            'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500' => ! in_array($groupKey, $expandedGroups),
                        ])>
                            <x-heroicon-o-chevron-down class="h-3.5 w-3.5" />
                        </span>
                    </button>
                    @if (in_array($groupKey, $expandedGroups))
                        <div class="border-t border-blue-100 px-5 pb-4 pt-3 dark:border-blue-500/20">
                            <div class="flex flex-wrap gap-2.5">
                                @foreach ($group['reasons'] as $reasonKey => $reasonLabel)
                                    <button
                                        type="button"
                                        wire:click="toggleItem('{{ $reasonKey }}')"
                                        @class([
                                            'rounded-xl border px-2.5 py-1.5 text-xs font-medium transition-all duration-150',
                                            'border-blue-300 bg-gradient-to-b from-blue-50 to-blue-100 text-blue-700 shadow-sm hover:shadow-md dark:border-blue-500 dark:from-blue-500/20 dark:to-blue-500/10 dark:text-blue-300' => in_array($reasonKey, $selectedItems),
                                            'border-gray-200 bg-white text-gray-600 shadow-sm hover:border-gray-300 hover:shadow dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:border-gray-500' => ! in_array($reasonKey, $selectedItems),
                                        ])
                                    >
                                        @if (in_array($reasonKey, $selectedItems))
                                            <x-heroicon-o-check class="mr-0.5 inline h-3 w-3 text-blue-500" />
                                        @endif
                                        {{ $reasonLabel }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @elseif ($this->reasons)
        {{-- Flat pills --}}
        <div class="mb-6">
            <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                @if ($scenario === 'request_info') Informação necessária
                @elseif ($scenario === 'quote_followup') Fase do acompanhamento
                @else Motivo @endif
            </p>
            <div class="flex flex-wrap gap-2.5">
                @foreach ($this->reasons as $key => $label)
                    <button
                        type="button"
                        wire:click="toggleItem('{{ $key }}')"
                        @class([
                            'rounded-2xl border px-4 py-2.5 text-sm font-medium shadow-sm transition-all duration-150',
                            'border-blue-300 bg-gradient-to-b from-blue-50 to-blue-100 text-blue-700 hover:shadow-md dark:border-blue-500 dark:from-blue-500/20 dark:to-blue-500/10 dark:text-blue-300' => in_array($key, $selectedItems),
                            'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:shadow dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-500' => ! in_array($key, $selectedItems),
                        ])
                    >
                        @if (in_array($key, $selectedItems))
                            <x-heroicon-o-check class="mr-0.5 inline h-3 w-3 text-blue-500" />
                        @endif
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Notes --}}
    <textarea
        wire:model.blur="freeText" rows="3"
        placeholder="Notas adicionais (opcional)..."
        class="mb-6 block w-full rounded-xl border-gray-200 bg-white text-sm shadow-sm placeholder:text-gray-400 transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:placeholder:text-gray-500"
    ></textarea>

    {{-- Generate --}}
    @if (! $showPreview)
        @if ($errorMessage)
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-500/10 dark:text-red-400">
                {{ $errorMessage }}
            </div>
        @endif
        <button
            wire:click.prevent="generateEmail"
            wire:loading.attr="disabled"
            wire:target="generateEmail"
            @if ($scenario !== 'general' && empty($selectedItems)) disabled @endif
            class="flex w-full items-center justify-center gap-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:from-blue-500 hover:to-blue-600 hover:shadow-xl hover:shadow-blue-500/30 disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none"
        >
            <span wire:loading.remove wire:target="generateEmail" class="inline-flex items-center gap-1.5"><x-heroicon-o-sparkles class="h-5 w-5" /> Gerar email</span>
            <span wire:loading wire:target="generateEmail" class="inline-flex items-center gap-1.5"><x-heroicon-o-arrow-path class="h-5 w-5 animate-spin" /> A gerar...</span>
        </button>
    @endif

    {{-- Preview --}}
    @if ($showPreview && $generatedEmail)
        <div class="space-y-4">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md dark:border-gray-600 dark:bg-gray-800">
                <div class="flex items-center gap-2.5 border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                    <span class="h-2.5 w-2.5 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-500 shadow-sm shadow-emerald-500/30"></span>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Pre-visualizacao</span>
                    <span class="ml-auto text-xs text-gray-400 dark:text-gray-500">Pode editar antes de enviar</span>
                </div>
                <textarea wire:model.live="generatedEmail" rows="9" class="block w-full border-0 bg-white px-5 py-4 font-sans text-sm leading-relaxed text-gray-800 focus:ring-0 dark:bg-gray-800 dark:text-gray-200"></textarea>
            </div>
            <div class="flex gap-3">
                <button wire:click.prevent="clearGenerated" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50 hover:shadow dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                    <x-heroicon-o-arrow-path class="mr-1.5 inline h-4 w-4" />Gerar novamente
                </button>
                <button wire:click.prevent="sendEmail" wire:loading.attr="disabled" wire:target="sendEmail" class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition-all hover:from-emerald-400 hover:to-emerald-500 hover:shadow-xl hover:shadow-emerald-500/30 disabled:opacity-40 disabled:shadow-none">
                    <span wire:loading.remove wire:target="sendEmail" class="inline-flex items-center gap-1.5"><x-heroicon-o-paper-airplane class="h-4 w-4 shrink-0" />Enviar email</span>
                    <span wire:loading wire:target="sendEmail" class="inline-flex items-center gap-1.5"><x-heroicon-o-arrow-path class="h-4 w-4 shrink-0 animate-spin" />A enviar...</span>
                </button>
            </div>
        </div>
    @endif
</div>
