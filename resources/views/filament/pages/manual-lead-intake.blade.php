<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left: Form --}}
        <div class="lg:col-span-1">
            <x-filament::section>
                <x-slot name="heading">Email do Cliente</x-slot>

                <form wire:submit="extractData" class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Serviço</label>
                        <select wire:model="serviceType" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-800">
                            <option value="">Selecionar...</option>
                            @foreach ($this->serviceOptions as $key => $name)
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Conteúdo do Email</label>
                        <textarea wire:model="emailText" rows="10"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-800"
                            placeholder="Cola aqui o texto do email do cliente..."></textarea>
                    </div>

                    <x-filament::button type="submit" color="gray" class="w-full">
                        Extrair Dados
                    </x-filament::button>
                </form>
            </x-filament::section>
        </div>

        {{-- Right: Results --}}
        <div class="lg:col-span-2">
            @if ($extractedFields)
                <x-filament::section>
                    <x-slot name="heading">Campos Extraídos</x-slot>
                    <x-slot name="description">{{ count($extractedFields) }} campos encontrados pela IA</x-slot>

                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($extractedFields as $key => $value)
                            <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $key }}</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $value ?: '—' }}</div>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            @endif

            @if ($missingFields)
                <x-filament::section class="mt-4">
                    <x-slot name="heading">Campos em Falta</x-slot>
                    <x-slot name="description">Estes campos são obrigatórios e ainda não foram preenchidos</x-slot>

                    <ul class="list-disc pl-5 text-sm text-danger-600 dark:text-danger-400">
                        @foreach ($missingFields as $field)
                            <li>{{ $field }}</li>
                        @endforeach
                    </ul>
                </x-filament::section>

                <div class="mt-4">
                    <x-filament::button wire:click="createLeadAndSendLink" color="success">
                        Criar Lead e Enviar Link
                    </x-filament::button>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        O cliente receberá um link para continuar o processo e fornecer os campos em falta.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
