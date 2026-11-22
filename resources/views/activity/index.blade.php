@extends('layouts.mobile-shell')

@section('title', __('My activities') . ' · i-page')

@section('app-bar')
    <a href="{{ route('guest.home') }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}"><i class="bi bi-arrow-left rtl-flip"></i></a>
    <div class="app-bar-title">{{ __('My activities') }}</div>
@endsection

@section('extra-styles')
    .act-empty { min-height: 60vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 24px 20px 110px; color: var(--text-secondary); }
    .act-empty h1 { margin: 12px 0 6px; font-size: 1.3rem; font-weight: 800; color: var(--text-primary); }
    .act-empty p { margin: 0; max-width: 340px; font-size: 14px; line-height: 1.6; }
    .act-badge { display: inline-flex; align-items: center; gap: 6px; margin-top: 16px; padding: 6px 14px; border-radius: 999px; background: var(--surface-hover); font-size: 12.5px; font-weight: 700; color: var(--text-secondary); }
@endsection

@section('content')
    {{-- Placeholder: the activity logic will be specified later (docs/27 "My activities"). --}}
    <section class="act-empty reveal">
        <x-illustration name="feed" size="190" />
        <h1>{{ __('My activities') }}</h1>
        <p>{{ __('Your likes, comments, votes and visits will show up here.') }}</p>
        <span class="act-badge"><i class="bi bi-hourglass-split"></i> {{ __('Coming soon') }}</span>
    </section>
@endsection
