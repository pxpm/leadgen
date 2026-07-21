@extends('landing.layout')

@php
    $industryName = __('landing.industries_section.'.$industryKey.'.name');
    $pageTitle = $serviceData['title'] ?? $serviceData['hero_headline'] ?? '';
    $pageDescription = $serviceData['seo_description'] ?? '';
@endphp

@section('title', $pageTitle)
@section('description', $pageDescription)
@section('og_type', 'website')
@section('canonical', service_url($industryKey, $serviceData['slug']))

@push('jsonld_graph')
,
{
    "@@type": "BreadcrumbList",
    "@@id": "{{ service_url($industryKey, $serviceData['slug']) }}/#breadcrumb",
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
            "name": "{{ $industryName }}",
            "item": "{{ industry_url($industryKey) }}"
        },
        {
            "@@type": "ListItem",
            "position": 3,
            "name": "{{ $serviceData['hero_headline'] ?? $pageTitle }}"
        }
    ]
},
{
    "@@type": "Service",
    "@@id": "{{ service_url($industryKey, $serviceData['slug']) }}/#service",
    "name": "{{ $pageTitle }}",
    "description": "{{ $pageDescription }}",
    "provider": { "@@id": "{{ url('/') }}/#organization" },
    "category": "{{ $industryName }}",
    "serviceType": "{{ $serviceData['hero_headline'] ?? '' }}",
    "areaServed": {
        "@@type": "Country",
        "name": "Portugal"
    }
}
@endpush

@section('content')

{{-- Hero — service-specific --}}
<section class="relative pt-36 pb-24 px-6 overflow-hidden bg-gradient-to-br from-amber-50 via-white to-gray-50">
    <div class="absolute inset-0 bg-dots opacity-30"></div>
    <div class="relative max-w-4xl mx-auto text-center">
        <div class="flex items-center justify-center gap-2 mb-6">
            <a href="{{ industry_url($industryKey) }}" class="text-sm text-amber-600 hover:text-amber-700 font-medium transition-colors">
                ← {{ $industryName }}
            </a>
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-gray-900 leading-[1.1]">
            {{ $serviceData['hero_headline'] ?? $pageTitle }}
        </h1>
        <p class="mt-6 text-lg text-gray-500 leading-relaxed max-w-2xl mx-auto">
            {{ $serviceData['hero_subheadline'] ?? '' }}
        </p>
        <div class="mt-8">
            <button @click="showTrialModal = true" class="group inline-flex items-center px-6 py-3.5 text-base font-semibold text-white bg-amber-500 rounded-xl hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20 cursor-pointer">
                {{ __('landing.demo_form.submit') }}
                <span class="ml-2 transition-transform group-hover:translate-x-1">→</span>
            </button>
        </div>
    </div>
</section>

{{-- Highlights --}}
@if (!empty($serviceData['highlights']))
<section class="py-20 px-6 bg-white">
    <div class="max-w-4xl mx-auto">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($serviceData['highlights'] as $highlight)
                <div class="flex items-center gap-3 bg-amber-50 border border-amber-100 rounded-2xl p-4">
                    <span class="shrink-0 w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </span>
                    <span class="text-sm font-semibold text-amber-800">{{ $highlight }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- How it works for this industry --}}
<section class="py-24 px-6 bg-gray-50/70">
    <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
            Como funciona
        </h2>
        <p class="mt-4 text-gray-500 max-w-xl mx-auto">
            O mesmo assistente, adaptado a {{ mb_strtolower($industryName) }}. Qualifique leads de {{ mb_strtolower(str_replace('Qualificação de Leads para ', '', $pageTitle)) }} em três passos.
        </p>

        <div class="mt-14 grid md:grid-cols-3 gap-8">
            <div class="bg-white border border-gray-200 rounded-2xl p-6 text-center hover:shadow-md transition-shadow">
                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center mx-auto mb-4">1</div>
                <h3 class="font-bold text-gray-900 mb-2">O cliente descreve</h3>
                <p class="text-sm text-gray-500">O assistente faz as perguntas certas para {{ mb_strtolower($industryName) }}, adaptadas a este serviço específico.</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl p-6 text-center hover:shadow-md transition-shadow">
                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center mx-auto mb-4">2</div>
                <h3 class="font-bold text-gray-900 mb-2">Fotos e detalhes</h3>
                <p class="text-sm text-gray-500">O cliente envia fotos, medidas, e toda a informação técnica que precisa para orçamentar.</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl p-6 text-center hover:shadow-md transition-shadow">
                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center mx-auto mb-4">3</div>
                <h3 class="font-bold text-gray-900 mb-2">Lead no seu email</h3>
                <p class="text-sm text-gray-500">Recebe o lead qualificado com fotos, resumo e email de follow-up pronto. Reveja e envie.</p>
            </div>
        </div>
    </div>
</section>

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

{{-- Navigation — back to industry page and related services --}}
<section class="py-16 px-6 bg-gray-50/70">
    <div class="max-w-4xl mx-auto">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="{{ industry_url($industryKey) }}" class="text-sm text-gray-500 hover:text-amber-600 transition-colors">
                ← Ver todos os serviços de {{ mb_strtolower($industryName) }}
            </a>
            <a href="{{ url('/') }}" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">
                {{ __('landing.site.title') }} — Página principal
            </a>
        </div>

        @php
            $allServices = __('landing.industry_pages.'.$industryKey.'.services') ?? [];
            $otherServices = array_filter($allServices, fn ($s) => ($s['slug'] ?? '') !== $serviceData['slug']);
        @endphp
        @if (count($otherServices) > 0)
        <div class="mt-8 pt-8 border-t border-gray-200">
            <p class="text-sm font-semibold text-gray-500 mb-4">Outros serviços de {{ mb_strtolower($industryName) }}:</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($otherServices as $svc)
                    <a href="{{ service_url($industryKey, $svc['slug']) }}"
                       class="px-4 py-2 text-sm font-medium bg-white border border-gray-200 rounded-full hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 transition-all">
                        {{ $svc['hero_headline'] ?? $svc['title'] }}
                    </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>

@endsection
