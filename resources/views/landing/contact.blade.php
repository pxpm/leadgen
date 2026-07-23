@extends('landing.layout')

@php $p = __('landing.contact_page'); @endphp

@section('title', $p['seo_title'])
@section('description', $p['seo_description'])
@section('canonical', contact_url())

@section('content')

<section class="relative pt-36 pb-24 px-6 bg-white overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-30"></div>

    <div class="relative max-w-xl mx-auto text-center">
        <p class="text-amber-600 text-sm font-semibold tracking-wide uppercase mb-3">Contacto</p>
        <h1 class="text-4xl sm:text-5xl font-serif-display font-bold tracking-tight text-gray-900 mb-4">
            {{ $p['page_title'] }}
        </h1>
        <p class="text-lg text-gray-500 mb-16">{{ $p['subtitle'] }}</p>

        <div class="bg-amber-50/50 border border-amber-100 rounded-2xl p-10 space-y-6">
            <div>
                <p class="text-sm font-medium text-gray-400 uppercase tracking-wide mb-2">{{ $p['email_label'] }}</p>
                <a href="mailto:{{ $p['email'] }}"
                   class="text-2xl font-bold text-gray-900 hover:text-amber-600 transition-colors">
                    {{ $p['email'] }}
                </a>
            </div>

            <p class="text-sm text-gray-400">{{ $p['response_time'] }}</p>
        </div>
    </div>
</section>

@endsection
