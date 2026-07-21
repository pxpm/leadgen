<x-filament-panels::page>
    <form wire:submit="save" class="max-w-md mx-auto space-y-6">
        <p class="text-gray-500 text-sm">
            Defina a sua password para aceder ao painel. Use pelo menos 8 caracteres.
        </p>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" wire:model="password"
                   class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all">
            @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Password</label>
            <input type="password" wire:model="passwordConfirmation"
                   class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all">
        </div>

        <x-filament::button type="submit" class="w-full">
            Definir Password
        </x-filament::button>
    </form>
</x-filament-panels::page>
