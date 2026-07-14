<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'i-Page')</title>

    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
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
            height: 100vh;
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
            padding-bottom: calc(72px + env(safe-area-inset-bottom));
        }

        /* Bottom Navigation — small, narrow, truly floating pill dock.
           The wrap has NO background of its own so only the pill itself
           is visible, giving a "floating above the content" look. */
        .bottom-nav-wrap {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            padding-bottom: max(var(--space-3), env(safe-area-inset-bottom));
            background: transparent;
            z-index: 20;
            pointer-events: none;
        }

        .bottom-nav {
            pointer-events: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-1);
            background-color: var(--primary-100);
            border: 1px solid var(--primary-400);
            border-radius: 999px;
            padding: 6px 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.14), 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        [data-theme="dark"] .bottom-nav {
            background-color: #1b2559;
            border-color: var(--primary-500);
        }

        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1px;
            text-decoration: none;
            color: var(--text-tertiary);
            font-size: 9px;
            font-weight: var(--font-weight-medium);
            padding: 6px 9px;
            border-radius: 999px;
            min-width: 38px;
            transition: color var(--transition-fast), background-color var(--transition-fast);
        }

        .bottom-nav-item.active {
            background-color: rgba(69, 87, 245, 0.14);
        }

        .bottom-nav-item i { font-size: var(--text-base); }

        .bottom-nav-item.active { color: var(--primary-600); }

        .bottom-nav-fab {
            width: 36px;
            height: 36px;
            margin: 0 2px;
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, var(--primary-600), var(--secondary-600));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: var(--text-lg);
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(69, 87, 245, 0.4);
            flex-shrink: 0;
        }

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

            @auth
                @unless(request()->routeIs('user.notifications'))
                    @php
                        $unreadNotifCount = auth()->user()->notifications()->whereNull('read_at')->count();
                    @endphp
                    <a href="{{ route('user.notifications') }}" class="app-bar-icon-btn app-bar-bell" aria-label="{{ __('Notifications') }}" style="position: relative;">
                        <i class="bi bi-bell"></i>
                        @if($unreadNotifCount > 0)
                            <span class="app-bar-bell-badge">{{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}</span>
                        @endif
                    </a>
                @endunless

                @unless(request()->routeIs('profile.settings'))
                    <a href="{{ route('profile.settings') }}" class="app-bar-avatar" aria-label="{{ __('Profile') }}">
                        @if(auth()->user()->avatar_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url(auth()->user()->avatar_path) }}" alt="{{ auth()->user()->full_name }}">
                        @else
                            {{ auth()->user()->initials }}
                        @endif
                    </a>
                @endunless
            @endauth
        </div>

        <div class="shell-content">
            @yield('content')
        </div>

        <div class="bottom-nav-wrap">
        <nav class="bottom-nav">
            @auth
                <a href="{{ route('user.feed') }}" class="bottom-nav-item {{ request()->routeIs('user.feed') ? 'active' : '' }}">
                    <i class="bi {{ request()->routeIs('user.feed') ? 'bi-house-fill' : 'bi-house' }}"></i>
                    <span>{{ __('Home') }}</span>
                </a>
                @if(auth()->user()->hasRole('super_admin'))
                    <a href="{{ route('admin.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        <i class="bi {{ request()->routeIs('admin.*') ? 'bi-speedometer2' : 'bi-speedometer' }}"></i>
                        <span>{{ __('Dashboard') }}</span>
                    </a>
                @elseif(auth()->user()->organizations()->exists())
                    <a href="{{ route('organizations.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('organizations.dashboard') ? 'active' : '' }}">
                        <i class="bi {{ request()->routeIs('organizations.dashboard') ? 'bi-speedometer2' : 'bi-speedometer' }}"></i>
                        <span>{{ __('Dashboard') }}</span>
                    </a>
                @endif
                <a href="{{ route('collections.index') }}" class="bottom-nav-item {{ request()->routeIs('collections.*') ? 'active' : '' }}">
                    <i class="bi {{ request()->routeIs('collections.*') ? 'bi-folder-fill' : 'bi-folder' }}"></i>
                    <span>{{ __('Collections') }}</span>
                </a>
                <a href="{{ route('user.explore-organizations') }}" class="bottom-nav-item {{ request()->routeIs('user.explore-organizations') || request()->routeIs('user.explore-channels') ? 'active' : '' }}">
                    <i class="bi {{ request()->routeIs('user.explore-organizations') || request()->routeIs('user.explore-channels') ? 'bi-compass-fill' : 'bi-compass' }}"></i>
                    <span>{{ __('Discover') }}</span>
                </a>
                <a href="{{ route('user.notifications') }}" class="bottom-nav-item {{ request()->routeIs('user.notifications') ? 'active' : '' }}" style="position: relative;">
                    <i class="bi {{ request()->routeIs('user.notifications') ? 'bi-bell-fill' : 'bi-bell' }}"></i>
                    @php
                        $bottomNavUnreadCount = auth()->user()->notifications()->whereNull('read_at')->count();
                    @endphp
                    @if($bottomNavUnreadCount > 0)
                        <span style="position: absolute; top: 0; right: 6px; background-color: var(--danger-600); color: white; font-size: 8px; font-weight: 700; min-width: 13px; height: 13px; border-radius: 999px; display: flex; align-items: center; justify-content: center; padding: 0 2px; border: 1.5px solid var(--primary-50);">{{ $bottomNavUnreadCount > 9 ? '9+' : $bottomNavUnreadCount }}</span>
                    @endif
                    <span>{{ __('Alerts') }}</span>
                </a>
                <a href="{{ route('profile.settings') }}" class="bottom-nav-item {{ request()->routeIs('profile.settings') ? 'active' : '' }}">
                    <i class="bi {{ request()->routeIs('profile.settings') ? 'bi-person-fill' : 'bi-person' }}"></i>
                    <span>{{ __('Profile') }}</span>
                </a>
            @else
                <a href="{{ route('guest.home') }}" class="bottom-nav-item {{ request()->routeIs('guest.home') ? 'active' : '' }}">
                    <i class="bi {{ request()->routeIs('guest.home') ? 'bi-house-fill' : 'bi-house' }}"></i>
                    <span>{{ __('Home') }}</span>
                </a>
                <a href="{{ route('guest.search-organizations') }}" class="bottom-nav-item {{ request()->routeIs('guest.search-organizations') ? 'active' : '' }}">
                    <i class="bi {{ request()->routeIs('guest.search-organizations') ? 'bi-grid-fill' : 'bi-grid' }}"></i>
                    <span>{{ __('Discover') }}</span>
                </a>
                <a href="{{ route('login') }}" class="bottom-nav-fab" title="{{ __('Sign In') }}">
                    <i class="bi bi-box-arrow-in-right"></i>
                </a>
                <button type="button" class="bottom-nav-item" style="background:none;border:none;" onclick="toggleShellTheme()">
                    <i class="bi bi-moon-stars"></i>
                    <span>{{ __('Theme') }}</span>
                </button>
                <a href="{{ route('register') }}" class="bottom-nav-item {{ request()->routeIs('register') ? 'active' : '' }}">
                    <i class="bi {{ request()->routeIs('register') ? 'bi-person-plus-fill' : 'bi-person-plus' }}"></i>
                    <span>{{ __('Sign Up') }}</span>
                </a>
            @endauth
        </nav>
        </div>
    </div>

    @yield('modals')

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
    @yield('scripts')
</body>
</html>
