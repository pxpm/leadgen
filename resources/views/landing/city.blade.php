@extends('landing.layout')

@php
    $cityKey = $cityKey ?? 'lisboa';
    $city = __('landing.city_pages.'.$cityKey);
    $cityName = match($cityKey) {
        'lisboa' => 'Lisboa',
        'porto' => 'Porto',
        'algarve' => 'Algarve',
        'minho' => 'Minho',
        'alentejo' => 'Alentejo',
        default => $cityKey,
    };
    $industries = __('landing.industries_section');
    $trades = array_filter($industries, fn ($v, $k) => is_array($v) && isset($v['name']), ARRAY_FILTER_USE_BOTH);
@endphp

@section('title', $city['seo_title'])
@section('description', $city['seo_description'])
@section('canonical', url('/orcamentos-'.$cityKey))

@push('jsonld_graph')
,
{
    "@@type": "Service",
    "@@id": "{{ url('/orcamentos-'.$cityKey) }}/#service",
    "name": "Orçamentos em {{ $cityName }}",
    "description": "{{ $city['seo_description'] }}",
    "provider": { "@@id": "{{ url('/') }}/#organization" },
    "areaServed": {
        "@@type": "City",
        "name": "{{ $cityName }}"
    }
}
@endpush

@section('content')

<section class="relative pt-36 pb-20 px-6 overflow-hidden bg-gradient-to-br from-amber-50 via-white to-gray-50">
    <div class="absolute inset-0 bg-dots opacity-30"></div>
    <div class="relative max-w-4xl mx-auto text-center">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-700 text-xs font-bold tracking-wider uppercase rounded-full mb-6">
            {{ $cityName }}
        </span>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-gray-900 leading-[1.1]">
            {{ $city['hero_headline'] }}
        </h1>
        <p class="mt-6 text-lg text-gray-500 leading-relaxed max-w-2xl mx-auto">
            {{ $city['hero_subheadline'] }}
        </p>
        <div class="mt-8">
            <button @click="showTrialModal = true" class="group inline-flex items-center px-6 py-3.5 text-base font-semibold text-white bg-amber-500 rounded-xl hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20 cursor-pointer">
                {{ __('landing.demo_form.submit') }}
                <span class="ml-2 transition-transform group-hover:translate-x-1">→</span>
            </button>
        </div>
    </div>
</section>

{{-- Industries in this city --}}
<section class="py-20 px-6 bg-white">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 text-center mb-12">
            Serviços disponíveis em {{ $cityName }}
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach ($trades as $key => $trade)
                <a href="{{ industry_url($key) }}"
                   class="group relative bg-white border border-gray-200 rounded-2xl p-6 text-center hover:border-amber-300 hover:shadow-lg transition-all overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-0.5 bg-amber-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <span class="text-3xl block mb-3 group-hover:scale-110 transition-transform inline-block">{{ $trade['icon'] }}</span>
                    <p class="text-sm font-semibold text-gray-700">{{ $trade['name'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">em {{ $cityName }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>

@include('landing.partials._final_cta')

@endsection
