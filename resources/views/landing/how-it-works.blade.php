@extends('landing.layout')

@php $p = __('landing.how_it_works_page'); @endphp

@section('title', $p['seo_title'])
@section('description', $p['seo_description'])
@section('og_type', 'website')
@section('canonical', how_it_works_url())

@push('jsonld_graph')
,
{
    "@@type": "HowTo",
    "@@id": "{{ how_it_works_url() }}/#howto",
    "name": "{{ $p['page_title'] }}",
    "description": "{{ $p['seo_description'] }}",
    "step": [
        {"@@type": "HowToStep", "position": 1, "name": "{{ $p['step_1_title'] }}", "text": "{{ $p['step_1_desc'] }}"},
        {"@@type": "HowToStep", "position": 2, "name": "{{ $p['step_2_title'] }}", "text": "{{ $p['step_2_desc'] }}"},
        {"@@type": "HowToStep", "position": 3, "name": "{{ $p['step_3_title'] }}", "text": "{{ $p['step_3_desc'] }}"},
        {"@@type": "HowToStep", "position": 4, "name": "{{ $p['step_4_title'] }}", "text": "{{ $p['step_4_desc'] }}"},
        {"@@type": "HowToStep", "position": 5, "name": "{{ $p['step_5_title'] }}", "text": "{{ $p['step_5_desc'] }}"}
    ]
}
@endpush

@section('content')

{{-- Hero --}}
<section class="relative pt-36 pb-20 px-6 bg-white overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-30"></div>
    <div class="relative max-w-2xl mx-auto text-center">
        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 leading-tight">
            {{ $p['subtitle'] }}
        </h1>
    </div>
</section>

{{-- All 5 steps — consistent card language --}}
<section class="relative pt-16 md:pt-24 pb-28 px-6 bg-gray-50/70 overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-40"></div>

    <div class="relative max-w-7xl mx-auto">
        @php
        $steps = [
            ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>', 'time' => 'step_1_time', 'title' => 'step_1_title', 'desc' => 'step_1_desc'],
            ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>', 'time' => 'step_2_time', 'title' => 'step_2_title', 'desc' => 'step_2_desc'],
            ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>', 'time' => 'step_3_time', 'title' => 'step_3_title', 'desc' => 'step_3_desc'],
            ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>', 'time' => 'step_4_time', 'title' => 'step_4_title', 'desc' => 'step_4_desc'],
            ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>', 'time' => 'step_5_time', 'title' => 'step_5_title', 'desc' => 'step_5_desc'],
        ];
        @endphp

        {{-- Row 1: Steps 1-3 --}}
        <div class="grid md:grid-cols-3 gap-6 items-start mb-6">
            @foreach (array_slice($steps, 0, 3) as $i => $s)
                <div class="card-lift relative bg-white border {{ $i === 1 ? 'border-2 border-amber-300 shadow-xl shadow-amber-100/50' : 'border-gray-200' }} rounded-2xl overflow-hidden">
                    <div class="h-1.5 {{ $i === 1 ? 'bg-amber-500' : 'bg-amber-300' }}"></div>
                    <div class="p-6 sm:p-8">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="shrink-0 w-11 h-11 {{ $i === 1 ? 'bg-amber-100' : 'bg-amber-50' }} rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $s['icon'] !!}</svg>
                            </div>
                            <h3 class="text-lg font-serif-display text-gray-900">{{ $p[$s['title']] }}</h3>
                        </div>
                        <p class="text-gray-500 leading-relaxed text-sm">{{ $p[$s['desc']] }}</p>
                        <div class="mt-5">
                            <span class="inline-block px-2.5 py-1 {{ $i === 1 ? 'bg-amber-500 text-white' : 'bg-gray-900 text-white' }} text-xs font-bold rounded-md">{{ $p[$s['time']] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Row 2: Steps 4-5 --}}
        <div class="grid md:grid-cols-2 gap-6 items-start max-w-4xl mx-auto">
            @foreach (array_slice($steps, 3, 2) as $i => $s)
                <div class="card-lift relative bg-white border border-gray-200 rounded-2xl overflow-hidden">
                    <div class="h-1.5 bg-amber-400"></div>
                    <div class="p-6 sm:p-8">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="shrink-0 w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $s['icon'] !!}</svg>
                            </div>
                            <h3 class="text-lg font-serif-display text-gray-900">{{ $p[$s['title']] }}</h3>
                        </div>
                        <p class="text-gray-500 leading-relaxed text-sm">{{ $p[$s['desc']] }}</p>
                        <div class="mt-5">
                            <span class="inline-block px-2.5 py-1 bg-gray-900 text-white text-xs font-bold rounded-md">{{ $p[$s['time']] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Result --}}
<section class="py-24 px-6 bg-white">
    <div class="max-w-lg mx-auto text-center">
        <div class="flex items-end justify-center gap-8">
            <div>
                <p class="text-sm text-gray-400 mb-2">Sem o assistente</p>
                <p class="text-4xl sm:text-5xl font-bold text-gray-200">~17<span class="text-xl text-gray-300"> min</span></p>
            </div>
            <div class="pb-1.5">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </div>
            <div>
                <p class="text-sm text-gray-400 mb-2">Com o assistente</p>
                <p class="text-4xl sm:text-5xl font-bold text-amber-500">&lt;1<span class="text-xl text-amber-400"> min</span></p>
            </div>
        </div>
        <p class="mt-8 text-gray-500 text-sm">20 minutos poupados por lead. Em m&eacute;dia.</p>
    </div>
</section>

{{-- CTA --}}
<section class="pb-28 px-6">
    <div class="max-w-xl mx-auto text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $p['cta_title'] }}</h2>
        <p class="mt-4 text-gray-500">{{ $p['cta_subtitle'] }}</p>
        <a href="{{ url('/') }}#demo" class="group inline-flex items-center mt-8 px-6 py-3.5 text-base font-semibold text-white bg-amber-500 rounded-xl hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20">
            {{ __('landing.demo_form.submit') }}
            <span class="ml-2 transition-transform group-hover:translate-x-1">→</span>
        </a>
    </div>
</section>

<div class="py-8 px-6 text-center bg-gray-50/70">
    <a href="{{ url('/') }}" class="text-sm text-gray-400 hover:text-amber-600 transition-colors">
        ← {{ __('landing.site.title') }}
    </a>
</div>

@endsection
