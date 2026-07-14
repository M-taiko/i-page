<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Sign In') }} - i-Page</title>

    <!-- Design System & Components -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            padding: var(--space-4);
        }

        .auth-card {
            background-color: var(--surface-bg);
            border-radius: var(--radius-2xl);
            padding: var(--space-8);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .auth-header {
            text-align: center;
            margin-bottom: var(--space-8);
        }

        .auth-logo {
            width: 60px;
            height: 60px;
            margin: 0 auto var(--space-4);
            background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: var(--text-3xl);
            font-weight: var(--font-weight-bold);
        }

        .auth-title {
            font-size: var(--text-2xl);
            font-weight: var(--font-weight-bold);
            color: var(--text-primary);
            margin: 0 0 var(--space-2);
        }

        .auth-subtitle {
            color: var(--text-secondary);
            font-size: var(--text-sm);
            margin: 0;
        }

        .form-group {
            margin-bottom: var(--space-6);
        }

        .form-label {
            display: block;
            margin-bottom: var(--space-2);
            font-weight: var(--font-weight-semibold);
            color: var(--text-primary);
            font-size: var(--text-sm);
        }

        .form-input {
            width: 100%;
            padding: var(--space-2) var(--space-3);
            border: 1px solid var(--surface-border);
            border-radius: var(--radius-md);
            font-size: var(--text-sm);
            background-color: var(--surface-bg);
            color: var(--text-primary);
            transition: all var(--transition-fast);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px var(--primary-50);
        }

        .form-input.is-invalid {
            border-color: var(--danger-500);
        }

        .invalid-feedback {
            color: var(--danger-600);
            font-size: var(--text-xs);
            margin-top: var(--space-1);
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .form-checkbox input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary-600);
        }

        .form-checkbox label {
            margin: 0;
            font-size: var(--text-sm);
            color: var(--text-secondary);
            cursor: pointer;
        }

        .form-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-6);
        }

        .forgot-link {
            font-size: var(--text-sm);
            color: var(--primary-600);
            text-decoration: none;
            transition: color var(--transition-fast);
        }

        .forgot-link:hover {
            color: var(--primary-700);
        }

        .btn-login {
            width: 100%;
            padding: var(--space-2) var(--space-4);
            background-color: var(--primary-600);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: var(--text-sm);
            font-weight: var(--font-weight-semibold);
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
        }

        .btn-login:hover {
            background-color: var(--primary-700);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(91, 127, 255, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .auth-footer {
            text-align: center;
            padding-top: var(--space-6);
            border-top: 1px solid var(--surface-border);
        }

        .auth-footer-text {
            color: var(--text-secondary);
            font-size: var(--text-sm);
            margin: 0;
        }

        .auth-footer-link {
            color: var(--primary-600);
            text-decoration: none;
            font-weight: var(--font-weight-semibold);
        }

        .auth-footer-link:hover {
            color: var(--primary-700);
        }

        .alert {
            padding: var(--space-3) var(--space-4);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-4);
            font-size: var(--text-sm);
        }

        .alert-danger {
            background-color: var(--danger-50);
            border: 1px solid var(--danger-200);
            color: var(--danger-700);
        }

        @media (max-width: 640px) {
            .auth-card {
                padding: var(--space-6);
            }

            .auth-logo {
                width: 50px;
                height: 50px;
                font-size: var(--text-2xl);
            }

            .auth-title {
                font-size: var(--text-xl);
            }
        }

        [data-theme="dark"] {
            --surface-bg: #1f2937;
        }

        [data-theme="dark"] body {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="bi bi-door-open"></i>
                </div>
                <h1 class="auth-title">{{ __('i-Page') }}</h1>
                <p class="auth-subtitle">{{ __('The Digital Front Door for Every Organization') }}</p>
            </div>

            <!-- Session Status -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>{{ __('Oops! Something went wrong.') }}</strong>
                    <ul style="margin: var(--space-2) 0 0; padding-left: var(--space-4);">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div class="form-group">
                    <label for="email" class="form-label">{{ __('Email Address') }}</label>
                    <input id="email"
                           type="email"
                           name="email"
                           value="{{ old('email') }}"
                           class="form-input @error('email') is-invalid @enderror"
                           placeholder="{{ __('you@example.com') }}"
                           required
                           autofocus
                           autocomplete="username">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password"
                           type="password"
                           name="password"
                           class="form-input @error('password') is-invalid @enderror"
                           placeholder="{{ __('••••••••') }}"
                           required
                           autocomplete="current-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="form-footer">
                    <label class="form-checkbox">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember" style="cursor: pointer; margin: 0;">
                            {{ __('Remember me') }}
                        </label>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>{{ __('Sign In') }}</span>
                </button>
            </form>

            <!-- Browse as Guest Option -->
            <div style="margin-top: var(--space-4); padding-top: var(--space-4); border-top: 1px solid var(--surface-border); text-align: center;">
                <p style="color: var(--text-secondary); font-size: var(--text-sm); margin-bottom: var(--space-2);">
                    {{ __('Want to browse posts without signing in?') }}
                </p>
                <a href="{{ route('guest.home') }}" style="display: inline-flex; align-items: center; gap: var(--space-2); padding: var(--space-2) var(--space-4); background-color: var(--neutral-100); color: var(--primary-600); border: none; border-radius: var(--radius-md); text-decoration: none; font-weight: var(--font-weight-semibold); font-size: var(--text-sm); transition: all var(--transition-fast);" onmouseover="this.style.backgroundColor='var(--primary-50)'" onmouseout="this.style.backgroundColor='var(--neutral-100)'">
                    <i class="bi bi-eye"></i>
                    <span>{{ __('Browse as Guest') }}</span>
                </a>
            </div>

            <!-- Footer -->
            <div class="auth-footer">
                <p class="auth-footer-text">
                    {{ __('Don\'t have an account?') }}
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="auth-footer-link">
                            {{ __('Sign up') }}
                        </a>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <script>
        // Theme initialization
        const storedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', storedTheme);
    </script>
</body>
</html>
