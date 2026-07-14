<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ $organization->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-600: #2563eb;
            --sidebar-bg: #1f2937;
            --sidebar-hover: #374151;
        }

        body {
            background: #f9fafb;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            background: var(--sidebar-bg);
            color: white;
            width: 260px;
            height: 100vh;
            overflow-y: auto;
            position: fixed;
            right: 0;
            top: 0;
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

        .nav-item {
            padding: 0;
            margin: 0;
        }

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
            background: var(--sidebar-hover);
            color: white;
        }

        .nav-link.active {
            background: var(--sidebar-hover);
            color: white;
            border-right-color: var(--primary-600);
        }

        .nav-icon {
            font-size: 1.1rem;
            min-width: 1.5rem;
        }

        .main-content {
            margin-right: 260px;
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
        }

        .topbar {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-right: 0;
                padding: 1rem;
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
    <!-- Sidebar -->
    <div class="sidebar">
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

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <div class="page-title">@yield('title')</div>
            <div class="user-menu">
                <span>👤 {{ auth()->user()->full_name }}</span>
            </div>
        </div>

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
