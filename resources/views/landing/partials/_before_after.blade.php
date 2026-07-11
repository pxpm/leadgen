<section class="py-28 px-6 bg-white">
    <div class="max-w-6xl mx-auto">
        <div class="text-center max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 text-gray-500 text-xs font-bold tracking-wider uppercase rounded-full mb-6">Comparação</span>
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.before_after.headline') }}
            </h2>
        </div>

        <div class="mt-14 grid md:grid-cols-2 rounded-3xl overflow-hidden border border-gray-200 shadow-xl shadow-gray-200/50">
            {{-- Before --}}
            <div class="bg-gray-50 p-8 md:p-12 relative">
                <div class="absolute top-4 right-4 px-3 py-1 bg-red-100 text-red-600 text-xs font-bold rounded-full tracking-wide">ANTES</div>
                <h3 class="text-2xl font-bold text-gray-300 mb-8">{{ __('landing.before_after.before.title') }}</h3>
                <ul class="space-y-5">
                    @foreach (['item_1', 'item_2', 'item_3', 'item_4'] as $item)
                        <li class="flex items-start gap-4 text-gray-500 group">
                            <span class="shrink-0 w-8 h-8 rounded-full bg-red-50 flex items-center justify-center group-hover:bg-red-100 transition-colors">
                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </span>
                            <span class="text-lg">{{ __('landing.before_after.before.'.$item) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- After --}}
            <div class="bg-green-50/50 p-8 md:p-12 relative border-l-0 md:border-l border-gray-200">
                <div class="absolute top-4 right-4 px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full tracking-wide">DEPOIS</div>
                <h3 class="text-2xl font-bold text-green-200 mb-8">{{ __('landing.before_after.after.title') }}</h3>
                <ul class="space-y-5">
                    @foreach (['item_1', 'item_2', 'item_3', 'item_4'] as $item)
                        <li class="flex items-start gap-4 text-green-800 group">
                            <span class="shrink-0 w-8 h-8 rounded-full bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span class="text-lg font-medium">{{ __('landing.before_after.after.'.$item) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
