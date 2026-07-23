@extends('landing.layout')

@php
    $slug = $slug ?? 'guia-orcamento-telhados';
    $articles = __('landing.blog_index.articles');
    $article = $articles[$slug] ?? reset($articles);
@endphp

@section('title', $article['seo_title'] ?? $article['title'])
@section('description', $article['seo_description'] ?? $article['excerpt'])
@section('canonical', url('/blog/'.$slug))

@push('jsonld_graph')
,
{
    "@@type": "Article",
    "@@id": "{{ url('/blog/'.$slug) }}/#article",
    "headline": "{{ $article['title'] }}",
    "description": "{{ $article['excerpt'] }}",
    "datePublished": "{{ $article['date'] }}",
    "author": {
        "@@type": "Organization",
        "name": "Lead Intake Assistant"
    }
}
@endpush

@section('content')

<article class="relative pt-36 pb-20 px-6 bg-white overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-30"></div>
    <div class="relative max-w-2xl mx-auto">
        <p class="text-amber-600 text-sm font-semibold uppercase tracking-wide mb-3">{{ $article['category'] }}</p>
        <h1 class="text-3xl sm:text-4xl font-serif-display font-bold tracking-tight text-gray-900 leading-tight mb-4">
            {{ $article['title'] }}
        </h1>
        <p class="text-sm text-gray-400 mb-10">{{ $article['date'] }} · {{ $article['read_time'] }}</p>

        <div class="prose prose-gray max-w-none text-gray-600 leading-relaxed space-y-6">
            {!! $article['body'] !!}
        </div>

        <div class="mt-16 p-6 bg-amber-50 border border-amber-200 rounded-2xl text-center">
            <p class="font-semibold text-amber-800 mb-2">Não perca mais contactos.</p>
            <p class="text-sm text-amber-600 mb-4">Experimente grátis durante 14 dias.</p>
            <button @click="showTrialModal = true" class="inline-flex items-center px-6 py-3 text-sm font-semibold text-white bg-amber-500 rounded-xl hover:bg-amber-600 transition-all shadow-sm cursor-pointer">
                {{ __('landing.demo_form.submit') }}
            </button>
        </div>
    </div>
</article>

@endsection
