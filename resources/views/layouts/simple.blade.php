<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'IPAGE Organization Communication Hub')</title>
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body style="background-color: var(--surface-bg); color: var(--text-primary);">
    <main style="min-height: 100vh; padding: var(--space-8) var(--space-4);">
        @if ($errors->any())
            <div style="max-width: 1200px; margin: 0 auto; margin-bottom: var(--space-6);">
                @foreach ($errors->all() as $error)
                    <x-alert-modern type="danger" :title="$error" dismissible />
                @endforeach
            </div>
        @endif

        @if (session('success'))
            <div style="max-width: 1200px; margin: 0 auto; margin-bottom: var(--space-6);">
                <x-alert-modern type="success" :title="session('success')" dismissible />
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        // Dark mode support
        const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);

        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            const newTheme = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }
    </script>
</body>
</html>
