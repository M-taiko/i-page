<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ auth()->check() && auth()->user()->language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - i-Page</title>

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

        .admin-sidebar {
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
            font-size: 1.25rem;
            font-weight: 700;
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        .admin-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .admin-header {
            background: var(--surface-bg);
            border-bottom: 1px solid var(--surface-border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .admin-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
        }

        .admin-main {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .admin-sidebar-nav {
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
    <div class="admin-sidebar">
        <div class="sidebar-header">
            <a href="/" class="sidebar-brand">
                <i class="bi bi-gear-fill"></i>
                <span>i-Page</span>
            </a>
            <div style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.7); margin-top: 0.5rem;">
                Super Admin
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('admin.organizations.index') }}"
               class="nav-link {{ request()->routeIs('admin.organizations.*') ? 'active' : '' }}">
                <i class="bi bi-building nav-icon"></i>
                <span>المنظمات</span>
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

    <div class="admin-content">
        <div class="admin-header">
            <h1>@yield('title')</h1>
            <span>👤 {{ auth()->user()->full_name }}</span>
        </div>

        <div class="admin-main">
            @yield('content')
        </div>
    </div>
</body>
</html>
