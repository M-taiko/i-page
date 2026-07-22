<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Verify Your Code') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-600: #2563eb;
            --primary-50: #eff6ff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
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
            max-width: 420px;
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
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .card-body {
            padding: 2rem;
        }

        .code-input {
            font-size: 1.75rem;
            letter-spacing: 0.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h1>{{ __('Verify Your Code') }}</h1>
                <p class="mb-0">{{ __('We sent a 6-digit code to') }} {{ $destination }}</p>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('otp.verify.store') }}">
                    @csrf
                    <div class="mb-3">
                        <input type="text" name="code" class="form-control code-input" maxlength="6"
                               inputmode="numeric" autocomplete="one-time-code" autofocus required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">{{ __('Verify') }}</button>
                </form>

                <form method="POST" action="{{ route('otp.resend') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-link w-100 text-decoration-none">
                        {{ __('Resend code') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
