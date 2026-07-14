<section id="industries" class="py-28 px-6 bg-gray-50/70">
    <div class="max-w-7xl mx-auto">
        <div class="text-center max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-200/50 text-gray-500 text-xs font-bold tracking-wider uppercase rounded-full mb-6">{{ __('landing.nav.industries') }}</span>
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
    </div>
</section>
