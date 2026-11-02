@extends('layouts.public', ['title' => __('public.errors.429.title')])

@section('content')
    <section class="samm-section">
        <h1 class="samm-title">{{ __('public.errors.429.title') }}</h1>
        <p class="samm-body-lg">{{ __('public.errors.429.lead') }}</p>
        <a class="btn btn-outline-secondary" href="{{ route('public.home') }}">{{ __('public.errors.back_home') }}</a>
    </section>
@endsection
