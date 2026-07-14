<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ auth()->check() && auth()->user()->language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Login') }} - IPAGE Organization Communication Hub</title>

    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

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
            overflow: hidden;
        }

        /* Background Animation */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            animation: pulse 20s ease-in-out infinite;
            pointer-events: none;
            z-index: 1;
        }

        @keyframes pulse {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(30px, 30px); }
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 400px;
            padding: var(--space-4);
        }

        /* Login Card */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-2xl);
            padding: var(--space-12);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.5s ease-out;
        }

        /* Brand Section */
        .login-brand {
            text-align: center;
            margin-bottom: var(--space-12);
        }

        .login-logo {
            width: 48px;
            height: 48px;
            margin: 0 auto var(--space-4);
            background: linear-gradient(135deg, var(--primary-600), var(--secondary-600));
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: var(--text-2xl);
            font-weight: var(--font-weight-bold);
        }

        .login-brand h1 {
            margin: 0;
            font-size: var(--text-2xl);
            color: var(--text-primary);
        }

        .login-brand p {
            margin: var(--space-2) 0 0;
            font-size: var(--text-sm);
            color: var(--text-secondary);
        }

        /* Form Section */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: var(--space-6);
        }

        .form-group-login {
            display: flex;
            flex-direction: column;
            gap: var(--space-2);
        }

        .form-label-login {
            font-size: var(--text-sm);
            font-weight: var(--font-weight-medium);
            color: var(--text-primary);
        }

        .form-input-login {
            padding: var(--space-3) var(--space-4);
            border: 1.5px solid var(--surface-border);
            border-radius: var(--radius-lg);
            font-size: var(--text-base);
            transition: all var(--transition-fast);
            background-color: var(--surface-bg);
            color: var(--text-primary);
        }

        .form-input-login:hover {
            border-color: var(--neutral-400);
        }

        .form-input-login:focus {
            outline: none;
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px rgba(91, 127, 255, 0.1);
        }

        .form-input-login::placeholder {
            color: var(--text-tertiary);
        }

        /* Password Visibility Toggle */
        .password-toggle {
            position: relative;
        }

        .password-toggle-btn {
            position: absolute;
            right: var(--space-4);
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: var(--text-lg);
            transition: color var(--transition-fast);
            padding: var(--space-2);
        }

        .password-toggle-btn:hover {
            color: var(--text-primary);
        }

        /* Checkbox Row */
        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: var(--space-2);
            font-size: var(--text-sm);
        }

        .form-check-login {
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .form-check-login input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary-600);
        }

        .form-check-login label {
            cursor: pointer;
            margin-bottom: 0;
            color: var(--text-secondary);
        }

        .login-options a {
            color: var(--primary-600);
            font-weight: var(--font-weight-medium);
            text-decoration: none;
            transition: color var(--transition-fast);
        }

        .login-options a:hover {
            color: var(--primary-700);
        }

        /* Submit Button */
        .btn-login {
            padding: var(--space-3) var(--space-4);
            background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            font-size: var(--text-base);
            font-weight: var(--font-weight-medium);
            cursor: pointer;
            transition: all var(--transition-fast);
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-login:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            right: var(--space-4);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        /* Divider */
        .login-divider {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            color: var(--text-tertiary);
            font-size: var(--text-sm);
        }

        .login-divider::before,
        .login-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: var(--surface-border);
        }

        /* Error Messages */
        .error-message {
            padding: var(--space-3) var(--space-4);
            background-color: var(--danger-50);
            border: 1px solid var(--danger-200);
            border-radius: var(--radius-lg);
            color: var(--danger-700);
            font-size: var(--text-sm);
            display: flex;
            gap: var(--space-3);
            align-items: flex-start;
        }

        /* Footer */
        .login-footer {
            margin-top: var(--space-12);
            text-align: center;
            font-size: var(--text-sm);
            color: var(--text-secondary);
        }

        .login-footer a {
            color: var(--primary-600);
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        /* Register Link */
        .register-link {
            margin-top: var(--space-6);
            padding-top: var(--space-6);
            border-top: 1px solid var(--surface-border);
            text-align: center;
            color: var(--text-secondary);
            font-size: var(--text-sm);
        }

        .register-link a {
            color: var(--primary-600);
            font-weight: var(--font-weight-medium);
        }

        /* Responsive */
        @media (max-width: 640px) {
            .login-container {
                max-width: 100%;
                padding: var(--space-6);
            }

            .login-card {
                padding: var(--space-8);
                border-radius: var(--radius-2xl);
            }

            .login-brand {
                margin-bottom: var(--space-8);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Brand Section -->
            <div class="login-brand">
                <div class="login-logo">
                    <i class="bi bi-globe"></i>
                </div>
                <h1>{{ __('IPAGE') }}</h1>
                <p>{{ __('Organization Communication Hub') }}</p>
            </div>

            <!-- Session Status -->
            @if ($errors->any())
                <div class="error-message">
                    <i class="bi bi-exclamation-circle-fill" style="font-size: var(--text-lg); flex-shrink: 0;"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (session('status'))
                <div style="padding: var(--space-3) var(--space-4); background-color: var(--success-50); border: 1px solid var(--success-200); border-radius: var(--radius-lg); color: var(--success-700); font-size: var(--text-sm); display: flex; gap: var(--space-3); align-items: center; margin-bottom: var(--space-6);">
                    <i class="bi bi-check-circle-fill" style="font-size: var(--text-lg);"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="login-form" id="loginForm">
                @csrf

                <!-- Email Address -->
                <div class="form-group-login">
                    <label for="email" class="form-label-login">{{ __('Email Address') }}</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="form-input-login @error('email') border-danger-500 @enderror"
                        placeholder="{{ __('Enter your email') }}"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                    />
                </div>

                <!-- Password -->
                <div class="form-group-login">
                    <label for="password" class="form-label-login">{{ __('Password') }}</label>
                    <div class="password-toggle">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-input-login @error('password') border-danger-500 @enderror"
                            placeholder="{{ __('Enter your password') }}"
                            required
                            autocomplete="current-password"
                            style="padding-right: var(--space-12);"
                        />
                        <button
                            type="button"
                            class="password-toggle-btn"
                            onclick="togglePasswordVisibility()"
                            tabindex="-1"
                        >
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Options Row -->
                <div class="login-options">
                    <label class="form-check-login">
                        <input type="checkbox" name="remember" id="remember_me" />
                        <span>{{ __('Remember me') }}</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
                    @endif
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="btn-login"
                    id="submitBtn"
                >
                    <span id="submitText">{{ __('Sign in') }}</span>
                </button>
            </form>

            <!-- Register Link -->
            @if (Route::has('register'))
                <div class="register-link">
                    {{ __("Don't have an account?") }}
                    <a href="{{ route('register') }}">{{ __('Create account') }}</a>
                </div>
            @endif
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Loading state
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            const text = document.getElementById('submitText');
            btn.disabled = true;
            btn.classList.add('loading');
            text.textContent = '{{ __("Signing in...") }}';
        });
    </script>
</body>
</html>
