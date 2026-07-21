<section id="industries" class="py-28 px-6 bg-gray-50/70">
    <div class="max-w-7xl mx-auto">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.industries_section.headline') }}
            </h2>
            <p class="mt-4 text-gray-500">{{ __('landing.industries_section.subtitle') }}</p>
        </div>

        <div class="mt-14 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @php
                $industries = __('landing.industries_section');
                $trades = array_filter($industries, fn ($v, $k) => is_array($v) && isset($v['name']), ARRAY_FILTER_USE_BOTH);
            @endphp
            @foreach ($trades as $key => $trade)
                <a href="{{ industry_url($key) }}"
                   class="group relative bg-white border border-gray-200 rounded-2xl p-6 text-center hover:border-amber-300 hover:shadow-lg transition-all overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-0.5 bg-amber-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <span class="text-3xl block mb-3 group-hover:scale-110 transition-transform inline-block">{{ $trade['icon'] }}</span>
                    <p class="text-sm font-semibold text-gray-700">{{ $trade['name'] }}</p>
                </a>
            @endforeach
        </div>

        {{-- Not listed --}}
        <div class="mt-8 text-center">
            <button @click="showTrialModal = true" class="inline-flex items-center gap-2 px-6 py-4 bg-white border-2 border-dashed border-gray-300 rounded-2xl text-sm text-gray-500 hover:border-amber-300 hover:text-amber-600 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                A sua indústria não está aqui? Contacte-nos.
            </button>
        </div>
    </div>
</section>
