@extends('landing.layout')

@section('title', __('landing.site.title'))
@section('description', __('landing.hero.subheadline'))

@section('content')

@include('landing.partials._hero')
@include('landing.partials._trust_strip')
@include('landing.partials._problem')
@include('landing.partials._solution')
@include('landing.partials._pipeline')
@include('landing.partials._calculator')
@include('landing.partials._before_after')
@include('landing.partials._lead_summary')
@include('landing.partials._why_not_forms')
@include('landing.partials._industries')
@include('landing.partials._interactive_demo')
@include('landing.partials._faq')
@include('landing.partials._final_cta')

@endsection
