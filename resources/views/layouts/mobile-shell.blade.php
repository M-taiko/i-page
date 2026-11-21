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

        /* Top App Bar */
        .app-bar {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-3) var(--space-4);
            padding-top: max(var(--space-3), env(safe-area-inset-top));
            background-color: var(--surface-bg);
            border-bottom: 1px solid var(--surface-border);
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .app-bar-icon-btn {
            background: none;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            font-size: var(--text-xl);
            cursor: pointer;
            text-decoration: none;
            flex-shrink: 0;
            transition: background-color var(--transition-fast);
        }

        .app-bar-icon-btn:hover { background-color: var(--surface-hover); }

        .app-bar-title {
            flex: 1;
            font-size: var(--text-lg);
            font-weight: var(--font-weight-bold);
            color: var(--text-primary);
        }

        .app-bar-actions {
            display: flex;
            align-items: center;
            gap: var(--space-1);
        }

        .app-bar-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-600), var(--secondary-600));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: var(--font-weight-bold);
            text-decoration: none;
            flex-shrink: 0;
            overflow: hidden;
            border: 1.5px solid var(--surface-border);
            margin-inline-start: 2px;
        }

        .app-bar-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .app-bar-bell-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background-color: var(--danger-600);
            color: white;
            font-size: 9px;
            font-weight: var(--font-weight-bold);
            min-width: 15px;
            height: 15px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 3px;
            border: 1.5px solid var(--surface-bg);
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
        <div class="app-bar">
            @yield('app-bar')

            <button type="button" class="app-bar-icon-btn theme-toggle" onclick="toggleShellTheme()" aria-label="{{ __('Switch light / dark mode') }}">
                <i class="bi bi-moon-stars"></i><i class="bi bi-sun"></i>
            </button>
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
    <div id="pageLoaderOverlay" style="display: none; position: fixed; inset: 0; background-color: var(--surface-bg); z-index: 999; align-items: center; justify-content: center;">
        <x-app-loader :size="72"/>
    </div>

    @yield('modals')

    <script>
        (function () {
            const overlay = document.getElementById('pageLoaderOverlay');

            document.addEventListener('click', function (event) {
                const link = event.target.closest('a[href]');
                if (!link || link.target === '_blank' || link.hasAttribute('download')) return;

                const url = new URL(link.href, window.location.href);
                const isSamePageAnchor = url.pathname === window.location.pathname && url.hash;
                if (url.origin !== window.location.origin || isSamePageAnchor || event.ctrlKey || event.metaKey) return;

                overlay.style.display = 'flex';
            });

            document.addEventListener('submit', function (event) {
                if (event.target.tagName === 'FORM') {
                    overlay.style.display = 'flex';
                }
            });

            window.addEventListener('pageshow', function () {
                overlay.style.display = 'none';
            });
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
