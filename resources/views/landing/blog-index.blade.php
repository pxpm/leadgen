@extends('landing.layout')

@php $p = __('landing.blog_index'); @endphp

@section('title', $p['seo_title'])
@section('description', $p['seo_description'])
@section('canonical', url('/blog'))

@section('content')

<section class="relative pt-36 pb-20 px-6 bg-white overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-30"></div>
    <div class="relative max-w-4xl mx-auto">
        <h1 class="text-4xl sm:text-5xl font-serif-display font-bold tracking-tight text-gray-900 mb-4 text-center">
            {{ $p['page_title'] }}
        </h1>
        <p class="text-lg text-gray-500 text-center mb-16">{{ $p['subtitle'] }}</p>

        <div class="grid md:grid-cols-2 gap-6">
            @foreach ($p['articles'] as $slug => $article)
                <a href="{{ url('/blog/'.$slug) }}"
                   class="group bg-white border border-gray-200 rounded-2xl p-6 hover:border-amber-300 hover:shadow-lg transition-all">
                    <p class="text-xs text-amber-600 font-semibold uppercase tracking-wide mb-2">{{ $article['category'] }}</p>
                    <h2 class="text-lg font-bold text-gray-900 group-hover:text-amber-600 transition-colors mb-2">
                        {{ $article['title'] }}
                    </h2>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ $article['excerpt'] }}</p>
                    <p class="text-xs text-gray-400 mt-4">{{ $article['date'] }} · {{ $article['read_time'] }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>

@endsection
