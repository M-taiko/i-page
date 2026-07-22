<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Private Channel') }} - {{ $channel->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-600: #2563eb;
            --primary-50: #eff6ff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, var(--primary-50), #f9fafb);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .container-fluid { max-width: 440px; width: 100%; }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            text-align: center;
        }

        .card-header {
            background: linear-gradient(135deg, {{ $state === 'denied' ? '#dc2626, #b91c1c' : 'var(--primary-600), #1d4ed8' }});
            color: white;
            padding: 2.5rem 2rem;
        }

        .card-header i { font-size: 2.5rem; margin-bottom: 0.75rem; display: block; }

        .card-header h1 { font-size: 1.4rem; font-weight: 700; margin-bottom: 0.25rem; }

        .card-body { padding: 2rem; }

        .card-body p { color: var(--text-secondary); margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="card">
            @if($state === 'login-required')
                <div class="card-header">
                    <i class="bi bi-lock-fill"></i>
                    <h1>{{ __('This is a Private Channel') }}</h1>
                </div>
                <div class="card-body">
                    <p>{{ __(':name is private. Sign in to see if you have access.', ['name' => $channel->name]) }}</p>
                    <a href="{{ route('login') }}" class="btn btn-primary w-100 mb-2">{{ __('Sign In') }}</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-secondary w-100">{{ __('Create Account') }}</a>
                </div>
            @else
                <div class="card-header">
                    <i class="bi bi-shield-lock-fill"></i>
                    <h1>{{ __('Access Not Allowed') }}</h1>
                </div>
                <div class="card-body">
                    @if($hasPendingRequest ?? false)
                        <p>{{ __('Your request to join :name is still pending approval.', ['name' => $channel->name]) }}</p>
                    @else
                        <p>{{ __('You don\'t have permission to view :name. This private channel requires an invitation or approval.', ['name' => $channel->name]) }}</p>

                        <form action="{{ route('dashboard.channels.subscribe', [$channel->organization_id, $channel->id]) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">{{ __('Request to Join') }}</button>
                        </form>
                    @endif
                    <a href="{{ route('user.feed') }}" class="btn btn-outline-secondary w-100">{{ __('Back Home') }}</a>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
