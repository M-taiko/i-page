<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/logo-mark.svg') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#2563eb">
    <link rel="apple-touch-icon" href="{{ asset('images/pwa/apple-touch-icon.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'i-Page')</title>
    @include('partials.seo-auto')

    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/illustrations.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body { height: 100%; background-color: var(--surface-bg-secondary); }

        body {
            font-family: var(--font-sans);
            color: var(--text-primary);
            display: flex;
            flex-direction: column;
        }

        /* Mobile-first: full width by default, then progressively widen and
           center as a "column" app on larger screens (tablet/desktop). */
        .shell {
            display: flex;
            flex-direction: column;
            height: 100vh; /* fallback for browsers without dvh support */
            height: 100dvh;
            width: 100%;
            margin: 0 auto;
            background-color: var(--surface-bg-secondary);
            position: relative;
        }

        @media (min-width: 640px) {
            .shell { max-width: 600px; box-shadow: 0 0 40px rgba(0, 0, 0, 0.06); }
        }

        @media (min-width: 1024px) {
            .shell { max-width: 720px; border-inline: 1px solid var(--surface-border); }
        }

        @media (min-width: 1440px) {
            .shell { max-width: 820px; }
        }

        /* Top app bar: a glassy sticky bar. Pages fill it through @section('app-bar'); the layout adds
           the theme toggle and, for members, the notification bell. Gains a shadow once content scrolls. */
        .app-bar {
            display: flex; align-items: center; gap: 6px;
            min-height: 60px;
            padding: 8px 12px; padding-top: max(8px, env(safe-area-inset-top));
            position: sticky; top: 0; z-index: 40;
            background: color-mix(in srgb, var(--surface-bg) 82%, transparent);
            -webkit-backdrop-filter: blur(18px) saturate(1.6);
            backdrop-filter: blur(18px) saturate(1.6);
            border-bottom: 1px solid color-mix(in srgb, var(--surface-border) 70%, transparent);
            transition: box-shadow .25s ease, border-color .25s ease;
        }
        .app-bar.is-scrolled { box-shadow: 0 10px 24px -18px rgba(15, 23, 42, .55); border-bottom-color: var(--surface-border); }
        .app-bar-icon-btn {
            position: relative; flex-shrink: 0;
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            border: 0; border-radius: 12px; background: none; cursor: pointer;
            color: var(--text-primary); font-size: 20px; text-decoration: none;
            transition: background-color .15s ease, color .15s ease, transform .15s ease;
        }
        .app-bar-icon-btn:hover { background-color: var(--surface-hover); }
        .app-bar-icon-btn:active { transform: scale(.92); }
        .app-bar-icon-btn:focus-visible { outline: 2px solid var(--primary-500); outline-offset: 2px; }
        .app-bar-title {
            flex: 1; min-width: 0;
            display: flex; align-items: center; gap: 8px;
            font-size: 17px; font-weight: 800; letter-spacing: -.01em; color: var(--text-primary);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .app-bar-title a { color: inherit; text-decoration: none; display: inline-flex; align-items: center; }
        .app-bar-actions { display: flex; align-items: center; gap: 2px; }

        /* Notification bell + panel */
        .nb { position: relative; }
        .nb-btn.has-unread i { animation: nbRing 4s ease-in-out infinite; transform-origin: 50% 10%; display: inline-block; }
        .nb-badge {
            position: absolute; top: 3px; inset-inline-end: 3px;
            min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #f43f5e, #e11d48); color: #fff;
            font-size: 10.5px; font-weight: 800; line-height: 1;
            border: 2px solid var(--surface-bg); box-shadow: 0 4px 10px -3px rgba(225, 29, 72, .6);
        }
        .nb-badge[hidden] { display: none; }
        .nb-badge.bump { animation: nbBump .5s cubic-bezier(.34, 1.56, .64, 1); }
        .np {
            position: absolute; top: calc(100% + 10px); inset-inline-end: -6px; z-index: 60;
            width: min(380px, calc(100vw - 24px));
            border-radius: 20px; overflow: hidden;
            background: var(--surface-bg); border: 1px solid var(--surface-border);
            box-shadow: 0 24px 60px -20px rgba(15, 23, 42, .45);
            transform-origin: top right; animation: npIn .2s cubic-bezier(.2, .8, .2, 1);
        }
        [dir="rtl"] .np { transform-origin: top left; }
        .np-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 14px 16px 10px; }
        .np-head strong { font-size: 15px; font-weight: 800; color: var(--text-primary); }
        .np-all { border: 0; background: none; color: var(--primary-600); font: inherit; font-size: 12.5px; font-weight: 700; cursor: pointer; padding: 4px 8px; border-radius: 8px; }
        .np-all:hover { background: var(--surface-hover); }
        .np-list { max-height: min(60vh, 440px); overflow-y: auto; padding: 0 6px 6px; }
        .np-item { position: relative; display: flex; gap: 12px; padding: 10px; border-radius: 14px; text-decoration: none; color: var(--text-secondary); transition: background .15s ease; }
        .np-item:hover { background: var(--surface-hover); }
        .np-item.is-unread { background: color-mix(in srgb, var(--primary-600) 7%, transparent); color: var(--text-primary); }
        .np-ic { flex: 0 0 auto; width: 38px; height: 38px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--c); background: color-mix(in srgb, var(--c) 14%, transparent); font-size: 17px; }
        .np-body { min-width: 0; display: flex; flex-direction: column; gap: 3px; padding-inline-end: 12px; }
        .np-msg { font-size: 13px; line-height: 1.45; font-weight: 600; overflow-wrap: anywhere; }
        .np-time { font-size: 11.5px; color: var(--text-tertiary); }
        .np-dot { position: absolute; top: 50%; inset-inline-end: 10px; width: 8px; height: 8px; margin-top: -4px; border-radius: 50%; background: var(--primary-600); }
        .np-empty { display: flex; flex-direction: column; align-items: center; gap: 4px; text-align: center; padding: 8px 20px 18px; color: var(--text-secondary); font-size: 12.5px; }
        .np-empty strong { color: var(--text-primary); font-size: 14px; }
        .np-skel { height: 58px; margin: 4px; border-radius: 14px; background: linear-gradient(90deg, var(--surface-hover) 25%, color-mix(in srgb, var(--surface-hover) 40%, var(--surface-bg)) 50%, var(--surface-hover) 75%); background-size: 200% 100%; animation: npShine 1.2s linear infinite; }
        .np-foot { display: block; padding: 12px; text-align: center; border-top: 1px solid var(--surface-border); font-size: 13px; font-weight: 700; color: var(--primary-600); text-decoration: none; }
        .np-foot:hover { background: var(--surface-hover); }
        @media (max-width: 520px) {
            .np { position: fixed; top: calc(62px + env(safe-area-inset-top)); left: 12px; right: 12px; width: auto; }
        }
        @keyframes nbRing { 0%, 86%, 100% { transform: rotate(0); } 88% { transform: rotate(14deg); } 91% { transform: rotate(-12deg); } 94% { transform: rotate(7deg); } 97% { transform: rotate(-3deg); } }
        @keyframes nbBump { 0% { transform: scale(.6); } 100% { transform: scale(1); } }
        @keyframes npIn { from { opacity: 0; transform: translateY(-6px) scale(.97); } to { opacity: 1; transform: none; } }
        @keyframes npShine { to { background-position: -200% 0; } }

        /* Page-transition loader */
        .page-loader {
            position: fixed; inset: 0; z-index: 999;
            display: none; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--surface-bg) 86%, transparent);
            -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px);
        }
        .page-loader.show { display: flex; animation: plIn .2s ease; }
        @keyframes plIn { from { opacity: 0; } to { opacity: 1; } }
        @media (prefers-reduced-motion: reduce) {
            .nb-btn.has-unread i, .nb-badge.bump, .np, .np-skel, .page-loader.show { animation: none; }
        }
        /* Shell content: scrollable area, floats fully behind the bottom nav */
        .shell-content {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: calc(100px + env(safe-area-inset-bottom));
        }

        /* Bottom navigation: a floating glass dock with five fixed destinations and the QR scanner
           in the middle. Hides while scrolling down, comes back when scrolling up. */
        .bottom-nav-wrap {
            position: absolute; left: 0; right: 0; bottom: 0; z-index: 30;
            padding: 0 12px max(10px, env(safe-area-inset-bottom));
            pointer-events: none;
            transition: transform .32s cubic-bezier(.2, .8, .2, 1), opacity .32s ease;
        }
        .bottom-nav-wrap.is-hidden { transform: translateY(calc(100% + 16px)); opacity: 0; }
        .bottom-nav {
            pointer-events: auto;
            max-width: 460px; margin: 0 auto;
            display: grid; grid-template-columns: repeat(5, 1fr); align-items: end;
            padding: 6px;
            border-radius: 24px;
            background: color-mix(in srgb, var(--surface-bg) 84%, transparent);
            -webkit-backdrop-filter: blur(18px) saturate(1.6);
            backdrop-filter: blur(18px) saturate(1.6);
            border: 1px solid color-mix(in srgb, var(--surface-border) 85%, transparent);
            box-shadow: 0 14px 34px -14px rgba(15, 23, 42, .45), 0 2px 8px rgba(15, 23, 42, .06);
        }
        .bn-item {
            position: relative;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px;
            min-height: 50px; padding: 5px 2px;
            border: 0; border-radius: 16px; background: none; cursor: pointer;
            color: var(--text-secondary); text-decoration: none;
            font: inherit; font-size: 11px; font-weight: 600; line-height: 1.1;
            -webkit-tap-highlight-color: transparent;
            transition: color .2s ease, background-color .2s ease;
        }
        .bn-item i { font-size: 21px; line-height: 1; transition: transform .25s cubic-bezier(.34, 1.56, .64, 1); }
        .bn-item:hover { color: var(--text-primary); }
        .bn-item:hover i { transform: translateY(-1px); }
        .bn-item.active { color: var(--primary-600); background: color-mix(in srgb, var(--primary-600) 11%, transparent); }
        .bn-item.active i { animation: bnPop .5s cubic-bezier(.34, 1.56, .64, 1); }
        .bn-item:focus-visible, .bn-fab:focus-visible { outline: 2px solid var(--primary-500); outline-offset: 2px; }
        .bn-label { max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .bn-badge {
            position: absolute; top: 3px; inset-inline-start: calc(50% + 3px);
            min-width: 18px; height: 18px; padding: 0 4px; border-radius: 999px;
            background: #ef4444; color: #fff; font-size: 10px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid var(--surface-bg);
        }
        .bn-me { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; font-size: 10px; font-weight: 800; color: #fff; background: linear-gradient(135deg, #6d5dfc, #ec4899); box-shadow: 0 0 0 2px var(--surface-bg); }
        .bn-item.active .bn-me { box-shadow: 0 0 0 2px var(--surface-bg), 0 0 0 4px var(--primary-600); }
        .bn-me img { width: 100%; height: 100%; object-fit: cover; }
        .bn-center { display: flex; flex-direction: column; align-items: center; gap: 3px; padding-bottom: 5px; }
        .bn-fab {
            position: relative;
            width: 56px; height: 56px; margin-top: -30px;
            border: 0; border-radius: 20px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 24px;
            background: linear-gradient(135deg, #7c6cff 0%, #4557f5 55%, #db2777 150%);
            box-shadow: 0 12px 24px -8px rgba(69, 87, 245, .7), inset 0 1px 0 rgba(255, 255, 255, .35);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .bn-fab::after { content: ''; position: absolute; inset: -5px; border-radius: 24px; border: 2px solid rgba(109, 93, 252, .45); animation: bnRing 2.8s ease-out infinite; pointer-events: none; }
        .bn-fab:hover { transform: translateY(-2px); box-shadow: 0 16px 28px -8px rgba(69, 87, 245, .75), inset 0 1px 0 rgba(255, 255, 255, .35); }
        .bn-fab:active { transform: scale(.94); }
        .bn-center .bn-label { font-size: 11px; font-weight: 600; color: var(--text-secondary); }
        @keyframes bnPop { 0% { transform: scale(.7); } 100% { transform: scale(1); } }
        @keyframes bnRing { 0% { opacity: .8; transform: scale(.92); } 100% { opacity: 0; transform: scale(1.22); } }
        @media (prefers-reduced-motion: reduce) {
            .bottom-nav-wrap, .bn-item i, .bn-fab { transition: none; }
            .bn-item.active i, .bn-fab::after { animation: none; }
        }

        /* Tooltip popup for any element with data-tip (title on the first line, data-tip-sub below).
           Lives on <body> with position: fixed, so scrolling strips never clip it. */
        .ip-tip {
            position: fixed; z-index: 2000; max-width: min(280px, calc(100vw - 24px));
            padding: 8px 12px; border-radius: 12px;
            background: #0f172a; color: #fff; box-shadow: 0 12px 28px -10px rgba(15, 23, 42, .55);
            font-size: 13px; font-weight: 700; line-height: 1.35; text-align: center; overflow-wrap: anywhere;
            pointer-events: none; opacity: 0; transform: translateY(4px) scale(.97);
            transition: opacity .15s ease, transform .15s ease;
        }
        .ip-tip.show { opacity: 1; transform: none; }
        .ip-tip small { display: block; margin-top: 2px; font-size: 11.5px; font-weight: 500; opacity: .75; }
        .ip-tip::after { content: ''; position: absolute; left: var(--arrow-x, 50%); top: 100%; width: 10px; height: 10px; background: inherit; transform: translate(-50%, -50%) rotate(45deg); border-radius: 2px; }
        .ip-tip.below::after { top: 0; }
        [data-theme="dark"] .ip-tip { background: #f8fafc; color: #0f172a; }
        @media (prefers-reduced-motion: reduce) { .ip-tip { transition: none; } }
        /* Theme toggle in the app bar: shows the moon in light mode and the sun in dark mode. */
        .theme-toggle .bi-sun { display: none; }
        [data-theme="dark"] .theme-toggle .bi-sun { display: inline-block; }
        [data-theme="dark"] .theme-toggle .bi-moon-stars { display: none; }
        [data-theme="dark"] {
            --surface-bg: #101012;
            --surface-bg-secondary: #0a0a0b;
            --surface-border: #232326;
            --surface-hover: #1a1a1d;
            --text-primary: #f5f5f5;
            --text-secondary: #b3b3b3;
            --text-tertiary: #7a7a7a;
        }

        @yield('extra-styles')
    </style>
</head>
<body>
    <div class="shell">
        <div class="app-bar" id="appBar">
            @yield('app-bar')

            <button type="button" class="app-bar-icon-btn theme-toggle" onclick="toggleShellTheme()" aria-label="{{ __('Switch light / dark mode') }}">
                <i class="bi bi-moon-stars"></i><i class="bi bi-sun"></i>
            </button>
            @auth
                @php $navUnread = auth()->user()->notifications()->whereNull('read_at')->count(); @endphp
                <div class="nb" id="notifBell" data-latest-url="{{ route('user.notifications.latest') }}" data-read-all-url="{{ route('user.notifications.read-all') }}">
                    <button type="button" class="app-bar-icon-btn nb-btn {{ $navUnread ? 'has-unread' : '' }}" id="notifBtn"
                            aria-haspopup="dialog" aria-expanded="false" aria-controls="notifPanel"
                            aria-label="{{ __('Notifications') }}">
                        <i class="bi bi-bell"></i>
                        <span class="nb-badge" id="notifBadge" @unless ($navUnread) hidden @endunless>{{ $navUnread > 99 ? '99+' : $navUnread }}</span>
                    </button>
                    <div class="np" id="notifPanel" role="dialog" aria-label="{{ __('Notifications') }}" hidden>
                        <div class="np-head">
                            <strong>{{ __('Notifications') }}</strong>
                            <button type="button" class="np-all" id="notifReadAll"><i class="bi bi-check2-all"></i> {{ __('Mark all read') }}</button>
                        </div>
                        <div class="np-list" id="notifList">
                            <div class="np-skel"></div><div class="np-skel"></div><div class="np-skel"></div>
                        </div>
                        <a class="np-foot" href="{{ route('user.notifications') }}">{{ __('See all notifications') }} <i class="bi bi-arrow-right rtl-flip"></i></a>
                    </div>
                </div>
            @endauth
        </div>

        <div class="shell-content">
            @yield('content')
        </div>

        @php
            $nav = [
                'home' => request()->routeIs('guest.home', 'user.feed'),
                'explore' => request()->routeIs('guest.search-organizations', 'user.explore-organizations', 'user.explore-channels', 'user.explore-people'),
                'alerts' => request()->routeIs('user.notifications'),
                'me' => request()->routeIs('profile.settings', 'collections.*'),
            ];
        @endphp
        <div class="bottom-nav-wrap" id="bottomNav">
            <nav class="bottom-nav" aria-label="{{ __('Main navigation') }}">
                <a href="{{ route('guest.home') }}" class="bn-item {{ $nav['home'] ? 'active' : '' }}" @if ($nav['home']) aria-current="page" @endif>
                    <i class="bi {{ $nav['home'] ? 'bi-house-door-fill' : 'bi-house-door' }}"></i><span class="bn-label">{{ __('Home') }}</span>
                </a>
                <a href="{{ auth()->check() ? route('user.explore-organizations') : route('guest.search-organizations') }}" class="bn-item {{ $nav['explore'] ? 'active' : '' }}" @if ($nav['explore']) aria-current="page" @endif>
                    <i class="bi {{ $nav['explore'] ? 'bi-compass-fill' : 'bi-compass' }}"></i><span class="bn-label">{{ __('Explore') }}</span>
                </a>
                <div class="bn-center">
                    <button type="button" class="bn-fab" onclick="openQrModal()" aria-label="{{ __('Scan a QR code') }}">
                        <i class="bi bi-qr-code-scan"></i>
                    </button>
                    <span class="bn-label" aria-hidden="true">{{ __('Scan') }}</span>
                </div>
                @auth
                    @php $bnUnread = auth()->user()->notifications()->whereNull('read_at')->count(); @endphp
                    <a href="{{ route('user.notifications') }}" class="bn-item {{ $nav['alerts'] ? 'active' : '' }}" @if ($nav['alerts']) aria-current="page" @endif
                       aria-label="{{ __('Alerts') }}{{ $bnUnread ? ' (' . $bnUnread . ')' : '' }}">
                        <i class="bi {{ $nav['alerts'] ? 'bi-bell-fill' : 'bi-bell' }}"></i>
                        @if ($bnUnread)<span class="bn-badge">{{ $bnUnread > 9 ? '9+' : $bnUnread }}</span>@endif
                        <span class="bn-label">{{ __('Alerts') }}</span>
                    </a>
                    <a href="{{ route('profile.settings') }}" class="bn-item {{ $nav['me'] ? 'active' : '' }}" @if ($nav['me']) aria-current="page" @endif>
                        <span class="bn-me">
                            @if (auth()->user()->avatar_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url(auth()->user()->avatar_path) }}" alt="">
                            @else
                                {{ auth()->user()->initials }}
                            @endif
                        </span>
                        <span class="bn-label">{{ __('Me') }}</span>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="bn-item {{ request()->routeIs('register') ? 'active' : '' }}">
                        <i class="bi bi-person-plus"></i><span class="bn-label">{{ __('Join') }}</span>
                    </a>
                    <a href="{{ route('login') }}" class="bn-item {{ request()->routeIs('login') ? 'active' : '' }}">
                        <i class="bi bi-box-arrow-in-right"></i><span class="bn-label">{{ __('Sign in') }}</span>
                    </a>
                @endauth
            </nav>
        </div>
    </div>

    @include('partials.qr-scan-modal')
    <!-- Page-transition loader -->
    <div id="pageLoaderOverlay" class="page-loader" aria-hidden="true">
        <x-app-loader :size="54"/>
    </div>

    @yield('modals')

    <script>
        (function () {
            // The logo loader appears only when a navigation takes longer than ~180 ms.
            const overlay = document.getElementById('pageLoaderOverlay');
            let loaderTimer = null;
            function startLoader() {
                clearTimeout(loaderTimer);
                loaderTimer = setTimeout(() => overlay.classList.add('show'), 180);
            }
            function stopLoader() {
                clearTimeout(loaderTimer);
                overlay.classList.remove('show');
            }

            document.addEventListener('click', function (event) {
                if (event.defaultPrevented || event.button !== 0) return;
                const link = event.target.closest('a[href]');
                if (!link || link.target === '_blank' || link.hasAttribute('download') || link.dataset.noLoader !== undefined) return;

                const url = new URL(link.href, window.location.href);
                const isSamePageAnchor = url.pathname === window.location.pathname && url.search === window.location.search && url.hash;
                if (url.origin !== window.location.origin || isSamePageAnchor || event.ctrlKey || event.metaKey || event.shiftKey) return;

                startLoader();
            });

            document.addEventListener('submit', function (event) {
                if (!event.defaultPrevented && event.target.tagName === 'FORM' && event.target.dataset.noLoader === undefined) startLoader();
            });

            window.addEventListener('pageshow', stopLoader);

            // App bar shadow once the content scrolls.
            const bar = document.getElementById('appBar');
            const scroller = document.querySelector('.shell-content');
            if (bar && scroller) {
                scroller.addEventListener('scroll', () => bar.classList.toggle('is-scrolled', scroller.scrollTop > 4), { passive: true });
            }

            // Notification bell: panel, mark as read, background refresh.
            const bell = document.getElementById('notifBell');
            if (bell) {
                const btn = document.getElementById('notifBtn');
                const panel = document.getElementById('notifPanel');
                const list = document.getElementById('notifList');
                const badge = document.getElementById('notifBadge');
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                let loaded = false;

                function setCount(n) {
                    const prev = parseInt(badge.textContent, 10) || 0;
                    badge.hidden = n <= 0;
                    badge.textContent = n > 99 ? '99+' : n;
                    btn.classList.toggle('has-unread', n > 0);
                    if (n > prev) { badge.classList.remove('bump'); void badge.offsetWidth; badge.classList.add('bump'); }
                }
                async function refresh() {
                    try {
                        const res = await fetch(bell.dataset.latestUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                        if (!res.ok) return;
                        const data = await res.json();
                        setCount(data.unread);
                        list.innerHTML = data.html;
                        loaded = true;
                    } catch (e) { /* offline: keep what we have */ }
                }
                function open() {
                    panel.hidden = false;
                    btn.setAttribute('aria-expanded', 'true');
                    if (!loaded) refresh();
                }
                function close() {
                    panel.hidden = true;
                    btn.setAttribute('aria-expanded', 'false');
                }
                btn.addEventListener('click', e => { e.preventDefault(); panel.hidden ? open() : close(); });
                document.addEventListener('click', e => { if (!panel.hidden && !bell.contains(e.target)) close(); });
                document.addEventListener('keydown', e => { if (e.key === 'Escape' && !panel.hidden) { close(); btn.focus(); } });

                // Opening a notification marks it read in the background, then follows its link.
                list.addEventListener('click', e => {
                    const item = e.target.closest('.np-item');
                    if (!item || !item.classList.contains('is-unread')) return;
                    fetch(item.dataset.readUrl, { method: 'POST', keepalive: true, credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
                    item.classList.remove('is-unread');
                    item.querySelector('.np-dot')?.remove();
                    setCount(Math.max(0, (parseInt(badge.textContent, 10) || 1) - 1));
                });
                document.getElementById('notifReadAll').addEventListener('click', async () => {
                    list.querySelectorAll('.np-item.is-unread').forEach(i => { i.classList.remove('is-unread'); i.querySelector('.np-dot')?.remove(); });
                    setCount(0);
                    try {
                        await fetch(bell.dataset.readAllUrl, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
                    } catch (e) { /* retried on the next refresh */ }
                });

                // Light polling while the tab is visible.
                setInterval(() => { if (document.visibilityState === 'visible') refresh(); }, 45000);
                document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') refresh(); });
            }
        })();
    </script>

    <script>
        const storedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', storedTheme);

        function toggleShellTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        }
    </script>
    <script>
        /* data-tip popups: hover (mouse), keyboard focus, and press-and-hold on touch screens. */
        (function () {
            let tip = null, target = null, holdTimer = null, hideTimer = null, held = false;

            function show(el) {
                clearTimeout(hideTimer);
                target = el;
                if (!tip) {
                    tip = document.createElement('div');
                    tip.className = 'ip-tip';
                    tip.id = 'ipTip';
                    tip.setAttribute('role', 'tooltip');
                    document.body.appendChild(tip);
                }
                tip.textContent = el.dataset.tip;
                if (el.dataset.tipSub) {
                    const sub = document.createElement('small');
                    sub.textContent = el.dataset.tipSub;
                    tip.appendChild(sub);
                }
                el.setAttribute('aria-describedby', 'ipTip');
                tip.classList.remove('show', 'below');
                const r = el.getBoundingClientRect();
                const t = tip.getBoundingClientRect();
                const margin = 12;
                let left = r.left + r.width / 2 - t.width / 2;
                left = Math.max(margin, Math.min(left, window.innerWidth - t.width - margin));
                let top = r.top - t.height - 10;
                if (top < margin) { top = r.bottom + 10; tip.classList.add('below'); }
                tip.style.left = left + 'px';
                tip.style.top = top + 'px';
                tip.style.setProperty('--arrow-x', (r.left + r.width / 2 - left) + 'px');
                void tip.offsetWidth;   // restart the fade-in without waiting for a frame
                tip.classList.add('show');
            }
            function hide(delay) {
                clearTimeout(hideTimer);
                hideTimer = setTimeout(() => {
                    if (tip) tip.classList.remove('show');
                    if (target) target.removeAttribute('aria-describedby');
                    target = null;
                }, delay || 0);
            }

            document.addEventListener('pointerover', e => {
                const el = e.target.closest('[data-tip]');
                if (el && e.pointerType === 'mouse' && el !== target) show(el);
            });
            document.addEventListener('pointerout', e => {
                const el = e.target.closest('[data-tip]');
                if (el && e.pointerType === 'mouse' && !el.contains(e.relatedTarget)) hide(60);
            });
            document.addEventListener('focusin', e => { const el = e.target.closest('[data-tip]'); if (el) show(el); });
            document.addEventListener('focusout', e => { if (e.target.closest('[data-tip]')) hide(); });

            // Touch: hold ~0.45s to see the full name; the tap that ends a hold doesn't navigate.
            document.addEventListener('touchstart', e => {
                const el = e.target.closest('[data-tip]');
                if (!el) return;
                held = false;
                holdTimer = setTimeout(() => { held = true; show(el); hide(1800); }, 450);
            }, { passive: true });
            ['touchend', 'touchmove', 'touchcancel'].forEach(type => document.addEventListener(type, () => clearTimeout(holdTimer), { passive: true }));
            document.addEventListener('click', e => {
                if (held && e.target.closest('[data-tip]')) { e.preventDefault(); e.stopPropagation(); held = false; }
            }, true);
            document.addEventListener('contextmenu', e => { if (e.target.closest('[data-tip]') && held) e.preventDefault(); });

            window.addEventListener('scroll', () => hide(), true);
            document.addEventListener('keydown', e => { if (e.key === 'Escape') hide(); });
        })();
    </script>
    <script>
        (function () {
            const scroller = document.querySelector('.shell-content');
            const nav = document.getElementById('bottomNav');
            if (!scroller || !nav) return;
            let last = scroller.scrollTop;
            let ticking = false;
            scroller.addEventListener('scroll', function () {
                if (ticking) return;
                ticking = true;
                requestAnimationFrame(function () {
                    const y = scroller.scrollTop;
                    const atEnd = y + scroller.clientHeight >= scroller.scrollHeight - 40;
                    if (y > last + 6 && y > 140 && !atEnd) nav.classList.add('is-hidden');
                    else if (y < last - 6 || y < 140 || atEnd) nav.classList.remove('is-hidden');
                    last = y;
                    ticking = false;
                });
            }, { passive: true });
            // Keyboard users: bring it back as soon as focus moves into it.
            nav.addEventListener('focusin', function () { nav.classList.remove('is-hidden'); });
        })();
    </script>

    <script>
        // Scroll-in reveal for .reveal elements; call window.ipReveal(container) after adding content.
        document.documentElement.classList.add('js-reveal');
        (function () {
            const io = 'IntersectionObserver' in window ? new IntersectionObserver(function (entries) {
                entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); } });
            }, { rootMargin: '0px 0px -40px 0px' }) : null;
            window.ipReveal = function (root) {
                (root || document).querySelectorAll('.reveal:not(.is-visible)').forEach(function (el) { io ? io.observe(el) : el.classList.add('is-visible'); });
            };
            document.addEventListener('DOMContentLoaded', function () { window.ipReveal(); });
        })();
    </script>
    @yield('scripts')
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(() => {});
            });
        }
    </script>
</body>
</html>
