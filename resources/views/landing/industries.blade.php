@extends('landing.layout')

@php
    $industries = __('landing.industries_section');
    $trades = array_filter($industries, fn ($v, $k) => is_array($v) && isset($v['name']), ARRAY_FILTER_USE_BOTH);
@endphp

@section('title', __('landing.industries_index.seo_title'))
@section('description', __('landing.industries_index.seo_description'))
@section('og_type', 'website')
@section('canonical', industries_url())

@push('jsonld_graph')
,
{
    "@@type": "BreadcrumbList",
    "@@id": "{{ industries_url() }}/#breadcrumb",
    "itemListElement": [
        {
            "@@type": "ListItem",
            "position": 1,
            "name": "{{ __('landing.site.title') }}",
            "item": "{{ url('/') }}"
        },
        {
            "@@type": "ListItem",
            "position": 2,
            "name": "{{ __('landing.industries_index.page_title') }}"
        }
    ]
},
{
    "@@type": "ItemList",
    "@@id": "{{ industries_url() }}/#list",
    "itemListElement": [
        @php $i = 1; @endphp
        @foreach ($trades as $key => $trade)
        {
            "@@type": "ListItem",
            "position": {{ $i }},
            "item": {
                "@@type": "Service",
                "name": "{{ $trade['name'] }}",
                "url": "{{ industry_url($key) }}",
                "description": "{{ __('landing.industry_pages.'.$key.'.seo_description') }}"
            }
        }@if ($i < count($trades)),@endif
        @php $i++; @endphp
        @endforeach
    ]
}
@endpush

@section('content')

{{-- Hero --}}
<section class="relative pt-36 pb-20 px-6 overflow-hidden bg-gradient-to-br from-amber-50 via-white to-gray-50">
    <div class="absolute inset-0 bg-dots opacity-30"></div>
    <div class="relative max-w-4xl mx-auto text-center">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-700 text-xs font-bold tracking-wider uppercase rounded-full mb-6">
            {{ __('landing.nav.industries') }}
        </span>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-gray-900 leading-[1.1]">
            {{ __('landing.industries_index.page_title') }}
        </h1>
        <p class="mt-6 text-lg text-gray-500 leading-relaxed max-w-2xl mx-auto">
            {{ __('landing.industries_index.subtitle') }}
        </p>
    </div>
</section>

{{-- Industries grid --}}
<section class="py-20 px-6 bg-white">
    <div class="max-w-6xl mx-auto">
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($trades as $key => $trade)
                @php
                    $page = __('landing.industry_pages.'.$key);
                    $servicesCount = count($page['services'] ?? []);
                @endphp
                <a href="{{ industry_url($key) }}"
                   class="group relative bg-white border border-gray-200 rounded-2xl overflow-hidden hover:border-amber-300 hover:shadow-xl transition-all">
                    <div class="h-1.5 bg-amber-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="p-6 sm:p-8">
                        <div class="flex items-center gap-4 mb-4">
                            <span class="text-4xl group-hover:scale-110 transition-transform inline-block">{{ $trade['icon'] }}</span>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 group-hover:text-amber-700 transition-colors">{{ $trade['name'] }}</h2>
                                @if ($servicesCount > 0)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $servicesCount }} {{ $servicesCount === 1 ? 'serviço' : 'serviços' }}</p>
                                @endif
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 leading-relaxed line-clamp-2">
                            {{ $page['seo_description'] ?? $page['hero_subheadline'] ?? '' }}
                        </p>
                        <div class="mt-4 flex flex-wrap gap-1.5">
                            @foreach (array_slice($page['services'] ?? [], 0, 3) as $svc)
                                <span class="px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded-md">
                                    {{ $svc['hero_headline'] ?? $svc['title'] }}
                                </span>
                            @endforeach
                        </div>
                        <span class="inline-flex items-center gap-1 mt-4 text-xs font-semibold text-amber-600 group-hover:translate-x-1 transition-transform">
                            Ver soluções <span>→</span>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- How it works — universal --}}
<section class="py-24 px-6 bg-gray-50/70">
    <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
            {{ __('landing.industries_index.how_it_works_title') }}
        </h2>
        <div class="mt-14 grid md:grid-cols-3 gap-8">
            <div class="bg-white border border-gray-200 rounded-2xl p-6 text-center hover:shadow-md transition-shadow">
                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center mx-auto mb-4">1</div>
                <h3 class="font-bold text-gray-900 mb-2">{{ __('landing.industries_index.step_1_title') }}</h3>
                <p class="text-sm text-gray-500">{{ __('landing.industries_index.step_1_desc') }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl p-6 text-center hover:shadow-md transition-shadow">
                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center mx-auto mb-4">2</div>
                <h3 class="font-bold text-gray-900 mb-2">{{ __('landing.industries_index.step_2_title') }}</h3>
                <p class="text-sm text-gray-500">{{ __('landing.industries_index.step_2_desc') }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl p-6 text-center hover:shadow-md transition-shadow">
                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center mx-auto mb-4">3</div>
                <h3 class="font-bold text-gray-900 mb-2">{{ __('landing.industries_index.step_3_title') }}</h3>
                <p class="text-sm text-gray-500">{{ __('landing.industries_index.step_3_desc') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-24 px-6 bg-white">
    <div class="max-w-2xl mx-auto text-center">
        <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
            {{ __('landing.industries_index.cta_title') }}
        </h2>
        <p class="mt-4 text-gray-500">{{ __('landing.industries_index.cta_subtitle') }}</p>
        <div class="mt-8">
            <button @click="showTrialModal = true" class="group inline-flex items-center px-6 py-3.5 text-base font-semibold text-white bg-amber-500 rounded-xl hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20 cursor-pointer">
                {{ __('landing.demo_form.submit') }}
                <span class="ml-2 transition-transform group-hover:translate-x-1">→</span>
            </button>
        </div>
    </div>
</section>

{{-- Back link --}}
<div class="py-8 px-6 text-center bg-gray-50/70">
    <a href="{{ url('/') }}" class="text-sm text-gray-500 hover:text-amber-600 transition-colors">
        ← {{ __('landing.site.title') }} — Página principal
    </a>
</div>

@endsection
