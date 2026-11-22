@extends('layouts.mobile-shell')

@section('title', __('Saved posts') . ' · i-page')

@section('app-bar')
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('guest.home') }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}"><i class="bi bi-arrow-left rtl-flip"></i></a>
    <div class="app-bar-title">{{ __('Saved') }}</div>
@endsection

@section('extra-styles')
    .home { padding-bottom: 96px; }
    @include('partials.feed._styles')
@endsection

@section('content')
<div class="home">
    <section class="sec" aria-labelledby="savedTitle">
        <div class="sec-head"><h2 id="savedTitle"><i class="bi bi-bookmark-fill" style="color: var(--primary-600);"></i> {{ __('Saved posts') }}</h2></div>
        @if ($posts->isEmpty())
            <div class="feed-empty reveal">
                <x-illustration name="collection" size="170" />
                <h3>{{ __('Nothing saved yet') }}</h3>
                <p>{{ __('Tap the bookmark on any post to keep it here for later.') }}</p>
                <a href="{{ route('guest.home') }}" class="pill-btn"><i class="bi bi-compass"></i> {{ __('Browse posts') }}</a>
            </div>
        @else
            @include('partials.feed._list', [
                'posts' => $posts,
                'endTitle' => __('That\'s everything you saved'),
                'endText' => __('Tap the bookmark on a post to add more.'),
                'endUrl' => route('guest.home'),
                'endLabel' => __('Browse posts'),
                'endIcon' => 'compass',
            ])
        @endif
    </section>
</div>
@endsection

@section('scripts')
@include('partials.feed._script')
@endsection
