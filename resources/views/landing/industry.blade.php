@extends('landing.layout')

@php
    $industryKey = $industryKey ?? 'roofing';
    $page = __('landing.industry_pages.'.$industryKey);
    $industryName = __('landing.industries_section.'.$industryKey.'.name');
@endphp

@section('title', $page['seo_title'] ?? $industryName)
@section('description', $page['seo_description'] ?? '')
@section('og_type', 'website')
@section('canonical', industry_url($industryKey))

@push('jsonld_graph')
,
{
    "@@type": "BreadcrumbList",
    "@@id": "{{ industry_url($industryKey) }}/#breadcrumb",
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
            "name": "{{ $industryName }}"
        }
    ]
},
{
    "@@type": "Service",
    "@@id": "{{ industry_url($industryKey) }}/#service",
    "name": "{{ __('landing.industries_section.'.$industryKey.'.name') }}",
    "description": "{{ $page['seo_description'] ?? '' }}",
    "provider": { "@@id": "{{ url('/') }}/#organization" },
    "serviceType": "{{ $industryName }}",
    "areaServed": {
        "@@type": "Country",
        "name": "Portugal"
    }
}
@endpush

@if (!empty($page['faq']))
@push('jsonld_graph')
,
{
    "@@type": "FAQPage",
    "@@id": "{{ industry_url($industryKey) }}/#faq",
    "mainEntity": [
        @foreach ($page['faq'] as $faqItem)
        {
            "@@type": "Question",
            "name": "{{ $faqItem['q'] }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ $faqItem['a'] }}"
            }
        }@if (!$loop->last),@endif
        @endforeach
    ]
}
@endpush
@endif

@section('content')

{{-- Hero — industry-specific --}}
<section class="relative pt-36 pb-24 px-6 overflow-hidden bg-gradient-to-br from-amber-50 via-white to-gray-50">
    <div class="absolute inset-0 bg-dots opacity-30"></div>
    <div class="relative max-w-4xl mx-auto text-center">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-700 text-xs font-bold tracking-wider uppercase rounded-full mb-6">
            {{ $industryName }}
        </span>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-gray-900 leading-[1.1]">
            {{ $page['hero_headline'] ?? '' }}
        </h1>
        <p class="mt-6 text-lg text-gray-500 leading-relaxed max-w-2xl mx-auto">
            {{ $page['hero_subheadline'] ?? '' }}
        </p>
        <div class="mt-8">
            <button @click="showTrialModal = true" class="group inline-flex items-center px-6 py-3.5 text-base font-semibold text-white bg-amber-500 rounded-xl hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20 cursor-pointer">
                {{ __('landing.demo_form.submit') }}
                <span class="ml-2 transition-transform group-hover:translate-x-1">→</span>
            </button>
        </div>
    </div>
</section>

{{-- How it works — 3 steps --}}
@if (!empty($page['steps']))
<section class="py-24 px-6 bg-white">
    <div class="max-w-5xl mx-auto">
        <div class="text-center max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 text-gray-500 text-xs font-bold tracking-wider uppercase rounded-full mb-6">
                {{ __('landing.nav.how_it_works') }}
            </span>
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                Como funciona para {{ mb_strtolower($industryName) }}
            </h2>
        </div>
        <div class="mt-14 grid md:grid-cols-3 gap-8">
            @foreach ($page['steps'] as $i => $step)
                <div class="relative text-center">
                    <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-700 font-bold text-lg flex items-center justify-center mx-auto mb-4">
                        {{ $i + 1 }}
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $step['title'] }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Benefits --}}
@if (!empty($page['benefits']))
<section class="py-24 px-6 bg-gray-50/70">
    <div class="max-w-4xl mx-auto">
        <div class="text-center max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-50 text-green-600 text-xs font-bold tracking-wider uppercase rounded-full mb-6">Benefícios</span>
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                O que ganha com o assistente
            </h2>
        </div>
        <div class="mt-14 grid sm:grid-cols-2 gap-4">
            @foreach ($page['benefits'] as $benefit)
                <div class="flex items-start gap-4 bg-white border border-gray-200 rounded-2xl p-5 hover:border-amber-200 hover:shadow-md transition-all">
                    <span class="shrink-0 w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center mt-0.5">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <p class="text-gray-700 text-sm leading-relaxed pt-0.5">{{ $benefit }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Services we cover --}}
