<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.seo', ['seo' => ['no_title' => true, 'title' => __('Create your free i-page account'), 'description' => __('Join i-page to follow hotels, stores, schools and more — and get their news, offers and events in one feed.'), 'url' => url()->current(), 'robots' => 'index,follow,max-image-preview:large', 'locale' => app()->getLocale() === 'ar' ? 'ar_AR' : 'en_US']])
    <title>{{ __('Create Account') }} - i-Page</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/logo-mark.svg') }}">
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');</script>

    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/illustrations.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            font-family: var(--font-sans); color: var(--text-primary);
            background: radial-gradient(90% 70% at 0% 0%, rgba(109, 93, 252, .16), transparent 60%),
                        radial-gradient(80% 60% at 100% 100%, rgba(236, 72, 153, .10), transparent 60%),
                        var(--surface-bg-secondary);
        }
        .rg { min-height: 100vh; min-height: 100dvh; display: grid; grid-template-columns: 1fr; }

        /* Brand panel (large screens) */
        .rg-brand { display: none; position: relative; overflow: hidden; padding: 48px; color: #fff;
            background: radial-gradient(120% 120% at 0% 0%, #7c6cff 0%, #4557f5 45%, #312e81 100%); }
        .rg-brand::before, .rg-brand::after { content: ''; position: absolute; border-radius: 50%; background: rgba(255,255,255,.07); }
        .rg-brand::before { width: 420px; height: 420px; inset: auto -140px -160px auto; }
        .rg-brand::after { width: 220px; height: 220px; inset: -80px auto auto -60px; }
        .rg-brand-inner { position: relative; z-index: 1; height: 100%; display: flex; flex-direction: column; gap: 28px; max-width: 460px; }
        .rg-brand .brand-logo { --bl-ink-1: #fff; --bl-ink-2: #e0e7ff; }
        .rg-brand h2 { margin: 0; font-size: 2rem; line-height: 1.2; font-weight: 800; letter-spacing: -.02em; color: #fff; }
        .rg-brand h2 span { color: #fde68a; }
        .rg-brand p { margin: 0; font-size: 15px; line-height: 1.7; color: rgba(255,255,255,.88); }
        .rg-points { list-style: none; margin: 0; padding: 0; display: grid; gap: 14px; }
        .rg-points li { display: flex; gap: 12px; align-items: flex-start; font-size: 14px; line-height: 1.5; color: rgba(255,255,255,.92); }
        .rg-points i { flex: 0 0 auto; width: 36px; height: 36px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,.14); font-size: 17px; color: #fde68a; }
        .rg-points b { display: block; color: #fff; }
        .rg-brand .ill { margin-top: auto; align-self: flex-start; --ill-paper: #fff; --ill-ink: #1e293b; --ill-soft: rgba(255,255,255,.14); --ill-softer: rgba(0,0,0,.15); --ill-primary: #c7d2fe; --ill-line: #e0e7ff; }

        /* Form side */
        .rg-main { display: flex; align-items: center; justify-content: center; padding: 28px 16px 40px; }
        .rg-card { width: 100%; max-width: 460px; padding: 28px 22px; border-radius: 28px; background: var(--surface-bg); border: 1px solid var(--surface-border);
            box-shadow: 0 30px 60px -36px rgba(15, 23, 42, .45); animation: rgIn .5s cubic-bezier(.2, .8, .2, 1); }
        @keyframes rgIn { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        .rg-head { text-align: center; margin-bottom: 22px; }
        .rg-head .brand-logo { margin-bottom: 14px; }
        .rg-head h1 { margin: 0 0 6px; font-size: 1.45rem; font-weight: 800; letter-spacing: -.01em; }
        .rg-head p { margin: 0; font-size: 14px; color: var(--text-secondary); }

        .rg-alert { display: flex; gap: 10px; padding: 12px 14px; margin-bottom: 16px; border-radius: 14px; background: rgba(239, 68, 68, .1); color: #b91c1c; font-size: 13px; }
        [data-theme="dark"] .rg-alert { color: #fca5a5; }
        .rg-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .rg-field { margin-bottom: 14px; }
        .rg-label { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: var(--text-primary); }
        .rg-label small { font-weight: 500; color: var(--text-tertiary); }
        .rg-input { position: relative; }
        .rg-input > i { position: absolute; top: 50%; inset-inline-start: 14px; transform: translateY(-50%); color: var(--text-tertiary); font-size: 16px; pointer-events: none; transition: color .15s; }
        .rg-input input {
            width: 100%; padding: 13px 14px; padding-inline-start: 42px; border-radius: 14px;
            border: 1.5px solid var(--surface-border); background: var(--surface-bg-secondary); color: var(--text-primary);
            font: inherit; font-size: 15px; transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .rg-input input:focus { outline: 0; border-color: #6d5dfc; background: var(--surface-bg); box-shadow: 0 0 0 4px rgba(109, 93, 252, .15); }
        .rg-input:focus-within > i { color: #6d5dfc; }
        .rg-input input.is-invalid { border-color: #ef4444; }
        .rg-input .rg-eye { position: absolute; top: 50%; inset-inline-end: 8px; transform: translateY(-50%); width: 36px; height: 36px; border: 0; border-radius: 10px; background: none; color: var(--text-tertiary); cursor: pointer; font-size: 17px; }
        .rg-input .rg-eye:hover { background: var(--surface-hover); color: var(--text-primary); }
        .rg-input.has-eye input { padding-inline-end: 48px; }
        .rg-error { margin-top: 6px; font-size: 12px; color: #dc2626; }

        .rg-strength { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; margin-top: 8px; }
        .rg-strength span { height: 4px; border-radius: 4px; background: var(--surface-hover); transition: background .25s; }
        .rg-checks { list-style: none; margin: 8px 0 0; padding: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 4px 10px; }
        .rg-checks li { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-tertiary); transition: color .2s; }
        .rg-checks li i { font-size: 13px; }
        .rg-checks li.ok { color: #059669; }
        .rg-match { margin-top: 6px; font-size: 12px; display: none; }
        .rg-match.show { display: flex; align-items: center; gap: 6px; }

        .rg-submit { width: 100%; margin-top: 6px; padding: 14px; border: 0; border-radius: 14px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: linear-gradient(135deg, #7c6cff, #4557f5 60%, #4338ca); color: #fff; font: inherit; font-size: 15px; font-weight: 800;
            box-shadow: 0 14px 28px -14px rgba(69, 87, 245, .8); transition: transform .15s, box-shadow .15s, opacity .15s; }
        .rg-submit:hover { transform: translateY(-1px); box-shadow: 0 18px 32px -14px rgba(69, 87, 245, .85); }
        .rg-submit:disabled { opacity: .7; cursor: progress; transform: none; }
        .rg-terms { margin: 12px 0 0; text-align: center; font-size: 12px; color: var(--text-tertiary); line-height: 1.6; }
        .rg-alt { display: flex; align-items: center; gap: 12px; margin: 20px 0 14px; color: var(--text-tertiary); font-size: 12px; }
        .rg-alt::before, .rg-alt::after { content: ''; flex: 1; height: 1px; background: var(--surface-border); }
        .rg-links { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .rg-link { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 11px; border-radius: 14px; border: 1px solid var(--surface-border); color: var(--text-primary); font-size: 13.5px; font-weight: 700; text-decoration: none; transition: border-color .15s, background .15s; }
        .rg-link:hover { border-color: #6d5dfc; background: rgba(109, 93, 252, .06); }
        .rg-link.primary { color: #4338ca; }
        [data-theme="dark"] .rg-link.primary { color: #c7d2fe; }

        @media (min-width: 1024px) {
            .rg { grid-template-columns: minmax(420px, 46%) 1fr; }
            .rg-brand { display: block; }
            .rg-card { padding: 36px 34px; }
            .rg-head .brand-logo { display: none; }
        }
        @media (max-width: 420px) {
            .rg-row { grid-template-columns: 1fr; gap: 0; }
            .rg-card { padding: 24px 16px; border-radius: 24px; }
        }
        @media (prefers-reduced-motion: reduce) { .rg-card { animation: none; } }
    </style>
</head>
<body>
<div class="rg">
    <aside class="rg-brand" aria-hidden="true">
        <div class="rg-brand-inner">
            <x-brand-logo :height="46" />
            <h2>{{ __('The digital front door of') }} <span>{{ __('every place you love') }}</span></h2>
            <p>{{ __('Hotels, stores, schools and more share their news, offers and updates on i-Page. One account follows them all.') }}</p>
            <ul class="rg-points">
                <li><i class="bi bi-qr-code-scan"></i><span><b>{{ __('Scan & follow') }}</b>{{ __('Scan the QR code at any place to join its channels.') }}</span></li>
                <li><i class="bi bi-bell"></i><span><b>{{ __('Never miss a thing') }}</b>{{ __('Offers, events and important alerts arrive as they happen.') }}</span></li>
                <li><i class="bi bi-chat-heart"></i><span><b>{{ __('Talk back') }}</b>{{ __('React, comment and vote in polls from your phone.') }}</span></li>
            </ul>
            <x-illustration name="channels" size="190" />
        </div>
    </aside>

    <main class="rg-main">
        <div class="rg-card">
            <div class="rg-head">
                <x-brand-logo :height="40" />
                <h1>{{ __('Create your account') }}</h1>
                <p>{{ __('It\'s free and takes less than a minute.') }}</p>
            </div>

            @if ($errors->any())
                <div class="rg-alert" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <div>{{ __('Please check the highlighted fields.') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="rgForm" novalidate>
                @csrf
                <div class="rg-row">
                    <div class="rg-field">
                        <label class="rg-label" for="first_name">{{ __('First name') }}</label>
                        <div class="rg-input"><i class="bi bi-person"></i>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required autocomplete="given-name" autofocus
                                   class="@error('first_name') is-invalid @enderror" placeholder="{{ __('Your first name') }}">
                        </div>
                        @error('first_name')<div class="rg-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="rg-field">
                        <label class="rg-label" for="last_name">{{ __('Last name') }}</label>
                        <div class="rg-input"><i class="bi bi-person"></i>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required autocomplete="family-name"
                                   class="@error('last_name') is-invalid @enderror" placeholder="{{ __('Your last name') }}">
                        </div>
                        @error('last_name')<div class="rg-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="rg-field">
                    <label class="rg-label" for="email">{{ __('Email') }}</label>
                    <div class="rg-input"><i class="bi bi-envelope"></i>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" inputmode="email"
                               class="@error('email') is-invalid @enderror" placeholder="you@example.com">
                    </div>
                    @error('email')<div class="rg-error">{{ $message }}</div>@enderror
                </div>

                <div class="rg-field">
                    <label class="rg-label" for="mobile">{{ __('Mobile') }} <small>{{ __('Optional') }}</small></label>
                    <div class="rg-input"><i class="bi bi-phone"></i>
                        <input id="mobile" name="mobile" type="tel" value="{{ old('mobile') }}" autocomplete="tel" inputmode="tel" dir="ltr"
                               class="@error('mobile') is-invalid @enderror" placeholder="+20 100 000 0000">
                    </div>
                    @error('mobile')<div class="rg-error">{{ $message }}</div>@enderror
                </div>

                <div class="rg-field">
                    <label class="rg-label" for="password">{{ __('Password') }}</label>
                    <div class="rg-input has-eye"><i class="bi bi-lock"></i>
                        <input id="password" name="password" type="password" required autocomplete="new-password" minlength="8"
                               class="@error('password') is-invalid @enderror" placeholder="{{ __('At least 8 characters') }}" aria-describedby="pwChecks">
                        <button type="button" class="rg-eye" data-eye="password" aria-label="{{ __('Show password') }}"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="rg-strength" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
                    <ul class="rg-checks" id="pwChecks">
                        <li data-rule="len"><i class="bi bi-circle"></i> {{ __('8+ characters') }}</li>
                        <li data-rule="case"><i class="bi bi-circle"></i> {{ __('Upper & lower case') }}</li>
                        <li data-rule="num"><i class="bi bi-circle"></i> {{ __('A number') }}</li>
                        <li data-rule="sym"><i class="bi bi-circle"></i> {{ __('A symbol') }}</li>
                    </ul>
                    @error('password')<div class="rg-error">{{ $message }}</div>@enderror
                </div>

                <div class="rg-field">
                    <label class="rg-label" for="password_confirmation">{{ __('Confirm password') }}</label>
                    <div class="rg-input has-eye"><i class="bi bi-shield-lock"></i>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="{{ __('Repeat your password') }}">
                        <button type="button" class="rg-eye" data-eye="password_confirmation" aria-label="{{ __('Show password') }}"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="rg-match" id="pwMatch" aria-live="polite"></div>
                </div>

                <button type="submit" class="rg-submit" id="rgSubmit"><i class="bi bi-person-check"></i> <span>{{ __('Create account') }}</span></button>
                <p class="rg-terms">{{ __('By creating an account you agree to receive updates from the channels you join. You can mute them anytime.') }}</p>
            </form>

            <div class="rg-alt">{{ __('or') }}</div>
            <div class="rg-links">
                <a href="{{ route('login') }}" class="rg-link primary"><i class="bi bi-box-arrow-in-right"></i> {{ __('Sign in') }}</a>
                <a href="{{ route('guest.home') }}" class="rg-link"><i class="bi bi-compass"></i> {{ __('Browse as guest') }}</a>
            </div>
        </div>
    </main>
</div>

<script>
    (function () {
        const pw = document.getElementById('password');
        const confirm = document.getElementById('password_confirmation');
        const bars = document.querySelectorAll('.rg-strength span');
        const match = document.getElementById('pwMatch');
        const colors = ['#ef4444', '#f59e0b', '#22c55e', '#059669'];
        const rules = {
            len: v => v.length >= 8,
            case: v => /[a-z]/.test(v) && /[A-Z]/.test(v),
            num: v => /\d/.test(v),
            sym: v => /[^A-Za-z0-9]/.test(v),
        };

        function update() {
            const v = pw.value;
            let score = 0;
            document.querySelectorAll('.rg-checks li').forEach(li => {
                const ok = rules[li.dataset.rule](v);
                li.classList.toggle('ok', ok);
                li.querySelector('i').className = ok ? 'bi bi-check-circle-fill' : 'bi bi-circle';
                if (ok) score++;
            });
            bars.forEach((b, i) => { b.style.background = v && i < score ? colors[score - 1] : ''; });
            if (confirm.value) {
                const same = confirm.value === v;
                match.className = 'rg-match show';
                match.style.color = same ? '#059669' : '#dc2626';
                match.innerHTML = same
                    ? '<i class="bi bi-check-circle-fill"></i> {{ __('Passwords match') }}'
                    : '<i class="bi bi-x-circle-fill"></i> {{ __('Passwords don\'t match yet') }}';
            } else {
                match.className = 'rg-match';
            }
        }
        pw.addEventListener('input', update);
        confirm.addEventListener('input', update);

        document.querySelectorAll('[data-eye]').forEach(btn => btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.eye);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            btn.setAttribute('aria-label', show ? '{{ __('Hide password') }}' : '{{ __('Show password') }}');
        }));

        document.getElementById('rgForm').addEventListener('submit', () => {
            const btn = document.getElementById('rgSubmit');
            btn.disabled = true;
            btn.querySelector('span').textContent = '{{ __('Creating your account…') }}';
        });
    })();
</script>
</body>
</html>
