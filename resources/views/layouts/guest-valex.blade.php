@php
    // Public layout for organizations that picked a design template (ADR-012).
    // Valex CSS/JS are loaded here only, never by the classic mobile shell.
    $isRtl = app()->getLocale() === 'ar';
    $cssDir = $isRtl ? 'css-rtl' : 'css';
    $assets = fn (string $path) => asset('assets/valex/' . $path);
    $primary = $primary ?? '#6259ca';
    $secondary = $secondary ?? $primary;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $primary }}">
    <title>@yield('title', 'i-Page') — {{ config('app.name', 'i-Page') }}</title>
    <link rel="icon" href="{{ $assets('img/brand/favicon.png') }}" type="image/x-icon">

    <link href="{{ $assets($cssDir . '/icons.css') }}" rel="stylesheet">
    <link href="{{ $assets($cssDir . '/style.css') }}" rel="stylesheet">
    <link href="{{ $assets($cssDir . '/style-dark.css') }}" rel="stylesheet">
    @stack('styles')

    <style>
        :root { --org-primary: {{ $primary }}; --org-secondary: {{ $secondary }}; }
        body.org-public { display: block; background: #f4f5fb; }
        body.org-public.dark-theme { background: #141b2d; }
        body.org-public::before { display: none; }
        .org-topbar { position: sticky; top: 0; z-index: 100; background: #fff; border-bottom: 1px solid rgba(0,0,0,.06); }
        .dark-theme .org-topbar { background: #1e2740; border-color: rgba(255,255,255,.08); }
        .org-topbar .container { display: flex; align-items: center; justify-content: space-between; min-height: 56px; }
        .org-page { padding-top: 1rem; padding-bottom: 3rem; }
        .org-hero-fig { border-radius: 14px; overflow: hidden; }
        /* Apply the organization's colors on top of Valex's palette. */
        .org-public .btn-primary { background-color: var(--org-primary); border-color: var(--org-primary); }
        .org-public .btn-outline-primary { color: var(--org-primary); border-color: var(--org-primary); }
        .org-public .btn-outline-primary:hover { background-color: var(--org-primary); color: #fff; }
        .org-public a:not(.btn):not(.nav-link):not(.list-group-item) { color: var(--org-primary); }
        .org-public .text-primary { color: var(--org-primary) !important; }
        .org-public .bg-primary, .org-public .badge-primary, .org-public .progress-bar { background-color: var(--org-primary) !important; }
        .org-public .bg-primary-gradient { background-image: linear-gradient(135deg, var(--org-primary), var(--org-secondary)) !important; }
        .org-public .bg-primary-transparent { background-color: rgba(0,0,0,.05) !important; color: var(--org-primary) !important; }
        .org-public .timeline-badge { background-color: var(--org-primary); }
        .org-public .nav-pills .nav-link.active { background-color: var(--org-primary); }
        /* Cover captions sit above the cover carousel; only their links/buttons receive clicks so arrows stay usable. */
        .org-hero-fig { overflow: hidden; }
        .org-hero-fig figcaption { pointer-events: none; }
        .org-hero-fig figcaption a, .org-hero-fig figcaption button, .org-hero-fig figcaption form { pointer-events: auto; }
        .org-card-link { color: inherit !important; text-decoration: none; }
        .org-card-link:hover .card { box-shadow: 0 8px 24px rgba(0,0,0,.12); transform: translateY(-2px); }
        .org-card-link .card { transition: all .2s ease; }
    </style>
</head>

<body class="main-body org-public">
    <header class="org-topbar">
        <div class="container">
            <a href="{{ $backUrl ?? route('guest.home') }}" class="d-flex align-items-center" style="color: inherit;">
                <i class="fe fe-arrow-{{ $isRtl ? 'right' : 'left' }} mx-2"></i>
                <strong class="text-truncate" style="max-width: 60vw;">@yield('title', config('app.name'))</strong>
            </a>
            <div class="d-flex align-items-center">
                <a href="#" id="orgThemeToggle" class="mx-2" title="{{ __('Toggle Theme') }}"><i class="fe fe-moon"></i></a>
                @guest
                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary mx-1">{{ __('Sign In') }}</a>
                @endguest
            </div>
        </div>
    </header>

    <main class="container org-page">
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @yield('content')
    </main>

    @yield('modals')

    <script src="{{ $assets('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ $assets('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
    <script>
        // Share the theme preference with the rest of the public site (localStorage 'theme').
        (function () {
            var body = document.body, toggle = document.getElementById('orgThemeToggle');
            try { if (localStorage.getItem('theme') === 'dark') body.classList.add('dark-theme'); } catch (e) {}
            function paint() { toggle.querySelector('i').className = body.classList.contains('dark-theme') ? 'fe fe-sun' : 'fe fe-moon'; }
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                body.classList.toggle('dark-theme');
                try { localStorage.setItem('theme', body.classList.contains('dark-theme') ? 'dark' : 'light'); } catch (err) {}
                paint();
            });
            paint();
        })();
    </script>
</body>
</html>
