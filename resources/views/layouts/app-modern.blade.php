<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ auth()->check() && auth()->user()->language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'IPAGE') - Organization Communication Hub</title>

    <!-- Design System & Components -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Main Layout */
        body {
            display: flex;
        }

        .app-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .app-sidebar {
            flex-shrink: 0;
            width: 260px;
        }

        @media (max-width: 1024px) {
            .app-sidebar {
                width: 240px;
            }
        }

        @media (max-width: 768px) {
            .app-sidebar {
                width: 0;
            }
        }

        .app-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: var(--surface-bg-secondary);
        }

        .app-top-nav {
            height: 64px;
            background-color: var(--surface-bg);
            border-bottom: 1px solid var(--surface-border);
            display: flex;
            align-items: center;
            padding: 0 var(--space-6);
            gap: var(--space-4);
            position: sticky;
            top: 0;
            z-index: var(--z-sticky);
        }

        .app-top-nav-spacer {
            flex: 1;
        }

        .app-top-nav-items {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .app-top-nav-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: var(--text-lg);
            cursor: pointer;
            padding: var(--space-2);
            border-radius: var(--radius-lg);
            transition: all var(--transition-fast);
        }

        .app-top-nav-btn:hover {
            color: var(--text-primary);
            background-color: var(--surface-hover);
        }

        .notif-bell-wrap { position: relative; }

        .notif-badge {
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
            pointer-events: none;
        }

        .notif-panel {
            display: none;
            position: absolute;
            top: calc(100% + var(--space-2));
            right: 0;
            width: 340px;
            max-width: 90vw;
            max-height: 420px;
            overflow-y: auto;
            background-color: var(--surface-bg);
            border: 1px solid var(--surface-border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl, 0 12px 32px rgba(0,0,0,0.16));
            z-index: var(--z-dropdown, 50);
        }

        .notif-panel.show { display: block; }

        .notif-panel-header {
            padding: var(--space-3) var(--space-4);
            font-weight: var(--font-weight-bold);
            font-size: var(--text-sm);
            border-bottom: 1px solid var(--surface-border);
            color: var(--text-primary);
        }

        .notif-panel-item {
            display: block;
            padding: var(--space-3) var(--space-4);
            border-bottom: 1px solid var(--surface-border);
            text-decoration: none;
            color: var(--text-primary);
            font-size: var(--text-sm);
        }

        .notif-panel-item:hover { background-color: var(--surface-hover); }
        .notif-panel-item:last-child { border-bottom: none; }
        .notif-panel-item.unread { background-color: var(--primary-50); }

        .notif-panel-time {
            font-size: var(--text-xs);
            color: var(--text-tertiary);
            margin-top: 2px;
        }

        .notif-panel-empty {
            padding: var(--space-6) var(--space-4);
            text-align: center;
            color: var(--text-secondary);
            font-size: var(--text-sm);
        }

        .notif-panel-footer {
            padding: var(--space-3) var(--space-4);
            text-align: center;
            border-top: 1px solid var(--surface-border);
        }

        .notif-panel-footer a {
            font-size: var(--text-sm);
            color: var(--primary-600);
            text-decoration: none;
            font-weight: var(--font-weight-medium);
        }

        .app-content {
            flex: 1;
            overflow-y: auto;
            padding: var(--space-8);
        }

        @media (max-width: 640px) {
            .app-content {
                padding: var(--space-4);
            }
        }

        /* Breadcrumbs */
        .breadcrumb-modern {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            font-size: var(--text-sm);
            margin-bottom: var(--space-6);
        }

        .breadcrumb-modern a {
            color: var(--primary-600);
            text-decoration: none;
        }

        .breadcrumb-modern a:hover {
            text-decoration: underline;
        }

        .breadcrumb-modern .separator {
            color: var(--text-tertiary);
        }

        /* Page Header */
        .page-header {
            margin-bottom: var(--space-8);
        }

        .page-header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: var(--space-4);
            flex-wrap: wrap;
        }

        .page-header-info h1 {
            margin-bottom: var(--space-2);
        }

        .page-header-info p {
            margin: 0;
            font-size: var(--text-sm);
        }

        .page-header-actions {
            display: flex;
            gap: var(--space-3);
            flex-wrap: wrap;
        }

        /* Toast Notification */
        .toast-container {
            position: fixed;
            top: var(--space-6);
            right: var(--space-6);
            z-index: var(--z-tooltip);
            max-width: 400px;
        }

        @media (max-width: 640px) {
            .toast-container {
                left: var(--space-4);
                right: var(--space-4);
                max-width: none;
            }
        }

        .toast {
            background-color: var(--surface-bg);
            border-radius: var(--radius-lg);
            padding: var(--space-4);
            margin-bottom: var(--space-3);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: var(--space-3);
            animation: slideDown var(--transition-base);
        }

        .toast.success {
            border-left: 4px solid var(--success-500);
            color: var(--success-700);
        }

        .toast.error {
            border-left: 4px solid var(--danger-500);
            color: var(--danger-700);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .app-top-nav {
                padding: 0 var(--space-4);
            }

            .page-header-top {
                flex-direction: column;
            }

            .page-header-actions {
                width: 100%;
            }

            .page-header-actions .btn {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="app-sidebar">
            @if(auth()->check() && (auth()->user()->hasRole('super_admin') || auth()->user()->organizations()->count() > 0))
                @include('layouts.sidebar-modern')
            @else
                @include('layouts.sidebar-guest')
            @endif
        </div>

        <!-- Main Content -->
        <div class="app-main">
            <!-- Top Navigation -->
            <header class="app-top-nav">
                <div class="app-top-nav-spacer"></div>

                <div class="app-top-nav-items">
                    <!-- Search -->
                    <button class="app-top-nav-btn" id="searchBtn" title="{{ __('Search') }}">
                        <i class="bi bi-search"></i>
                    </button>

                    <!-- Notifications -->
                    @auth
                        @php
                            $navNotifications = auth()->user()->notifications()->latest()->take(8)->get();
                            $navUnreadCount = $navNotifications->whereNull('read_at')->count();
                        @endphp
                        <div class="notif-bell-wrap">
                            <button type="button" class="app-top-nav-btn" title="{{ __('Notifications') }}" onclick="toggleNotifPanel(event)">
                                <i class="bi bi-bell"></i>
                                @if($navUnreadCount > 0)
                                    <span class="notif-badge">{{ $navUnreadCount > 9 ? '9+' : $navUnreadCount }}</span>
                                @endif
                            </button>
                            <div class="notif-panel" id="notifPanel">
                                <div class="notif-panel-header">{{ __('Notifications') }}</div>
                                @forelse($navNotifications as $notification)
                                    <a href="{{ $notification->data['link'] ?? route('user.notifications') }}"
                                       class="notif-panel-item {{ $notification->isRead() ? '' : 'unread' }}">
                                        <div>{{ $notification->data['message'] ?? ucfirst(str_replace('_', ' ', $notification->type)) }}</div>
                                        <div class="notif-panel-time">{{ $notification->created_at->diffForHumans() }}</div>
                                    </a>
                                @empty
                                    <div class="notif-panel-empty">{{ __('No notifications yet') }}</div>
                                @endforelse
                                <div class="notif-panel-footer">
                                    <a href="{{ route('user.notifications') }}">{{ __('View All') }}</a>
                                </div>
                            </div>
                        </div>
                    @else
                        <button class="app-top-nav-btn" title="{{ __('Notifications') }}" disabled>
                            <i class="bi bi-bell"></i>
                        </button>
                    @endauth

                    <!-- Theme Toggle -->
                    <button class="app-top-nav-btn" id="themeToggle" title="{{ __('Toggle Theme') }}">
                        <i class="bi bi-moon"></i>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="app-content">
                <!-- Flash Messages -->
                @if (session('success'))
                    <x-alert-modern type="success" dismissible>
                        {{ session('success') }}
                    </x-alert-modern>
                @endif

                @if (session('error'))
                    <x-alert-modern type="danger" dismissible>
                        {{ session('error') }}
                    </x-alert-modern>
                @endif

                <!-- Page Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Search Modal -->
    <div id="searchModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; padding-top: 60px;">
        <div style="background: var(--surface-bg); max-width: 600px; margin: 0 auto; border-radius: var(--radius-lg); box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden;">
            <div style="padding: var(--space-4); border-bottom: 1px solid var(--surface-border);">
                <input type="text" id="searchInput" placeholder="Search organizations..." style="width: 100%; padding: var(--space-3) var(--space-4); border: 1px solid var(--surface-border); border-radius: var(--radius-lg); font-size: var(--text-base); color: var(--text-primary); background: var(--surface-bg); outline: none;">
            </div>
            <div id="searchResults" style="max-height: 400px; overflow-y: auto;">
                <div style="padding: var(--space-6); text-align: center; color: var(--text-secondary);">
                    <i class="bi bi-search" style="font-size: 2rem; margin-bottom: var(--space-2);"></i>
                    <p>Type to search organizations</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Theme Toggle Script -->
    <script>
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        const storedTheme = localStorage.getItem('theme');

        // Default to 'light' theme if not set
        const isDark = storedTheme === 'dark';

        if (isDark) {
            htmlElement.setAttribute('data-theme', 'dark');
        } else {
            htmlElement.setAttribute('data-theme', 'light');
        }
        updateThemeIcon();

        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            htmlElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon();
        });

        function updateThemeIcon() {
            const icon = themeToggle.querySelector('i');
            const isDark = htmlElement.getAttribute('data-theme') === 'dark';
            icon.className = isDark ? 'bi bi-sun' : 'bi bi-moon';
        }

        // Close alerts
        document.querySelectorAll('[data-dismiss="alert"]').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.closest('.alert').style.animation = 'slideUp 0.3s ease-out forwards';
                setTimeout(() => btn.closest('.alert').remove(), 300);
            });
        });

        // Search functionality
        const searchBtn = document.getElementById('searchBtn');
        const searchModal = document.getElementById('searchModal');
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;

        searchBtn.addEventListener('click', () => {
            searchModal.style.display = 'block';
            searchInput.focus();
        });

        searchModal.addEventListener('click', (e) => {
            if (e.target === searchModal) {
                searchModal.style.display = 'none';
            }
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                searchModal.style.display = 'none';
            }
        });

        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();

            if (query.length === 0) {
                searchResults.innerHTML = '<div style="padding: var(--space-6); text-align: center; color: var(--text-secondary);"><i class="bi bi-search" style="font-size: 2rem; margin-bottom: var(--space-2);"></i><p>Type to search organizations</p></div>';
                return;
            }

            searchResults.innerHTML = '<div style="padding: var(--space-4); text-align: center; color: var(--text-secondary);">Searching...</div>';

            searchTimeout = setTimeout(() => {
                fetch(`/api/organizations/search?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.organizations && data.organizations.length > 0) {
                            searchResults.innerHTML = data.organizations.map(org => `
                                <a href="/organization/${org.id}/dashboard" style="display: flex; align-items: center; gap: var(--space-3); padding: var(--space-4); border-bottom: 1px solid var(--surface-border); text-decoration: none; color: var(--text-primary); transition: all var(--transition-fast);" onmouseover="this.style.backgroundColor='var(--surface-hover)'" onmouseout="this.style.backgroundColor='transparent'">
                                    <div style="width: 44px; height: 44px; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--primary-600), var(--secondary-600)); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; flex-shrink: 0;">
                                        ${org.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; font-size: var(--text-sm);">${org.name}</div>
                                        <div style="font-size: var(--text-xs); color: var(--text-secondary);">${org.users_count || 0} members • ${org.channels_count || 0} channels</div>
                                    </div>
                                </a>
                            `).join('');
                        } else {
                            searchResults.innerHTML = '<div style="padding: var(--space-6); text-align: center; color: var(--text-secondary);">No organizations found</div>';
                        }
                    })
                    .catch(err => {
                        searchResults.innerHTML = '<div style="padding: var(--space-6); text-align: center; color: var(--danger-600);">Error searching organizations</div>';
                    });
            }, 300);
        });
    </script>

    <script>
        // This layout doesn't load Bootstrap's JS bundle, so data-bs-toggle
        // never actually opens anything — implement the minimal show/hide
        // behavior ourselves for the design-system .modal component.
        document.addEventListener('click', function (event) {
            const opener = event.target.closest('[data-bs-toggle="modal"]');
            if (opener) {
                const target = document.querySelector(opener.getAttribute('data-bs-target'));
                if (target) openModal(target);
                return;
            }

            const dismisser = event.target.closest('[data-bs-dismiss="modal"]');
            if (dismisser) {
                const modal = dismisser.closest('.modal');
                if (modal) closeModal(modal);
            }
        });

        function openModal(modal) {
            let backdrop = document.querySelector('.modal-backdrop');
            if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop';
                document.body.appendChild(backdrop);
            }
            backdrop.classList.add('show');
            backdrop.onclick = () => closeModal(modal);
            modal.classList.add('show');
        }

        function closeModal(modal) {
            modal.classList.remove('show');
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
        }
    </script>

    <script>
        function toggleNotifPanel(event) {
            event.stopPropagation();
            document.getElementById('notifPanel')?.classList.toggle('show');
        }

        document.addEventListener('click', function (event) {
            const panel = document.getElementById('notifPanel');
            if (panel && panel.classList.contains('show') && !panel.contains(event.target)) {
                panel.classList.remove('show');
            }
        });
    </script>
</body>
</html>
