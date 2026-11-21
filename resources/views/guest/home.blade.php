@extends('layouts.mobile-shell')

@section('title', __('i-Page') . ' - ' . ($tab === 'for-you' ? __('Your feed') : __('Discover')))

@section('app-bar')
    <button type="button" class="app-bar-icon-btn" onclick="toggleDrawer(true)" aria-label="{{ __('Menu') }}">
        <i class="bi bi-list"></i>
    </button>
    <div class="app-bar-title" style="display: flex; align-items: center; gap: var(--space-2);">
        <img src="{{ asset('images/brand/logo-mark.svg') }}" alt="" style="width: 24px; height: 24px;">
        i-Page
    </div>
    <div class="app-bar-actions">
        <button type="button" class="app-bar-icon-btn" onclick="openQrModal()" aria-label="{{ __('Scan QR code') }}">
            <i class="bi bi-qr-code-scan"></i>
        </button>
        <a href="{{ route('guest.search-organizations') }}" class="app-bar-icon-btn" aria-label="{{ __('Search') }}">
            <i class="bi bi-search"></i>
        </a>
        @guest
            <a href="{{ route('login') }}" class="app-bar-icon-btn" aria-label="{{ __('Sign in') }}" style="color: var(--primary-600);">
                <i class="bi bi-box-arrow-in-right"></i>
            </a>
        @endguest
    </div>
@endsection

