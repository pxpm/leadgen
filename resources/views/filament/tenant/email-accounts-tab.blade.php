<div class="space-y-6">
    @php
        $accounts = \App\Models\TenantEmailAccount::where('tenant_id', $tenant->id)
            ->orderBy('email')
            ->get();
    @endphp

    {{-- Add buttons --}}
    <div class="flex gap-3">
        <a href="{{ \App\Filament\Resources\TenantResource::getUrl('edit', ['record' => $tenant]) }}"
           class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-btn-color-primary gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-primary-600 text-white hover:bg-primary-500">
            <x-heroicon-o-plus class="h-5 w-5" />
            Gerir contas de email
        </a>
    </div>

    {{-- Existing accounts list --}}
    @if ($accounts->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 px-6 py-12 text-center dark:border-gray-600">
            <x-heroicon-o-envelope class="mx-auto h-10 w-10 text-gray-400" />
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Nenhuma conta de email configurada.</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                Adicione uma conta Gmail, Outlook ou SMTP personalizado na página de edição do tenant.
            </p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Fornecedor</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Email</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Tipo</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($accounts as $account)
                        <tr class="bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800">
                            <td class="px-4 py-3">
                                <span class="font-medium">
                                    {{ match ($account->provider) {
                                        'google' => '🔵 Gmail',
                                        'microsoft' => '🔷 Outlook',
                                        default => '⚙️ ' . ($account->provider ?? 'SMTP'),
                                    } }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $account->email }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                    'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400' => $account->isVerified(),
                                    'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400' => !$account->isVerified(),
                                ])>
                                    {{ $account->isVerified() ? '✓ Verificada' : '⏳ Pendente' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                    'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400' => $account->isActive(),
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' => !$account->isActive(),
                                ])>
                                    {{ $account->isActive() ? 'Ativa' : 'Inativa' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
