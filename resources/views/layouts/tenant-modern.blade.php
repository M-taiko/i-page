<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ auth()->check() && auth()->user()->language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ $organization->name }}</title>

    <!-- Design System & Components -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        body {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .tenant-sidebar {
            flex-shrink: 0;
            width: 260px;
            background: var(--sidebar-bg, #1f2937);
            color: white;
            height: 100vh;
            overflow-y: auto;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand {
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            color: white;
            display: block;
        }

        .sidebar-org-name {
            font-size: 1.1rem;
            font-weight: 700;
            margin-top: 0.5rem;
            word-break: break-word;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item { padding: 0; margin: 0; }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            transition: all 0.2s;
            border-right: 3px solid transparent;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-right-color: var(--primary-600, #2563eb);
        }

        .nav-icon {
            font-size: 1.1rem;
            min-width: 1.5rem;
        }

        .tenant-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .tenant-header {
            background: var(--surface-bg);
            border-bottom: 1px solid var(--surface-border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .tenant-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
        }

        .tenant-main {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
        }

        @media (max-width: 768px) {
            .tenant-sidebar {
                width: 100%;
                height: auto;
            }

            .sidebar-nav {
                display: flex;
                flex-wrap: wrap;
            }

            .nav-link {
                flex: 1;
                justify-content: center;
                border-right: none;
                border-bottom: 3px solid transparent;
            }

            .nav-link.active {
                border-right: none;
                border-bottom-color: var(--primary-600);
            }
        }
    </style>
</head>
<body>
    <div class="tenant-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">📱 i-Page</div>
            <div class="sidebar-org-name">{{ $organization->name }}</div>
            <div style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.7); margin-top: 0.5rem;">
                Organization Admin
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('tenant.dashboard') }}"
               class="nav-link {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 nav-icon"></i>
                <span>لوحة التحكم</span>
            </a>

            <a href="{{ route('tenant.channels.index') }}"
               class="nav-link {{ request()->routeIs('tenant.channels.*') ? 'active' : '' }}">
                <i class="bi bi-tv nav-icon"></i>
                <span>القنوات</span>
            </a>

            <hr style="margin: 0.5rem 0; border-color: rgba(255, 255, 255, 0.1);">

            <div style="padding: 0.75rem 1.5rem; color: rgba(255, 255, 255, 0.5); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">
                الحساب
            </div>

            <form action="{{ route('logout') }}" method="POST" style="padding: 0;">
                @csrf
                <button type="submit" class="nav-link w-100 text-start border-0" style="background: none;">
                    <i class="bi bi-box-arrow-left nav-icon"></i>
                    <span>تسجيل الخروج</span>
                </button>
            </form>
        </nav>
    </div>

    <div class="tenant-content">
        <div class="tenant-header">
            <h1>@yield('title')</h1>
            <span>👤 {{ auth()->user()->full_name }}</span>
        </div>

        <div class="tenant-main">
            @yield('content')
        </div>
    </div>
</body>
</html>
