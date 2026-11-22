@extends('layouts.mobile-shell')

@section('title', ($tab === 'for-you' ? __('Your feed') : __('Discover hotels, stores, schools & more')) . ' · i-page')
@section('meta_description', __('Follow the places you love — hotels, stores, schools and more — and get their news, offers and events in one feed. Scan a QR code to join any channel.'))

@section('app-bar')
    <div class="app-bar-title">
        <a href="{{ route('guest.home') }}" aria-label="{{ __('i-page home') }}"><x-brand-logo animate="idle" :height="30" /></a>
    </div>
    <div class="app-bar-actions">
        <a href="{{ route('guest.search-organizations') }}" class="app-bar-icon-btn" aria-label="{{ __('Search') }}">
            <i class="bi bi-search"></i>
        </a>
    </div>
@endsection

@section('extra-styles')
    .home { padding-bottom: 96px; }
    @include('partials.feed._styles')
@endsection

@section('content')
@php
    $palette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777', '#0891b2'];
    $colorFor = fn ($seed) => $palette[crc32((string) $seed) % count($palette)];
    $hour = (int) now()->format('G');
    $greeting = $hour >= 5 && $hour < 12 ? __('Good morning') : ($hour >= 12 && $hour < 17 ? __('Good afternoon') : __('Good evening'));
    $user = auth()->user();
