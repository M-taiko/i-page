<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Create Account') }} - i-Page</title>

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
            min-height: 100vh;
        }

        .auth-container {
            width: 100%;
            max-width: 480px;
            padding: var(--space-4);
            display: flex;
            flex-direction: column;
            justify-content: center;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-4);
        }

        .form-group {
            margin-bottom: var(--space-6);
        }

        .form-row .form-group {
            margin-bottom: 0;
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
            font-family: inherit;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px var(--primary-50);
        }

        .form-input.is-invalid {
            border-color: var(--danger-500);
        }

        .form-input.is-invalid:focus {
            box-shadow: 0 0 0 3px var(--danger-50);
        }

        .invalid-feedback {
            color: var(--danger-600);
            font-size: var(--text-xs);
            margin-top: var(--space-1);
            display: block;
        }

        .password-requirements {
            background-color: var(--info-50);
            border: 1px solid var(--info-200);
            border-radius: var(--radius-md);
            padding: var(--space-3) var(--space-4);
            margin-bottom: var(--space-6);
            font-size: var(--text-xs);
        }

        .password-requirements h4 {
            margin: 0 0 var(--space-2);
            font-size: var(--text-xs);
            font-weight: var(--font-weight-semibold);
            color: var(--info-700);
        }

        .password-requirements ul {
            margin: 0;
            padding-left: var(--space-4);
            color: var(--info-700);
        }

        .password-requirements li {
            margin-bottom: var(--space-1);
        }

        .password-requirements li:last-child {
            margin-bottom: 0;
        }

        .btn-register {
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
            margin-bottom: var(--space-4);
        }

        .btn-register:hover {
            background-color: var(--primary-700);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(91, 127, 255, 0.3);
        }

        .btn-register:active {
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

        .alert ul {
            margin: var(--space-2) 0 0;
            padding-left: var(--space-4);
        }

        .alert li {
            margin-bottom: var(--space-1);
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

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .form-row .form-group {
                margin-bottom: var(--space-6);
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
                    <i class="bi bi-person-plus"></i>
                </div>
                <h1 class="auth-title">{{ __('Create Account') }}</h1>
                <p class="auth-subtitle">{{ __('Join i-Page and connect your organization') }}</p>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>{{ __('Please fix the following errors:') }}</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Registration Form -->
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- First Name & Last Name -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name" class="form-label">{{ __('First Name') }}</label>
                        <input id="first_name"
                               type="text"
                               name="first_name"
                               value="{{ old('first_name') }}"
                               class="form-input @error('first_name') is-invalid @enderror"
                               placeholder="{{ __('Your first name') }}"
                               required
                               autofocus
                               autocomplete="given-name">
                        @error('first_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="last_name" class="form-label">{{ __('Last Name') }}</label>
                        <input id="last_name"
                               type="text"
                               name="last_name"
                               value="{{ old('last_name') }}"
                               class="form-input @error('last_name') is-invalid @enderror"
                               placeholder="{{ __('Your last name') }}"
                               required
                               autocomplete="family-name">
                        @error('last_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

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
                           autocomplete="username">
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Mobile (Optional) -->
                <div class="form-group">
                    <label for="mobile" class="form-label">{{ __('Mobile Number') }} <small style="color: var(--text-tertiary);">{{ __('(Optional)') }}</small></label>
                    <input id="mobile"
                           type="tel"
                           name="mobile"
                           value="{{ old('mobile') }}"
                           class="form-input @error('mobile') is-invalid @enderror"
                           placeholder="{{ __('(Optional) +1 555-123-4567') }}"
                           autocomplete="tel">
                    @error('mobile')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Requirements -->
                <div class="password-requirements">
                    <h4><i class="bi bi-shield-check"></i> {{ __('Password Requirements') }}</h4>
                    <ul>
                        <li>{{ __('At least 8 characters long') }}</li>
                        <li>{{ __('Mix of uppercase and lowercase letters') }}</li>
                        <li>{{ __('At least one number') }}</li>
                    </ul>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password"
                           type="password"
                           name="password"
                           class="form-input @error('password') is-invalid @enderror"
                           placeholder="{{ __('••••••••••') }}"
                           required
                           autocomplete="new-password">
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation"
                           type="password"
                           name="password_confirmation"
                           class="form-input @error('password_confirmation') is-invalid @enderror"
                           placeholder="{{ __('••••••••••') }}"
                           required
                           autocomplete="new-password">
                    @error('password_confirmation')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Register Button -->
                <button type="submit" class="btn-register">
                    <i class="bi bi-person-check"></i>
                    <span>{{ __('Create Account') }}</span>
                </button>
            </form>

            <!-- Footer -->
            <div class="auth-footer">
                <p class="auth-footer-text">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('login') }}" class="auth-footer-link">
                        {{ __('Sign in') }}
                    </a>
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
