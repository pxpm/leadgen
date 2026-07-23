<section class="py-28 px-6 bg-gray-50/70">
    <div class="max-w-5xl mx-auto">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.custom_forms.headline') }}
            </h2>
            <p class="mt-4 text-gray-500 text-lg">{{ __('landing.custom_forms.subtitle') }}</p>
        </div>

        <div class="mt-14 grid md:grid-cols-2 rounded-3xl overflow-hidden border border-gray-200 shadow-xl shadow-gray-200/50">
            <div class="bg-gray-50/80 p-8 md:p-10 relative">
                <div class="absolute top-5 left-5 w-10 h-10 rounded-xl bg-gray-200 flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="mt-14">
                    <h3 class="text-xl font-bold text-gray-400 mb-6">{{ __('landing.custom_forms.old.title') }}</h3>
                    <ul class="space-y-4">
                        @foreach (['item_1', 'item_2', 'item_3'] as $item)
                            <li class="flex items-center gap-3 text-gray-400">
                                <svg class="w-5 h-5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ __('landing.custom_forms.old.'.$item) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-8 md:p-10 relative border-l-0 md:border-l border-gray-200">
                <div class="absolute top-5 right-5 w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </div>
                <div class="mt-14">
                    <h3 class="text-xl font-bold text-amber-700 mb-6">{{ __('landing.custom_forms.new.title') }}</h3>
                    <ul class="space-y-4">
                        @foreach (['item_1', 'item_2', 'item_3', 'item_4'] as $item)
                            <li class="flex items-center gap-3 text-amber-800 font-medium">
                                <div class="shrink-0 w-5 h-5 rounded-full bg-amber-200 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>{{ __('landing.custom_forms.new.'.$item) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