@endphp
<div class="home">
    {{-- Hero --}}
    <section class="hero reveal">
        <div class="hero-text">
            @if ($tab === 'for-you')
                <div class="hero-kicker"><span class="js-greeting" data-morning="{{ __('Good morning') }}" data-afternoon="{{ __('Good afternoon') }}" data-evening="{{ __('Good evening') }}">{{ $greeting }}</span>{{ $user ? ', ' . $user->first_name : '' }} 👋</div>
                <h1>{{ __('Your feed is ready') }}</h1>
                <p>{{ trans_choice('Fresh news from the :count channel you joined.|Fresh news from the :count channels you joined.', $joinedCount, ['count' => $joinedCount]) }}</p>
                <a href="{{ route('guest.home', ['tab' => 'discover']) }}" class="hero-cta"><i class="bi bi-compass"></i> {{ __('Discover more') }}</a>
            @else
                <div class="hero-kicker">@if ($user)<span class="js-greeting" data-morning="{{ __('Good morning') }}" data-afternoon="{{ __('Good afternoon') }}" data-evening="{{ __('Good evening') }}">{{ $greeting }}</span>, {{ $user->first_name }} 👋@else{{ __('Welcome to i-Page') }}@endif</div>
                <h1>{{ __('Discover what\'s happening') }}</h1>
                <p>{{ __('Hotels, stores, schools and more share their news here. Follow the channels you like to build your own feed.') }}</p>
                @guest
                    <a href="{{ route('register') }}" class="hero-cta"><i class="bi bi-person-plus"></i> {{ __('Create a free account') }}</a>
                @else
                    <a href="{{ route('guest.search-organizations') }}" class="hero-cta"><i class="bi bi-building"></i> {{ __('Find organizations') }}</a>
                @endguest
            @endif
        </div>
        <x-illustration :name="$tab === 'for-you' ? 'feed' : 'discover'" size="132" />
    </section>

    {{-- Tabs (only when there is a personal feed to switch to) --}}
    @if ($hasFeed)
        <nav class="seg" aria-label="{{ __('Feed') }}">
            <div class="seg-inner">
                <a href="{{ route('guest.home', ['tab' => 'for-you']) }}" class="{{ $tab === 'for-you' ? 'active' : '' }}" @if ($tab === 'for-you') aria-current="page" @endif><i class="bi bi-house-heart"></i> {{ __('For you') }}</a>
                <a href="{{ route('guest.home', ['tab' => 'discover']) }}" class="{{ $tab === 'discover' ? 'active' : '' }}" @if ($tab === 'discover') aria-current="page" @endif><i class="bi bi-shuffle"></i> {{ __('Discover') }}</a>
            </div>
        </nav>
    @endif

    @if ($tab === 'discover')
        {{-- Organizations --}}
        @if ($organizations->isNotEmpty())
            <section class="sec reveal" aria-labelledby="orgsTitle">
                <div class="sec-head">
                    <h2 id="orgsTitle">{{ __('Organizations') }}</h2>
                    <a href="{{ route('guest.search-organizations') }}">{{ __('See all') }}</a>
                </div>
                <div class="hscroll">
                    @foreach ($organizations as $org)
                        <a href="{{ route('guest.organization-detail', $org->id) }}" class="org" data-tip="{{ $org->name }}" data-tip-sub="{{ trans_choice(':count channel|:count channels', $org->channels_count) }}">
                            <div class="org-ring"><div class="org-av" style="--av: {{ $colorFor($org->name) }};">{{ mb_substr($org->name, 0, 1) }}</div></div>
                            <div class="org-name">{{ $org->name }}</div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Channels to start with --}}
        @if ($suggested->isNotEmpty())
            <section class="sec reveal" aria-labelledby="sugTitle">
                <div class="sec-head"><h2 id="sugTitle">{{ $hasFeed ? __('More channels for you') : __('Channels to start with') }}</h2></div>
                <div class="hscroll">
                    @foreach ($suggested as $channel)
                        <a href="{{ route('guest.channel-detail', [$channel->organization_id, $channel->slug]) }}" class="sug" data-tip="{{ $channel->name }}" data-tip-sub="{{ $channel->organization?->name }}">
                            <div class="sug-av" style="--av: {{ $colorFor($channel->name) }};">{{ mb_substr($channel->name, 0, 1) }}</div>
                            <div class="sug-name">{{ $channel->name }}</div>
                            <div class="sug-org">{{ $channel->organization?->name }}</div>
                            <div class="sug-foot"><span><i class="bi bi-people"></i> {{ $channel->users_count }}</span><span class="sug-go">{{ __('Open') }}</span></div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if (! $hasFeed)
            <div class="nudge reveal">
                <x-illustration name="channels" size="70" />
                <div><strong>{{ __('Make it yours') }}</strong>{{ __('Join a channel and this page turns into your personal feed.') }}</div>
            </div>
        @endif
    @endif

    {{-- Feed --}}
    <section class="sec" aria-labelledby="feedTitle">
        <div class="sec-head"><h2 id="feedTitle">{{ $tab === 'for-you' ? __('Latest from your channels') : __('Random picks') }}</h2></div>

        @if ($posts->isEmpty())
            <div class="feed-empty reveal">
                <x-illustration :name="$tab === 'for-you' ? 'empty' : 'search'" size="170" />
                <h3>{{ $tab === 'for-you' ? __('Your channels are quiet for now') : __('Nothing to discover yet') }}</h3>
                <p>{{ $tab === 'for-you' ? __('New posts from the channels you joined will land here. Meanwhile, see what others are sharing.') : __('Organizations haven\'t posted publicly yet. Check back soon!') }}</p>
                @if ($tab === 'for-you')<a href="{{ route('guest.home', ['tab' => 'discover']) }}" class="pill-btn"><i class="bi bi-compass"></i> {{ __('Discover posts') }}</a>@endif
            </div>
        @else
            @include('partials.feed._list', [
                'posts' => $posts,
                'endTitle' => $tab === 'for-you' ? __('You\'re all caught up!') : __('That\'s all for now'),
                'endText' => $tab === 'for-you' ? __('You\'ve seen every new post from your channels.') : __('Come back later for a fresh mix — or open a channel to see more.'),
                'endUrl' => route('guest.home', ['tab' => 'discover']),
                'endLabel' => $tab === 'for-you' ? __('Discover more') : __('Shuffle again'),
            ])
        @endif
    </section>
</div>
@endsection

@section('modals')
    <div class="signin-modal-overlay" id="signinModal" onclick="if(event.target===this) closeSignInModal()">
        <div class="signin-modal">
            <x-illustration name="notifications" size="120" />
            <h3>{{ __('Sign in required') }}</h3>
            <p>{{ __('Create a free account to like, comment, and follow organizations.') }}</p>
            <div class="signin-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeSignInModal()">{{ __('Cancel') }}</button>
                <a href="{{ route('login') }}" class="btn-signin">{{ __('Sign In') }}</a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@include('partials.feed._script')
<script>
    function requireSignIn() { document.getElementById('signinModal').classList.add('show'); }
    function closeSignInModal() { document.getElementById('signinModal').classList.remove('show'); }

</script>
@endsection
