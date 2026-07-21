<div x-data="{
    submitted: false,
    error: false,
    loading: false,
    isOutro: false,
    errors: {},
    errorMessage: '',
    form: { name: '', email: '', phone: '', company: '', industry: '', industry_other: '' },
    async submitForm() {
        this.loading = true;
        this.error = false;
        this.errors = {};
        this.errorMessage = '';

        // Grab Turnstile token if widget is present
        const turnstileToken = typeof turnstile !== 'undefined' ? turnstile.getResponse() : null;

        try {
            const res = await fetch('/trial-signup', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ ...this.form, turnstile_token: turnstileToken }),
            });
            if (res.status === 419) {
                this.error = true;
                this.errorMessage = '{{ __('landing.demo_form.error_message') }}';
                window.location.reload();
                return;
            }
            if (res.ok) {
                const data = await res.json();
                this.isOutro = data.outro === true;
                this.submitted = true;
                return;
            }
            const data = await res.json();
            if (data.errors) {
                this.errors = data.errors;
            } else {
                this.error = true;
                this.errorMessage = data.message || '{{ __('landing.demo_form.error_message') }}';
            }
        } catch (e) {
            this.error = true;
            this.errorMessage = '{{ __('landing.demo_form.error_message') }}';
        } finally {
            this.loading = false;
        }
    },
    fieldError(field) {
        const errs = this.errors[field];
        return errs ? errs[0] : null;
    }
}">
    {{-- Success: normal trial --}}
    <div x-show="submitted && !isOutro" x-cloak class="bg-green-50 border border-green-200 rounded-2xl p-8 text-center">
        <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h3 class="text-xl font-bold text-green-800">{{ __('landing.demo_form.success_title') }}</h3>
        <p class="mt-2 text-green-600">{{ __('landing.demo_form.success_message') }}</p>
    </div>

    {{-- Success: outro (demo request) --}}
    <div x-show="submitted && isOutro" x-cloak class="bg-blue-50 border border-blue-200 rounded-2xl p-8 text-center">
        <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <h3 class="text-xl font-bold text-blue-800">{{ __('landing.demo_form.outro_title') }}</h3>
        <p class="mt-2 text-blue-600">{{ __('landing.demo_form.outro_message') }}</p>
    </div>

    {{-- Generic error (shown at top level, outside the form wrapper) --}}
    <div x-show="error && !submitted" class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-600 mb-4">
        <span x-text="errorMessage || '{{ __('landing.demo_form.error_message') }}'"></span>
    </div>

    {{-- Form --}}
    <div x-show="!submitted" class="space-y-5">
            {{-- Social login buttons --}}
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('social.redirect', 'google') }}"
                   class="flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Google
                </a>
                <a href="{{ route('social.redirect', 'facebook') }}"
                   class="flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-white bg-[#1877F2] border border-[#1877F2] rounded-xl hover:bg-[#166fe5] transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    Facebook
                </a>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex-1 border-t border-gray-200"></div>
                <span class="text-xs text-gray-400">ou</span>
                <div class="flex-1 border-t border-gray-200"></div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('landing.demo_form.name') }} *</label>
                    <input type="text" x-model="form.name" required
                           placeholder="{{ __('landing.demo_form.name_placeholder') }}"
                           :class="fieldError('name') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-gray-200 focus:border-amber-300 focus:ring-amber-100'"
                           class="w-full px-4 py-3 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 transition-all">
                    <p x-show="fieldError('name')" x-text="fieldError('name')" class="mt-1 text-xs text-red-500"></p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('landing.demo_form.email') }} *</label>
                    <input type="email" x-model="form.email" required
                           placeholder="{{ __('landing.demo_form.email_placeholder') }}"
                           :class="fieldError('email') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-gray-200 focus:border-amber-300 focus:ring-amber-100'"
                           class="w-full px-4 py-3 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 transition-all">
                    <p x-show="fieldError('email')" x-text="fieldError('email')" class="mt-1 text-xs text-red-500"></p>
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('landing.demo_form.phone') }}</label>
                    <input type="tel" x-model="form.phone"
                           placeholder="{{ __('landing.demo_form.phone_placeholder') }}"
                           class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('landing.demo_form.company') }} *</label>
                    <input type="text" x-model="form.company"
                           placeholder="{{ __('landing.demo_form.company_placeholder') }}"
                           :class="fieldError('company') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-gray-200 focus:border-amber-300 focus:ring-amber-100'"
                           class="w-full px-4 py-3 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 transition-all">
                    <p x-show="fieldError('company')" x-text="fieldError('company')" class="mt-1 text-xs text-red-500"></p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('landing.demo_form.industry') }} *</label>
                <select x-model="form.industry"
                        :class="fieldError('industry') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-gray-200 focus:border-amber-300 focus:ring-amber-100'"
                        class="w-full px-4 py-3 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 transition-all">
                    <option value="">{{ __('landing.demo_form.industry_placeholder') }}</option>
                    @php
                        $industries = __('landing.industries_section');
                        $trades = array_filter($industries, fn ($v, $k) => is_array($v) && isset($v['name']), ARRAY_FILTER_USE_BOTH);
                    @endphp
                    @foreach ($trades as $key => $trade)
                        <option value="{{ $key }}">{{ $trade['name'] }}</option>
                    @endforeach
                </select>
                <p x-show="fieldError('industry')" x-text="fieldError('industry')" class="mt-1 text-xs text-red-500"></p>
            </div>

            {{-- Other industry — shown when "Outro" is selected --}}
            <div x-show="form.industry === 'outro'" x-cloak>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('landing.demo_form.industry_other_label') }} *</label>
                <input type="text" x-model="form.industry_other" required
                       placeholder="{{ __('landing.demo_form.industry_other_placeholder') }}"
                       :class="fieldError('industry_other') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-gray-200 focus:border-amber-300 focus:ring-amber-100'"
                       class="w-full px-4 py-3 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 transition-all">
                <p x-show="fieldError('industry_other')" x-text="fieldError('industry_other')" class="mt-1 text-xs text-red-500"></p>
            </div>

            {{-- Honeypot — hidden from humans, filled by bots --}}
            <div style="position: absolute; left: -9999px;" aria-hidden="true">
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>

            {{-- Turnstile — shown only when key is configured --}}
            @if(config('services.turnstile.site_key'))
            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-size="flexible"></div>
            @endif

            <button @click="submitForm()" :disabled="loading"
                    class="w-full px-6 py-3.5 text-base font-semibold text-white bg-amber-500 rounded-xl hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-amber-500/20">
                <span x-show="!loading">{{ __('landing.demo_form.submit') }}</span>
                <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    {{ __('landing.demo_form.submitting') }}
                </span>
            </button>
        </div>
    </div>
</div>
