<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Select Organization') }} - i-Page</title>
    <style>
        :root {
            --primary-600: #2563eb;
            --primary-50: #eff6ff;
            --surface-bg: #ffffff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, var(--primary-50), #f9fafb);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text-primary);
            min-height: 100vh;
            padding: 2rem 1rem;
        }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 3rem; }
        .header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .header p { color: var(--text-secondary); font-size: 1.05rem; }
        .organizations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .org-card {
            background: var(--surface-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .org-card:hover {
            border-color: var(--primary-600);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.15);
            transform: translateY(-4px);
        }
        .org-icon {
            width: 50px; height: 50px; border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-600), #1d4ed8);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.5rem; font-weight: 700;
        }
        .org-name { font-size: 1.1rem; font-weight: 600; margin: 1rem 0 0.5rem; }
        .org-description { font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5; }
        .role-badge {
            display: inline-block;
            background: var(--primary-50);
            color: var(--primary-600);
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        .user-section {
            background: var(--surface-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: center;
        }
        .user-section p { color: var(--text-secondary); margin-bottom: 1rem; }
        .user-email { font-weight: 600; color: var(--text-primary); }
        .logout-btn {
            background: #ef4444; color: white; border: none;
            padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.9rem;
            cursor: pointer; text-decoration: none; display: inline-block;
            transition: background 0.2s; font-weight: 600;
        }
        .logout-btn:hover { background: #dc2626; }
        @media (prefers-color-scheme: dark) {
            :root {
                --surface-bg: #1f2937;
                --text-primary: #f3f4f6;
                --text-secondary: #d1d5db;
                --border-color: #374151;
                --primary-50: #0f172a;
            }
            body { background: linear-gradient(135deg, #111827, #1f2937); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ __('Select Organization') }}</h1>
            <p>{{ __('Choose an organization to continue') }}</p>
        </div>

        @if ($organizations->count() > 0)
            <div class="organizations-grid">
                @foreach ($organizations as $organization)
                    <a href="{{ route('dashboard.home', $organization->id) }}" class="org-card">
                        <div class="org-icon">{{ strtoupper(substr($organization->name, 0, 1)) }}</div>
                        <div class="org-name">{{ $organization->name }}</div>
                        <span class="role-badge">{{ $organization->pivot->role ?? 'member' }}</span>
                        <div class="org-description">{{ $organization->description ?? $organization->city . ', ' . $organization->country }}</div>
                    </a>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 3rem 1rem; background: var(--surface-bg); border-radius: 12px;">
                <p style="color: var(--text-secondary); font-size: 1rem; margin-bottom: 1rem;">
                    {{ __('You do not have access to any organizations.') }}
                </p>
            </div>
        @endif

        <div class="user-section">
            <p>{{ __('Logged in as:') }} <span class="user-email">{{ auth()->user()->email }}</span></p>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">{{ __('Logout') }}</button>
            </form>
        </div>
    </div>
</body>
</html>
