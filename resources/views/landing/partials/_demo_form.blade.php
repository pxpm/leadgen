<div x-data="{
    submitted: false,
    error: false,
    loading: false,
    form: { name: '', email: '', phone: '', company: '', industry: '', message: '' },
    async submitForm() {
        this.loading = true;
        this.error = false;
        try {
            const res = await fetch('/demo-request', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(this.form),
            });
            if (!res.ok) throw new Error();
            this.submitted = true;
        } catch (e) {
            this.error = true;
        } finally {
            this.loading = false;
        }
    }
}" class="mt-12">
    {{-- Success --}}
    <div x-show="submitted" x-cloak class="bg-green-50 border border-green-200 rounded-2xl p-8 text-center">
        <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h3 class="text-xl font-bold text-green-800">{{ __('landing.demo_form.success_title') }}</h3>
        <p class="mt-2 text-green-600">{{ __('landing.demo_form.success_message') }}</p>
    </div>

    {{-- Form --}}
    <div x-show="!submitted" class="bg-white border border-gray-200 rounded-2xl p-6 sm:p-8 shadow-xl shadow-gray-200/30">
        <div class="space-y-5">
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('landing.demo_form.name') }} *</label>
                    <input type="text" x-model="form.name" required
                           placeholder="{{ __('landing.demo_form.name_placeholder') }}"
                           class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('landing.demo_form.email') }} *</label>
                    <input type="email" x-model="form.email" required
                           placeholder="{{ __('landing.demo_form.email_placeholder') }}"
                           class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all">
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
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('landing.demo_form.company') }}</label>
                    <input type="text" x-model="form.company"
                           placeholder="{{ __('landing.demo_form.company_placeholder') }}"
                           class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('landing.demo_form.industry') }}</label>
                <select x-model="form.industry"
                        class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all">
                    <option value="">{{ __('landing.demo_form.industry_placeholder') }}</option>
                    @php
                        $industries = __('landing.industries_section');
                        $trades = array_filter($industries, fn ($v, $k) => is_array($v) && isset($v['name']), ARRAY_FILTER_USE_BOTH);
                    @endphp
                    @foreach ($trades as $key => $trade)
                        <option value="{{ $key }}">{{ $trade['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('landing.demo_form.message') }}</label>
                <textarea x-model="form.message" rows="3"
                          placeholder="{{ __('landing.demo_form.message_placeholder') }}"
                          class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition-all resize-none"></textarea>
            </div>

            {{-- Error --}}
            <div x-show="error" x-cloak class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-600">
                {{ __('landing.demo_form.error_message') }}
            </div>

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
