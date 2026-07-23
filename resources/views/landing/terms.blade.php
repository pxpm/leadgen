@extends('landing.layout')

@php $p = __('landing.terms_page'); @endphp

@section('title', $p['seo_title'])
@section('description', $p['seo_description'])
@section('canonical', terms_url())

@section('content')

<section class="relative pt-36 pb-24 px-6 bg-white overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-30"></div>

    <div class="relative max-w-3xl mx-auto">
        {{-- Header --}}
        <div class="mb-16">
            <p class="text-amber-600 text-sm font-semibold tracking-wide uppercase mb-3">Legal</p>
            <h1 class="text-4xl sm:text-5xl font-serif-display font-bold tracking-tight text-gray-900 mb-4">
                {{ $p['page_title'] }}
            </h1>
            <p class="text-gray-400 text-sm">{{ $p['last_updated'] }}</p>
        </div>

        {{-- Intro --}}
        <p class="text-lg text-gray-600 leading-relaxed mb-16">{{ $p['intro'] }}</p>

        {{-- Sections --}}
        <div class="space-y-20 pt-8">
            @foreach ($p['sections'] as $section)
                <div class="group">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-baseline gap-3">
                        <span class="text-amber-500 font-serif-display text-2xl shrink-0">
                            {{ explode('.', $section['title'])[0] }}.
                        </span>
                        <span>{{ preg_replace('/^\d+\.\s/', '', $section['title']) }}</span>
                    </h2>

                    @if (! empty($section['body']))
                        <p class="text-gray-600 leading-relaxed ml-10">{{ $section['body'] }}</p>
                    @endif

                    @if (! empty($section['items']))
                        <ul class="mt-3 ml-10 space-y-2">
                            @foreach ($section['items'] as $item)
                                <li class="flex items-start gap-2.5 text-gray-600 leading-relaxed">
                                    <span class="text-amber-400 mt-1.5 shrink-0">—</span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
