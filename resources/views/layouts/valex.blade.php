@php
    // Valex ships separate LTR/RTL builds; pick one from the user's language (ADR-010, ADR-011).
    $isRtl = auth()->check() && auth()->user()->language === 'ar';
    $cssDir = $isRtl ? 'css-rtl' : 'css';
    $assets = fn (string $path) => asset('assets/valex/' . $path);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'i-Page') — {{ config('app.name', 'i-Page') }}</title>
    <link rel="icon" href="{{ $assets('img/brand/favicon.png') }}" type="image/x-icon">

    <link href="{{ $assets($cssDir . '/icons.css') }}" rel="stylesheet">
    <link href="{{ $assets('plugins/mscrollbar/jquery.mCustomScrollbar.css') }}" rel="stylesheet">
    <link href="{{ $assets('plugins/sidebar/sidebar.css') }}" rel="stylesheet">
    <link href="{{ $assets($cssDir . '/sidemenu.css') }}" rel="stylesheet">
    @yield('css')
    <link href="{{ $assets($cssDir . '/style.css') }}" rel="stylesheet">
    <link href="{{ $assets($cssDir . '/style-dark.css') }}" rel="stylesheet">
    <link href="{{ $assets($cssDir . '/skin-modes.css') }}" rel="stylesheet">
    @stack('styles')

    {{-- Apply the saved theme before first paint to avoid a flash. --}}
    <script>
        try {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark-theme-pending');
            }
        } catch (e) {}
    </script>
</head>

<body class="main-body app sidebar-mini">
    <script>
        if (document.documentElement.classList.contains('dark-theme-pending')) {
            document.body.classList.add('dark-theme');
        }
    </script>

    <div id="global-loader">
        <img src="{{ $assets('img/loader.svg') }}" class="loader-img" alt="">
    </div>

    @include('layouts.valex.sidebar')

    <div class="main-content app-content">
        @include('layouts.valex.header')

        <div class="container-fluid">
            @hasSection('page-header')
                @yield('page-header')
            @endif

            @include('layouts.valex.flash')

            @yield('content')
        </div>
    </div>

    @include('layouts.valex.footer')

    <a href="#top" id="back-to-top"><i class="las la-angle-double-up"></i></a>

    <script src="{{ $assets('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ $assets('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ $assets('plugins/moment/moment.js') }}"></script>
    <script src="{{ $assets('plugins/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ $assets('plugins/perfect-scrollbar/p-scroll.js') }}"></script>
    <script src="{{ $assets('plugins/jquery-sparkline/jquery.sparkline.min.js') }}"></script>
    <script src="{{ $assets('plugins/mscrollbar/jquery.mCustomScrollbar.concat.min.js') }}"></script>
    <script src="{{ $assets('plugins/sidebar/' . ($isRtl ? 'sidebar-rtl.js' : 'sidebar.js')) }}"></script>
    <script src="{{ $assets('plugins/sidebar/sidebar-custom.js') }}"></script>
    @yield('js')
    @stack('scripts')
    <script src="{{ $assets('js/sticky.js') }}"></script>
    <script src="{{ $assets('js/custom.js') }}"></script>
    <script src="{{ $assets('plugins/side-menu/sidemenu.js') }}"></script>

    <script>
        // Dark mode: Valex switches on `body.dark-theme`. Preference is kept per browser;
        // the toggle lives in the header (#valexThemeToggle).
        (function () {
            var body = document.body;
            var toggle = document.getElementById('valexThemeToggle');
            if (!toggle) return;
            function paint() {
                var dark = body.classList.contains('dark-theme');
                toggle.querySelector('i').className = dark ? 'fe fe-sun' : 'fe fe-moon';
            }
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                body.classList.toggle('dark-theme');
                try {
                    localStorage.setItem('theme', body.classList.contains('dark-theme') ? 'dark' : 'light');
                } catch (err) {}
                paint();
            });
            paint();
        })();
    </script>
</body>
</html>
