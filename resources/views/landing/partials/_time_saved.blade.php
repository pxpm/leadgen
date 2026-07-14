<section class="py-28 px-6 bg-gray-50/70 overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-40"></div>

    <div class="relative max-w-6xl mx-auto">
        <div class="text-center max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-600 text-xs font-bold tracking-wider uppercase rounded-full mb-6">Tempo Poupa-se</span>
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.time_saved.headline') }}
            </h2>
            <p class="mt-4 text-gray-500">{{ __('landing.time_saved.subtitle') }}</p>
        </div>

        {{-- Side-by-side comparison --}}
        <div class="mt-14 grid md:grid-cols-2 rounded-3xl overflow-hidden border border-gray-200 shadow-xl shadow-gray-200/50">

            {{-- Without — red/gray --}}
            <div class="bg-gray-50 p-8 md:p-12 relative">
                <div class="absolute top-4 right-4 px-3 py-1 bg-red-100 text-red-600 text-xs font-bold rounded-full tracking-wide">
                    {{ mb_strtoupper(__('landing.time_saved.without.title')) }}
                </div>
                <div class="flex items-center gap-3 mb-8">
                    <span class="shrink-0 w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center text-2xl">⏱️</span>
                    <h3 class="text-xl font-bold text-gray-400">{{ __('landing.time_saved.without.title') }}</h3>
                </div>
                <ul class="space-y-4 mb-8">
                    @foreach (['item_1', 'item_2', 'item_3', 'item_4', 'item_5'] as $item)
                        <li class="flex items-start gap-3 text-gray-500 group">
                            <span class="shrink-0 w-6 h-6 rounded-full bg-red-50 flex items-center justify-center mt-0.5 group-hover:bg-red-100 transition-colors">
                                <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </span>
                            <span class="text-base">{{ __('landing.time_saved.without.'.$item) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="border-t border-red-100 pt-4">
                    <p class="text-sm font-semibold text-red-500 leading-relaxed">
                        {{ __('landing.time_saved.without.result') }}
                    </p>
                </div>
            </div>

            {{-- With — green/amber --}}
            <div class="bg-gradient-to-br from-amber-50 to-green-50 p-8 md:p-12 relative border-l-0 md:border-l border-gray-200">
                <div class="absolute top-4 right-4 px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full tracking-wide">
                    {{ mb_strtoupper(__('landing.time_saved.with.title')) }}
                </div>
                <div class="flex items-center gap-3 mb-8">
                    <span class="shrink-0 w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center text-2xl">⚡</span>
                    <h3 class="text-xl font-bold text-green-700">{{ __('landing.time_saved.with.title') }}</h3>
                </div>
                <ul class="space-y-4 mb-8">
                    @foreach (['item_1', 'item_2', 'item_3', 'item_4', 'item_5'] as $item)
                        <li class="flex items-start gap-3 text-green-800 group">
                            <span class="shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center mt-0.5 group-hover:bg-green-200 transition-colors">
                                <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span class="text-base font-medium">{{ __('landing.time_saved.with.'.$item) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="border-t border-green-200 pt-4">
                    <p class="text-sm font-semibold text-green-600 leading-relaxed">
                        {{ __('landing.time_saved.with.result') }}
                    </p>
                </div>
            </div>
        </div>


    </div>
</section>
