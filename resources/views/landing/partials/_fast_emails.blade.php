<section class="relative py-28 px-6 bg-white overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-30"></div>

    <div class="relative max-w-4xl mx-auto">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.fast_emails.headline') }}
            </h2>
            <p class="mt-4 text-gray-500 text-lg">{{ __('landing.fast_emails.subtitle') }}</p>
        </div>

        <div class="mt-14 bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-2xl shadow-gray-200/60">
            <div class="bg-gray-900 text-white px-6 py-4 flex items-center gap-3">
                <span class="w-9 h-9 rounded-lg bg-amber-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <div>
                    <p class="font-semibold text-sm">{{ __('landing.fast_emails.email_subject') }}</p>
                    <p class="text-xs text-gray-400">{{ __('landing.fast_emails.email_to') }}</p>
                </div>
            </div>

            <div class="p-6 sm:p-8 space-y-4 text-sm text-gray-700 leading-relaxed">
                <p>{{ __('landing.fast_emails.email_body_1') }}</p>
                <p>{{ __('landing.fast_emails.email_body_2') }}</p>
                <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-gray-500">
                    <p class="font-medium text-gray-600 text-xs uppercase tracking-wide">{{ __('landing.fast_emails.email_details_label') }}</p>
                    <p>{{ __('landing.fast_emails.email_detail_1') }}</p>
                    <p>{{ __('landing.fast_emails.email_detail_2') }}</p>
                    <p>{{ __('landing.fast_emails.email_detail_3') }}</p>
                </div>
                <p>{{ __('landing.fast_emails.email_body_3') }}</p>
            </div>

            <div class="px-6 sm:px-8 pb-6 flex flex-wrap gap-3">
                <span class="px-3 py-1.5 bg-green-50 text-green-700 text-xs font-medium rounded-full border border-green-200">{{ __('landing.fast_emails.tag_ready') }}</span>
                <span class="px-3 py-1.5 bg-amber-50 text-amber-700 text-xs font-medium rounded-full border border-amber-200">{{ __('landing.fast_emails.tag_review') }}</span>
                <span class="px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-medium rounded-full border border-blue-200">{{ __('landing.fast_emails.tag_minutes') }}</span>
            </div>
        </div>
    </div>
</section>