@section('extra-styles')
    .home { padding-bottom: 96px; }

    /* Hero */
    .hero {
        position: relative; overflow: hidden; margin: var(--space-4); padding: 1.25rem 1.25rem 1.1rem;
        border-radius: 24px; color: #fff;
        background: radial-gradient(120% 140% at 0% 0%, #6d5dfc 0%, #4557f5 45%, #2b2f9e 100%);
        box-shadow: 0 18px 40px -18px rgba(69, 87, 245, .65);
        display: flex; align-items: center; gap: var(--space-3);
    }
    .hero::after { content: ''; position: absolute; inset: auto -40px -60px auto; width: 180px; height: 180px; border-radius: 50%; background: rgba(255,255,255,.08); }
    .hero-text { position: relative; z-index: 1; flex: 1; min-width: 0; }
    .hero-kicker { font-size: 12px; font-weight: 600; opacity: .85; letter-spacing: .02em; }
    .hero h1 { font-size: 1.35rem; line-height: 1.25; font-weight: 800; margin: 4px 0 6px; }
    .hero p { font-size: 13px; line-height: 1.55; color: rgba(255, 255, 255, .9); margin: 0 0 12px; }
    .hero h1, .hero-kicker { color: #fff; }
    .hero-cta { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 999px; background: #fff; color: #3730a3; font-weight: 700; font-size: 13px; text-decoration: none; }
    .hero-cta:hover { background: #eef2ff; }
    .hero .ill { flex: 0 0 auto; position: relative; z-index: 1; --ill-paper: #fff; --ill-ink: #1e293b; --ill-soft: rgba(255,255,255,.16); --ill-softer: rgba(0,0,0,.12); --ill-primary: #a5b4fc; --ill-line: #c7d2fe; }

    /* Segmented tabs */
    .seg { position: sticky; top: 0; z-index: 20; padding: 8px var(--space-4); background: color-mix(in srgb, var(--surface-bg-secondary, #f8fafc) 88%, transparent); backdrop-filter: blur(10px); }
    .seg-inner { display: grid; grid-auto-flow: column; grid-auto-columns: 1fr; gap: 4px; padding: 4px; border-radius: 14px; background: var(--surface-bg); border: 1px solid var(--surface-border); }
    .seg a { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 10px; border-radius: 10px; font-size: 13.5px; font-weight: 700; color: var(--text-secondary); text-decoration: none; transition: background .2s, color .2s; }
    .seg a.active { background: var(--primary-600); color: #fff; box-shadow: 0 6px 14px -6px rgba(69, 87, 245, .7); }
    .seg a:not(.active):hover { background: var(--surface-hover); color: var(--text-primary); }

    /* Section heads */
    .sec { margin: var(--space-4) 0 var(--space-2); }
    .sec-head { display: flex; align-items: center; justify-content: space-between; padding: 0 var(--space-4); margin-bottom: 10px; }
    .sec-head h2 { margin: 0; font-size: 15px; font-weight: 800; color: var(--text-primary); }
    .sec-head a { font-size: 12.5px; font-weight: 600; color: var(--primary-600); text-decoration: none; }
    .hscroll { display: flex; gap: 12px; overflow-x: auto; padding: 2px var(--space-4) 10px; scroll-snap-type: x proximity; scrollbar-width: none; }
    .hscroll::-webkit-scrollbar { display: none; }

    /* Organizations */
    .org { flex: 0 0 auto; width: 76px; text-align: center; text-decoration: none; scroll-snap-align: start; }
    .org-ring { width: 64px; height: 64px; margin: 0 auto 6px; padding: 3px; border-radius: 50%; background: conic-gradient(from 200deg, #6d5dfc, #ec4899, #f59e0b, #6d5dfc); transition: transform .2s; }
    .org:hover .org-ring { transform: scale(1.06); }
    .org-av { width: 100%; height: 100%; border-radius: 50%; border: 3px solid var(--surface-bg); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 20px; background: var(--av); }
    .org-name { font-size: 11.5px; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Suggested channels */
    .sug { flex: 0 0 auto; width: 168px; padding: 14px; border-radius: 18px; background: var(--surface-bg); border: 1px solid var(--surface-border); text-decoration: none; scroll-snap-align: start; transition: transform .2s, box-shadow .2s; }
    .sug:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -16px rgba(15, 23, 42, .35); }
    .sug-av { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; background: var(--av); margin-bottom: 10px; }
    .sug-name { font-size: 13.5px; font-weight: 700; color: var(--text-primary); line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.6em; }
    .sug-org { font-size: 11.5px; color: var(--text-tertiary); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sug-foot { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 11.5px; color: var(--text-tertiary); }
    .sug-go { padding: 4px 10px; border-radius: 999px; background: var(--primary-50, #eef2ff); color: var(--primary-700, #4338ca); font-weight: 700; }
    [data-theme="dark"] .sug-go { background: rgba(99, 102, 241, .2); color: #c7d2fe; }

    /* Nudge when the visitor has no channels yet */
    .nudge { margin: var(--space-2) var(--space-4) 0; display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 16px; background: var(--surface-bg); border: 1px dashed var(--primary-400, #818cf8); font-size: 13px; color: var(--text-secondary); }
    .nudge strong { color: var(--text-primary); display: block; font-size: 13.5px; }

    /* Feed + cards */
    .feed { display: flex; flex-direction: column; gap: 14px; padding: var(--space-2) var(--space-4) 0; }
    .fc { background: var(--surface-bg); border: 1px solid var(--surface-border); border-radius: 22px; overflow: hidden; box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 10px 30px -22px rgba(15, 23, 42, .35); }
    .fc-head { display: flex; align-items: center; gap: 12px; padding: 14px 16px 6px; }
    .fc-avatar { position: relative; flex: 0 0 auto; width: 44px; height: 44px; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 18px; text-decoration: none; background: linear-gradient(135deg, var(--av), color-mix(in srgb, var(--av) 60%, #000)); }
    .fc-verified { position: absolute; bottom: -4px; inset-inline-end: -4px; width: 18px; height: 18px; border-radius: 50%; background: var(--surface-bg); color: #3b82f6; font-size: 14px; display: flex; align-items: center; justify-content: center; }
    .fc-who { min-width: 0; flex: 1; }
    .fc-source { display: block; font-weight: 800; font-size: 14.5px; color: var(--text-primary); text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .fc-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 5px; font-size: 12px; color: var(--text-tertiary); margin-top: 1px; }
    .fc-org { color: var(--text-secondary); text-decoration: none; font-weight: 600; }
    .fc-pill { padding: 1px 7px; border-radius: 999px; background: var(--surface-hover); font-size: 11px; font-weight: 600; color: var(--text-secondary); }
    .fc-pill-lock { background: rgba(245, 158, 11, .14); color: #b45309; }
    .fc-body { padding: 0 4px; }
    .fc-actions { display: flex; align-items: center; gap: 6px; padding: 8px 12px 12px; }
    .fc-actions form { margin: 0; }
    .fc-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 999px; border: 0; background: var(--surface-hover); color: var(--text-secondary); font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s, color .15s, transform .15s; }
    .fc-btn:hover { background: var(--primary-50, #eef2ff); color: var(--primary-600); }
    .fc-btn:active { transform: scale(.94); }
    .fc-btn.is-on { background: rgba(236, 72, 153, .12); color: #db2777; }
    .fc-btn.is-on i { animation: fcPop .45s cubic-bezier(.34, 1.56, .64, 1); }
    .fc-static { cursor: default; }
    .fc-views { margin-inline-start: auto; display: inline-flex; align-items: center; gap: 5px; font-size: 12px; color: var(--text-tertiary); }
    .fc-comments { padding: 4px 14px 14px; border-top: 1px solid var(--surface-border); }
    .fc-comment { display: flex; gap: 8px; margin-top: 10px; }
    .fc-c-avatar { flex: 0 0 auto; width: 28px; height: 28px; border-radius: 50%; color: #fff; font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; background: var(--av); }
    .fc-c-bubble { padding: 7px 11px; border-radius: 14px; background: var(--surface-hover); font-size: 12.5px; min-width: 0; }
    .fc-c-bubble strong { display: block; font-size: 12px; color: var(--text-primary); }
    .fc-c-bubble p { margin: 1px 0 0; color: var(--text-secondary); line-height: 1.5; overflow-wrap: anywhere; }
    .fc-c-form { display: flex; gap: 8px; margin-top: 12px; }
    .fc-c-form input { flex: 1; min-width: 0; padding: 9px 14px; border-radius: 999px; border: 1px solid var(--surface-border); background: var(--surface-bg-secondary); color: var(--text-primary); font-size: 13px; }
    .fc-c-form input:focus { outline: 2px solid var(--primary-500); outline-offset: 1px; }
    .fc-c-form button { width: 38px; height: 38px; border-radius: 50%; border: 0; background: var(--primary-600); color: #fff; cursor: pointer; }
    @keyframes fcPop { 0% { transform: scale(.6); } 100% { transform: scale(1); } }

    /* Loading, end and empty states */
    .sk { border-radius: 22px; background: var(--surface-bg); border: 1px solid var(--surface-border); padding: 16px; }
    .sk-row { display: flex; gap: 12px; align-items: center; margin-bottom: 14px; }
    .sk-b { border-radius: 8px; background: linear-gradient(90deg, var(--surface-hover) 25%, color-mix(in srgb, var(--surface-hover) 40%, var(--surface-bg)) 50%, var(--surface-hover) 75%); background-size: 200% 100%; animation: skShine 1.3s linear infinite; }
    @keyframes skShine { to { background-position: -200% 0; } }
    .feed-end, .feed-empty { text-align: center; padding: var(--space-6) var(--space-4) var(--space-4); color: var(--text-secondary); }
    .feed-end h3, .feed-empty h3 { font-size: 16px; font-weight: 800; color: var(--text-primary); margin: 10px 0 4px; }
    .feed-end p, .feed-empty p { font-size: 13px; margin: 0 auto 14px; max-width: 340px; line-height: 1.6; }
    .pill-btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 999px; border: 0; background: var(--primary-600); color: #fff !important; font-weight: 700; font-size: 13px; text-decoration: none; cursor: pointer; }
    .pill-btn.ghost { background: var(--surface-hover); color: var(--text-primary) !important; }
    .feed-more { display: flex; justify-content: center; padding: var(--space-4); }
    .js .feed-more-link { display: none; }

    @media (prefers-reduced-motion: reduce) { .sk-b { animation: none; } .fc-btn.is-on i { animation: none; } }
    @media (min-width: 640px) { .hero h1 { font-size: 1.5rem; } }
@endsection

@section('content')
@php
    $palette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777', '#0891b2'];
    $colorFor = fn ($seed) => $palette[crc32((string) $seed) % count($palette)];
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? __('Good morning') : ($hour < 18 ? __('Good afternoon') : __('Good evening'));
    $user = auth()->user();
@endphp
<div class="home">
    {{-- Hero --}}
    <section class="hero reveal">
        <div class="hero-text">
            @if ($tab === 'for-you')
                <div class="hero-kicker">{{ $greeting }}{{ $user ? ', ' . $user->first_name : '' }} 👋</div>
                <h1>{{ __('Your feed is ready') }}</h1>
                <p>{{ trans_choice('Fresh news from the :count channel you joined.|Fresh news from the :count channels you joined.', $joinedCount, ['count' => $joinedCount]) }}</p>
                <a href="{{ route('guest.home', ['tab' => 'discover']) }}" class="hero-cta"><i class="bi bi-compass"></i> {{ __('Discover more') }}</a>
            @else
                <div class="hero-kicker">{{ $user ? $greeting . ', ' . $user->first_name . ' 👋' : __('Welcome to i-Page') }}</div>
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
                        <a href="{{ route('guest.organization-detail', $org->id) }}" class="org" title="{{ $org->name }}">
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
                        <a href="{{ route('guest.channel-detail', [$channel->organization_id, $channel->slug]) }}" class="sug">
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
            <div class="feed" id="feed" data-next="{{ $posts->nextPageUrl() }}">
                @include('guest.partials._feed-cards', ['posts' => $posts])
            </div>

            <div id="feedLoading" class="feed" hidden aria-hidden="true">
                @for ($i = 0; $i < 2; $i++)
                    <div class="sk">
                        <div class="sk-row"><div class="sk-b" style="width:44px;height:44px;border-radius:14px"></div><div style="flex:1"><div class="sk-b" style="height:12px;width:45%;margin-bottom:8px"></div><div class="sk-b" style="height:10px;width:28%"></div></div></div>
                        <div class="sk-b" style="height:12px;margin-bottom:8px"></div><div class="sk-b" style="height:12px;width:80%;margin-bottom:8px"></div><div class="sk-b" style="height:12px;width:60%"></div>
                    </div>
                @endfor
            </div>

            <div id="feedError" class="feed-end" hidden>
                <p>{{ __('Couldn\'t load more posts.') }}</p>
                <button type="button" class="pill-btn ghost" onclick="feedLoad()"><i class="bi bi-arrow-clockwise"></i> {{ __('Try again') }}</button>
            </div>

            <div id="feedEnd" class="feed-end" @if ($posts->hasMorePages()) hidden @endif>
                <x-illustration name="caught-up" size="140" />
                <h3>{{ $tab === 'for-you' ? __('You\'re all caught up!') : __('That\'s all for now') }}</h3>
                <p>{{ $tab === 'for-you' ? __('You\'ve seen every new post from your channels.') : __('Come back later for a fresh mix — or open a channel to see more.') }}</p>
                <a href="{{ route('guest.home', ['tab' => $tab === 'for-you' ? 'discover' : 'discover']) }}" class="pill-btn ghost"><i class="bi bi-shuffle"></i> {{ $tab === 'for-you' ? __('Discover more') : __('Shuffle again') }}</a>
            </div>

            <div id="feedSentinel" aria-hidden="true" style="height: 1px;"></div>
            @if ($posts->hasMorePages())
                <div class="feed-more feed-more-link"><a href="{{ $posts->nextPageUrl() }}" class="pill-btn ghost">{{ __('Load more') }}</a></div>
            @endif
        @endif
    </section>
</div>
@endsection

@section('modals')
    <div class="drawer-overlay" id="drawerOverlay" onclick="toggleDrawer(false)"></div>
    <div class="drawer" id="drawer">
        <div class="drawer-brand">
            <div class="drawer-brand-icon"><img src="{{ asset('images/brand/logo-mark.svg') }}" alt="i-Page" style="width: 100%; height: 100%; object-fit: contain;"></div>
            <div>
                <div style="font-weight: var(--font-weight-bold); font-size: var(--text-base);">i-Page</div>
                <div style="font-size: var(--text-xs); color: var(--text-tertiary);">{{ __('The Digital Front Door') }}</div>
            </div>
        </div>

        <a href="{{ route('guest.home') }}" class="drawer-link"><i class="bi bi-house"></i> {{ __('Home') }}</a>
        <a href="{{ route('guest.search-organizations') }}" class="drawer-link"><i class="bi bi-building"></i> {{ __('Explore Organizations') }}</a>

        <div style="flex: 1;"></div>

        @guest
            <a href="{{ route('login') }}" class="drawer-link" style="background-color: var(--primary-50); color: var(--primary-700);">
                <i class="bi bi-box-arrow-in-right" style="color: var(--primary-600);"></i> {{ __('Sign In') }}
            </a>
            <a href="{{ route('register') }}" class="drawer-link"><i class="bi bi-person-plus"></i> {{ __('Create Account') }}</a>
        @endguest
    </div>

    @include('partials.qr-scan-modal')

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
<script>
    document.documentElement.classList.add('js');

    function toggleDrawer(show) {
        document.getElementById('drawer').classList.toggle('show', show);
        document.getElementById('drawerOverlay').classList.toggle('show', show);
    }
    function requireSignIn() { document.getElementById('signinModal').classList.add('show'); }
    function closeSignInModal() { document.getElementById('signinModal').classList.remove('show'); }

    function toggleFeedComments(button) {
        const box = button.closest('.fc').querySelector('.fc-comments');
        if (!box) return;
        box.hidden = !box.hidden;
        button.setAttribute('aria-expanded', String(!box.hidden));
        if (!box.hidden) box.querySelector('input')?.focus();
    }

    function sharePost(url, button) {
        if (navigator.share) { navigator.share({ url }).catch(() => {}); return; }
        navigator.clipboard?.writeText(url);
        const icon = button.querySelector('i');
        icon.className = 'bi bi-check2';
        setTimeout(() => { icon.className = 'bi bi-send'; }, 1500);
    }

    /* Infinite scroll: fetch the next batch as HTML when the sentinel comes into view. */
    (function () {
        const feed = document.getElementById('feed');
        if (!feed) return;
        const loading = document.getElementById('feedLoading');
        const error = document.getElementById('feedError');
        const end = document.getElementById('feedEnd');
        const sentinel = document.getElementById('feedSentinel');
        let next = feed.dataset.next || null;
        let busy = false;

        window.feedLoad = async function () {
            if (!next || busy) return;
            busy = true;
            error.hidden = true;
            loading.hidden = false;
            try {
                const url = new URL(next, window.location.href);
                url.searchParams.set('partial', '1');
                const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                if (!res.ok) throw new Error(res.status);
                const data = await res.json();
                const holder = document.createElement('div');
                holder.innerHTML = data.html;
                const added = Array.from(holder.children);
                added.forEach(el => feed.appendChild(el));
                window.ipReveal && window.ipReveal(feed);
                next = data.next;
                if (!next) { end.hidden = false; observer && observer.disconnect(); }
            } catch (e) {
                error.hidden = false;
            } finally {
                loading.hidden = true;
                busy = false;
            }
        };

        const observer = 'IntersectionObserver' in window
            ? new IntersectionObserver(entries => { if (entries[0].isIntersecting) feedLoad(); }, { rootMargin: '700px 0px' })
            : null;
        if (observer && next) observer.observe(sentinel);
        if (!observer) document.querySelectorAll('.feed-more-link').forEach(el => el.style.display = 'flex');
    })();
</script>
@endsection
