@extends('landing.layout')

@section('title', __('landing.site.title'))
@section('description', __('landing.hero.subheadline'))

@push('jsonld_graph')
,
{
    "@@type": "FAQPage",
    "@@id": "{{ url('/') }}/#faq",
    "mainEntity": [
        @foreach (__('landing.faq.items') as $faqItem)
        {
            "@@type": "Question",
            "name": "{{ $faqItem['question'] }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ $faqItem['answer'] }}"
            }
        }@if (!$loop->last),@endif
        @endforeach
    ]
}
@endpush

@section('content')

@include('landing.partials._hero')
@include('landing.partials._trust_strip')
@include('landing.partials._problem')
@include('landing.partials._solution')
@include('landing.partials._fast_emails')
@include('landing.partials._custom_forms')
@include('landing.partials._calendar')
@include('landing.partials._industries')
@include('landing.partials._time_saved')
@include('landing.partials._negative_reviews')
@include('landing.partials._faq')
@include('landing.partials._final_cta')

@endsection
