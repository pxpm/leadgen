<x-filament-panels::page>
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="mb-6 rounded-full bg-amber-100 p-4 dark:bg-amber-900">
            <x-heroicon-o-exclamation-triangle class="h-10 w-10 text-amber-600 dark:text-amber-400" />
        </div>

        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
            {{ __('admin.subscription_inactive.title') }}
        </h2>

        <p class="mt-2 max-w-md text-gray-500 dark:text-gray-400">
            {{ __('admin.subscription_inactive.message') }}
        </p>

        <div class="mt-8 flex gap-3">
            <a href="mailto:support@leadgen.com"
               class="inline-flex items-center rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-amber-500">
                {{ __('admin.common.contact_support') }}
            </a>

            <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center rounded-lg bg-gray-200 px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                    {{ __('admin.common.sign_out') }}
                </button>
            </form>
        </div>
    </div>
</x-filament-panels::page>
