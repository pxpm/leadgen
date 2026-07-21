@extends('landing.layout')

@php
use App\DTO\PricingPlanData;
use App\Models\Plan;

$p = __('landing.pricing_page');
$plans = PricingPlanData::collect(Plan::public()->orderBy('sort_order')->get());
$hasPlans = PricingPlanData::hasPlans($plans);
@endphp

@section('title', $p['seo_title'])
@section('description', $p['seo_description'])
@section('og_type', 'website')
@section('canonical', pricing_url())

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

{{-- Plan cards --}}
<section x-data="{ billing: 'yearly' }" class="relative pt-16 pb-28 px-6 bg-gray-50/70 overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-40"></div>

    {{-- Billing toggle --}}
    @if($hasPlans)
    <div class="relative max-w-xs mx-auto flex items-center justify-center gap-3">
        <button
            @click="billing = 'monthly'"
            :class="billing === 'monthly' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-400 hover:text-gray-600'"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors">
            {{ $p['billed_monthly'] }}
        </button>
        <button
            @click="billing = 'yearly'"
            :class="billing === 'yearly' ? 'bg-amber-400 text-white shadow-sm' : 'text-gray-400 hover:text-gray-600'"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors">
            {{ $p['billed_yearly'] }}
        </button>
    </div>
    @endif

    {{-- Spacer --}}
    <div class="h-8"></div>

    @if($hasPlans)
    <div class="relative max-w-6xl mx-auto grid md:grid-cols-3 gap-8 items-start">
        @foreach ($plans as $plan)
            <div class="card-lift relative bg-white border-2 {{ $plan->isPopular ? 'border-amber-300 shadow-xl shadow-amber-100/50' : 'border-gray-200' }} rounded-3xl overflow-hidden {{ $plan->isPopular ? 'md:-mt-6' : '' }}">
                @if ($plan->isPopular)
                    <div class="absolute top-3 right-3 px-3 py-1 bg-amber-400 text-white text-xs font-bold rounded-full tracking-wide shadow-sm">
                        {{ $p['most_popular'] }}
                    </div>
                @endif

                <div class="h-1.5 {{ $plan->isPopular ? 'bg-amber-500' : 'bg-gray-300' }}"></div>
                <div class="p-8 sm:p-10 text-center">
                    <h2 class="text-lg font-bold tracking-widest uppercase {{ $plan->isPopular ? 'text-amber-600' : 'text-gray-500' }} mb-2">
                        {{ $plan->name }}
                    </h2>

                    {{-- Yearly price --}}
                    <div x-show="billing === 'yearly'" class="mt-4">
                        <div class="flex items-baseline justify-center gap-0.5">
                            @if ($plan->currencyBefore)<span class="text-xl text-gray-400 font-medium self-start mt-1.5">€</span>@endif
                            <span class="text-4xl sm:text-5xl font-extrabold text-gray-900">{{ $plan->yearlyPrice }}</span>
                            @if (! $plan->currencyBefore)<span class="text-xl text-gray-400 font-medium self-end mb-1">€</span>@endif
                            <span class="text-lg text-gray-400 font-medium">{{ $p['per_month'] }}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-400 line-through">
                            @if ($plan->currencyBefore)€@endif{{ $plan->monthlyPriceRaw }}@if (! $plan->currencyBefore)€@endif{{ $p['per_month'] }}
                        </p>
                        <p class="mt-0.5 text-xs text-green-600 font-semibold">
                            {{ number_format($plan->yearlyTotal, 0) }}€/ano
                            <span class="ml-1.5 px-1.5 py-0.5 bg-green-50 text-green-700 rounded-full">{{ str_replace('{percent}', (string) $plan->savingsPercent, $p['save_label']) }}</span>
                        </p>
                    </div>

                    {{-- Monthly price --}}
                    <div x-show="billing === 'monthly'" class="mt-4">
                        <div class="flex items-baseline justify-center gap-0.5">
                            @if ($plan->currencyBefore)<span class="text-xl text-gray-400 font-medium self-start mt-1.5">€</span>@endif
                            <span class="text-4xl sm:text-5xl font-extrabold text-gray-900">{{ $plan->monthlyPrice }}</span>
                            @if (! $plan->currencyBefore)<span class="text-xl text-gray-400 font-medium self-end mb-1">€</span>@endif
                            <span class="text-lg text-gray-400 font-medium">{{ $p['per_month'] }}</span>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-400">
                            {{ number_format($plan->monthlyTotal, 0) }}€/ano
                        </p>
                    </div>

                    <p class="mt-3 text-gray-500 text-sm leading-relaxed">{{ $plan->description }}</p>

                    <button @click="showTrialModal = true" class="mt-8 inline-flex items-center justify-center w-full px-6 py-3.5 text-sm font-bold text-white {{ $plan->isPopular ? 'bg-amber-500 hover:bg-amber-600 shadow-md shadow-amber-200/50' : 'bg-gray-800 hover:bg-gray-900' }} rounded-xl transition-colors cursor-pointer">
                        {{ $p['cta_button'] }}
                    </button>
                </div>

                {{-- Limits --}}
                <div class="border-t border-gray-100 px-8 sm:px-10 py-8">
                    <h3 class="text-sm font-bold tracking-wider uppercase text-gray-400 mb-6">{{ $p['limits_title'] }}</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <div class="shrink-0 w-9 h-9 rounded-lg {{ $plan->isPopular ? 'bg-amber-50' : 'bg-gray-50' }} flex items-center justify-center">
                                <svg class="w-4.5 h-4.5 {{ $plan->isPopular ? 'text-amber-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ number_format($plan->smsLimit, 0, ',', '.') }} {{ $p['limit_sms'] }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $p['limit_sms_desc'] }}</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="shrink-0 w-9 h-9 rounded-lg {{ $plan->isPopular ? 'bg-amber-50' : 'bg-gray-50' }} flex items-center justify-center">
                                <svg class="w-4.5 h-4.5 {{ $plan->isPopular ? 'text-amber-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ number_format($plan->emailLimit, 0, ',', '.') }} {{ $p['limit_email'] }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $p['limit_email_desc'] }}</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="shrink-0 w-9 h-9 rounded-lg {{ $plan->isPopular ? 'bg-amber-50' : 'bg-gray-50' }} flex items-center justify-center">
                                <svg class="w-4.5 h-4.5 {{ $plan->isPopular ? 'text-amber-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ number_format($plan->ingestionLimit, 0, ',', '.') }} {{ $p['limit_ingestion'] }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $p['limit_ingestion_desc'] }}</p>
                            </div>
                        </li>
                    </ul>

                    {{-- Recovery call badge --}}
                    @if ($plan->hasRecoveryCall)
                    <div class="mt-6 pt-5 border-t border-gray-100">
                        <div class="flex items-center justify-center gap-2.5">
                            <svg class="shrink-0 w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="text-xs text-green-600 font-semibold">{{ $p['feature_recovery_call'] }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @else
    <div class="relative max-w-2xl mx-auto text-center py-12">
        <p class="text-gray-400 text-lg">{{ $p['no_plans'] }}</p>
    </div>
    @endif
