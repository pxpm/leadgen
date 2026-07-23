<x-filament-panels::page.simple>
    <div
        class="space-y-6"
        x-data="{
            email: $wire.entangle('data.email'),
            get emailValid() {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email || '');
            },
        }"
    >
        {{-- Logo --}}
        <div class="flex justify-center">
            <a href="/" class="flex items-center gap-2">
                <img src="/logo.svg" alt="{{ config('app.name') }} logo" class="w-10 h-10 rounded-lg">
                <span class="text-xl font-bold tracking-tight text-gray-900">{{ config('app.name', 'Lead Intake Assistant') }}</span>
            </a>
        </div>

        {{-- Magic link sent confirmation --}}
        @if ($magicLinkSent)
            <div class="bg-green-50 border border-green-200 rounded-2xl p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-green-800">{{ __('auth.login.magic_link_sent_title') }}</p>
                <p class="text-xs text-green-600 mt-1">{{ __('auth.login.magic_link_sent_message') }}</p>
            </div>
        @endif

        {{-- Login form --}}
        @if (! $magicLinkSent)
            <form wire:submit="authenticate" class="space-y-4">
                {{-- Email --}}
                <div>
                    <input
                        type="email"
                        x-model="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="{{ __('auth.login.magic_link_placeholder') }}"
                        class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all"
                    >
                    @error('data.email')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Magic link + password — shown after valid email --}}
                <div x-show="emailValid" x-cloak class="space-y-3">
                    {{-- Magic link --}}
                    <button type="button" wire:click="sendMagicLink"
                            class="text-sm text-amber-600 hover:text-amber-700 underline transition-colors">
                        {{ __('auth.login.magic_link_send') }}
                    </button>

                    {{-- Password --}}
                    <div>
                        <input
                            type="password"
                            wire:model="data.password"
                            required
                            autocomplete="current-password"
                            placeholder="{{ __('filament-panels::auth/pages/login.form.password.label') }}"
                            class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all"
                        >
                        @error('data.password')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember me --}}
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="data.remember"
                            class="rounded border-gray-300 text-amber-500 focus:ring-amber-100"
                        >
                        {{ __('filament-panels::auth/pages/login.form.remember.label') }}
                    </label>

                    {{-- Sign in button --}}
                    <button type="submit"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition-colors">
                        {{ __('auth.login.title') }}
                    </button>
                </div>
            </form>
        @endif

        {{-- Social login buttons --}}
        @if(config('app.is_live_mode'))
        <div class="flex items-center gap-3">
            <div class="flex-1 border-t border-gray-200"></div>
            <span class="text-xs text-gray-400">{{ __('auth.login.or') }}</span>
            <div class="flex-1 border-t border-gray-200"></div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <a href="{{ $this->getGoogleLoginUrl() }}"
               class="flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                {{ __('auth.login.google') }}
            </a>

            <a href="{{ $this->getFacebookLoginUrl() }}"
               class="flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                {{ __('auth.login.facebook') }}
            </a>
        </div>
        @endif
    </div>
</x-filament-panels::page.simple>