@php $services = $page['services'] ?? []; @endphp
@if (count($services) > 0)
<section class="py-24 px-6 bg-white">
    <div class="max-w-5xl mx-auto">
        <div class="text-center max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-600 text-xs font-bold tracking-wider uppercase rounded-full mb-6">Serviços</span>
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                O que qualificamos em {{ mb_strtolower($industryName) }}
            </h2>
            <p class="mt-4 text-gray-500">Cada serviço tem perguntas de qualificação específicas. O assistente adapta-se automaticamente.</p>
        </div>
        <div class="mt-14 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($services as $svc)
                <a href="{{ service_url($industryKey, $svc['slug']) }}"
                   class="group relative bg-white border border-gray-200 rounded-2xl p-6 hover:border-amber-300 hover:shadow-md transition-all overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-0.5 bg-amber-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <h3 class="font-bold text-gray-900 mb-2 group-hover:text-amber-700 transition-colors">{{ $svc['hero_headline'] ?? $svc['title'] }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed line-clamp-2">{{ $svc['seo_description'] ?? '' }}</p>
                    <span class="inline-flex items-center gap-1 mt-3 text-xs font-semibold text-amber-600 group-hover:translate-x-1 transition-transform">
                        Saber mais <span>→</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Social proof strip --}}
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-6">
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-3xl p-8 md:p-10 text-center">
            <p class="text-lg md:text-xl font-serif-display text-gray-800 leading-relaxed">
                "Com o assistente, qualificamos leads de <strong class="text-amber-600">{{ mb_strtolower($industryName) }}</strong> em minutos. Chegam com fotos, medidas, e o email de follow-up pronto. É como ter um comercial a trabalhar 24 horas."
            </p>
            <p class="mt-4 text-sm text-gray-500">— Empresa de {{ mb_strtolower($industryName) }}, Portugal</p>
        </div>
    </div>
</section>

{{-- FAQ --}}
@if (!empty($page['faq']))
<section class="py-24 px-6 bg-gray-50/70">
    <div class="max-w-3xl mx-auto">
        <div class="text-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-200/50 text-gray-500 text-xs font-bold tracking-wider uppercase rounded-full mb-6">FAQ</span>
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.faq.headline') }}
            </h2>
        </div>
        <div class="mt-14 space-y-3" x-data="{ open: null }">
            @foreach ($page['faq'] as $i => $faqItem)
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full px-6 py-5 flex items-center justify-between text-left gap-4">
                        <span class="font-semibold text-gray-900 text-sm sm:text-base">{{ $faqItem['q'] }}</span>
                        <span class="shrink-0 w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center transition-all duration-200" :class="open === {{ $i }} ? 'rotate-180 bg-amber-100 text-amber-600' : ''">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse>
                        <div class="px-6 pb-5 text-gray-500 leading-relaxed text-sm border-t border-gray-50 pt-4">
                            {{ $faqItem['a'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- CTA with form --}}
<section id="demo-form" class="py-24 px-6 bg-white">
    <div class="max-w-2xl mx-auto">
        <div class="text-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-600 text-xs font-bold tracking-wider uppercase rounded-full mb-6">Demo</span>
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
                {{ __('landing.demo_form.headline') }}
            </h2>
            <p class="mt-4 text-gray-500">{{ __('landing.demo_form.subtitle') }}</p>
        </div>

        @include('landing.partials._demo_form')
    </div>
</section>

{{-- Back link --}}
<div class="py-8 px-6 text-center bg-gray-50/70">
    <a href="{{ url('/') }}" class="text-sm text-gray-500 hover:text-amber-600 transition-colors">
        ← Voltar à página principal
    </a>
</div>

@endsection
