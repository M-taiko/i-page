<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Guest Registration') }} - {{ $organization->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-600: #2563eb;
            --primary-50: #eff6ff;
            --surface-bg: #ffffff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .container-fluid {
            max-width: 500px;
            width: 100%;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-600), #1d4ed8);
            color: white;
            padding: 2rem;
            text-align: center;
            border: none;
        }

        .card-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .card-header p {
            opacity: 0.9;
            font-size: 0.95rem;
            margin: 0;
        }

        .card-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
            color: var(--text-primary);
        }

        .form-label .required {
            color: #ef4444;
            margin-left: 0.25rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: var(--surface-bg);
            color: var(--text-primary);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-600);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-input::placeholder {
            color: var(--text-secondary);
        }

        .form-input.is-invalid {
            border-color: #ef4444;
        }

        .invalid-feedback {
            display: block;
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }

        .btn-primary {
            background: var(--primary-600);
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        .alert {
            border: none;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .channel-info {
            background: var(--primary-50);
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .channel-info strong {
            color: var(--primary-600);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --surface-bg: #1f2937;
                --text-primary: #f3f4f6;
                --text-secondary: #d1d5db;
                --border-color: #374151;
                --primary-50: #0f172a;
            }

            body {
                background: linear-gradient(135deg, #111827, #1f2937);
            }

            .card {
                background: #1f2937;
                color: var(--text-primary);
            }

            .channel-info {
                background: #111827;
                border-color: #1e40af;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h1>{{ __('Join as Guest') }}</h1>
                <p>{{ $organization->name }} - {{ $channel->name }}</p>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>{{ __('Error') }}</strong>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="channel-info">
                    <strong>{{ $channel->name }}</strong><br>
                    {{ $channel->description ?? __('Join this channel to access exclusive content and updates.') }}
                </div>

                <form method="POST" action="{{ route('qr.guest-register.store') }}">
                    @csrf

                    <input type="hidden" name="channel_id" value="{{ $channel->id }}">
                    <input type="hidden" name="organization_id" value="{{ $organization->id }}">

                    <div class="form-group">
                        <label class="form-label">
                            {{ __('First Name') }}
                            <span class="required">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-input @error('first_name') is-invalid @enderror"
                            name="first_name"
                            value="{{ old('first_name') }}"
                            placeholder="{{ __('Your first name') }}"
                            required
                            autofocus
                        >
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            {{ __('Last Name') }}
                            <span class="required">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-input @error('last_name') is-invalid @enderror"
                            name="last_name"
                            value="{{ old('last_name') }}"
                            placeholder="{{ __('Your last name') }}"
                            required
                        >
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            {{ __('Email') }}
                            <span style="color: #999; font-size: 0.85rem; font-weight: normal;">({{ __('optional') }})</span>
                        </label>
                        <input
                            type="email"
                            class="form-input @error('email') is-invalid @enderror"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="your@email.com"
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            {{ __('Phone Number') }}
                            <span style="color: #999; font-size: 0.85rem; font-weight: normal;">({{ __('optional') }})</span>
                        </label>
                        <input
                            type="tel"
                            class="form-input @error('mobile') is-invalid @enderror"
                            name="mobile"
                            value="{{ old('mobile') }}"
                            placeholder="+20 100 000 0000"
                        >
                        @error('mobile')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        {{ __('Join Channel') }}
                    </button>
                </form>

                <div class="text-center" style="margin-top: 1.5rem;">
                    <p class="text-muted">
                        {{ __('Already have an account?') }}
                        <a href="{{ route('login') }}" style="color: var(--primary-600); text-decoration: none; font-weight: 600;">
                            {{ __('Sign in here') }}
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