</section>

{{-- FAQ --}}
<section class="py-24 sm:py-28 px-6 bg-white">
    <div class="max-w-2xl mx-auto">
        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 text-center mb-12">
            {{ __('landing.faq.headline') }}
        </h2>
        <div class="space-y-4">
            @foreach ($p['faq'] as $faq)
                <div x-data="{ open: false }" class="border border-gray-200 rounded-xl overflow-hidden">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-5 text-left text-sm font-semibold text-gray-800 hover:bg-gray-50 transition-colors">
                        {{ $faq['question'] }}
                        <svg :class="open ? 'rotate-180' : ''" class="shrink-0 w-4 h-4 text-gray-400 transition-transform ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-collapse class="px-6 pb-5 text-sm text-gray-500 leading-relaxed">
                        {{ $faq['answer'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Final CTA --}}
<section class="py-24 sm:py-28 px-6 bg-gray-900">
    <div class="max-w-2xl mx-auto text-center">
        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white leading-tight">
            {{ $p['cta_headline'] }}
        </h2>
        <p class="mt-4 text-gray-400 text-lg">
            {{ $p['cta_subtitle'] }}
        </p>
        <button @click="showTrialModal = true" class="mt-8 inline-flex items-center px-8 py-3.5 text-sm font-bold text-gray-900 bg-amber-400 rounded-xl hover:bg-amber-300 transition-colors shadow-lg shadow-amber-500/20 cursor-pointer">
            {{ $p['cta_button'] }}
        </button>
    </div>
</section>

@endsection
