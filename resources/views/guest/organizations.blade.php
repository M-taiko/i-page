@extends('layouts.mobile-shell')

@section('title', ($search !== '' ? $search . ' · ' : '') . __('Search') . ' - i-Page')

@section('app-bar')
    <a href="{{ route('guest.home') }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}">
        <i class="bi bi-arrow-left rtl-flip"></i>
    </a>
    <div class="app-bar-title">{{ __('Search') }}</div>
@endsection

@section('extra-styles')
    .sp { padding-bottom: 96px; }

    /* Hero with the search box */
    .sp-hero { position: relative; overflow: hidden; margin: 12px 12px 0; padding: 18px 16px 16px; border-radius: 24px; color: #fff;
        background: radial-gradient(120% 140% at 100% 0%, #c084fc 0%, #6d5dfc 40%, #4338ca 100%); box-shadow: 0 18px 40px -20px rgba(67, 56, 202, .7); }
    .sp-hero::before { content: ''; position: absolute; width: 220px; height: 220px; border-radius: 50%; background: rgba(255,255,255,.08); inset: -90px auto auto -60px; }
    .sp-hero-top { position: relative; display: flex; align-items: center; gap: 10px; }
    .sp-hero h1 { flex: 1; margin: 0; font-size: 1.3rem; line-height: 1.25; font-weight: 800; color: #fff; }
    .sp-hero h1 small { display: block; margin-top: 4px; font-size: 12.5px; font-weight: 500; color: rgba(255,255,255,.85); }
    .sp-hero .ill { flex: 0 0 auto; width: 92px; height: auto; --ill-paper: #fff; --ill-ink: #1e293b; --ill-soft: rgba(255,255,255,.16); --ill-softer: rgba(0,0,0,.12); --ill-primary: #fff; --ill-line: #e0e7ff; }
    .sp-form { position: relative; margin-top: 14px; }
    .sp-box { display: flex; align-items: center; gap: 8px; padding: 6px 6px 6px 14px; border-radius: 16px; background: #fff; box-shadow: 0 10px 24px -12px rgba(15, 23, 42, .5); }
    [dir="rtl"] .sp-box { padding: 6px 14px 6px 6px; }
    .sp-box > i { color: #6d5dfc; font-size: 18px; }
    .sp-box input { flex: 1; min-width: 0; border: 0; outline: 0; background: none; font: inherit; font-size: 15px; color: #0f172a; padding: 8px 0; }
    .sp-box input::placeholder { color: #94a3b8; }
    .sp-clear[hidden] { display: none; }
    .sp-clear { border: 0; background: #f1f5f9; color: #64748b; width: 30px; height: 30px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .sp-go { border: 0; border-radius: 12px; padding: 10px 14px; background: linear-gradient(135deg, #6d5dfc, #4338ca); color: #fff; font-weight: 700; font-size: 13.5px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
    .sp-spin { width: 16px; height: 16px; border-radius: 50%; border: 2px solid #c7d2fe; border-top-color: #6d5dfc; animation: spSpin .7s linear infinite; display: none; }
    .sp-form.is-loading .sp-spin { display: block; }
    @keyframes spSpin { to { transform: rotate(360deg); } }

    /* Filter chips */
    .sp-chips { display: flex; gap: 8px; overflow-x: auto; padding: 14px 12px 4px; scrollbar-width: none; }
    .sp-chips::-webkit-scrollbar { display: none; }
    .sp-chip { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 999px; border: 1px solid var(--surface-border); background: var(--surface-bg); color: var(--text-secondary); font-size: 13px; font-weight: 700; text-decoration: none; transition: all .15s; }
    .sp-chip:hover { border-color: var(--primary-500); color: var(--primary-600); }
    .sp-chip.active { background: var(--primary-600); border-color: var(--primary-600); color: #fff; box-shadow: 0 6px 14px -6px rgba(69, 87, 245, .7); }

    /* Results */
    .sr { transition: opacity .2s; }
    .sr.is-loading { opacity: .45; }
    .sr-sec { margin: 16px 12px 0; }
    .sr-title { display: flex; align-items: center; gap: 8px; margin: 0 4px 10px; font-size: 15px; font-weight: 800; color: var(--text-primary); }
    .sr-title i { color: var(--primary-600); }
    .sr-title span { margin-inline-start: auto; padding: 2px 9px; border-radius: 999px; background: var(--surface-hover); font-size: 12px; color: var(--text-secondary); }
    .sr mark { background: #fde68a; color: inherit; border-radius: 4px; padding: 0 2px; }
    [data-theme="dark"] .sr mark { background: #a16207; }

    .sr-channels { display: grid; gap: 8px; }
    .sr-ch { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 16px; background: var(--surface-bg); border: 1px solid var(--surface-border); text-decoration: none; color: var(--text-tertiary); transition: transform .15s, box-shadow .15s; }
    .sr-ch:hover { transform: translateY(-1px); box-shadow: 0 10px 22px -16px rgba(15, 23, 42, .45); }
    .sr-ch-av { flex: 0 0 auto; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; background: var(--av); }
    .sr-ch-text { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .sr-ch-name { font-size: 14px; font-weight: 700; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sr-ch-sub { font-size: 12px; color: var(--text-tertiary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .sr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr)); gap: 12px; }
    .sr-org { display: flex; flex-direction: column; border-radius: 20px; overflow: hidden; background: var(--surface-bg); border: 1px solid var(--surface-border); text-decoration: none; transition: transform .2s, box-shadow .2s; }
    .sr-org:hover { transform: translateY(-3px); box-shadow: 0 18px 34px -22px rgba(15, 23, 42, .55); }
    .sr-org-cover { position: relative; height: 62px; background: linear-gradient(135deg, var(--av), color-mix(in srgb, var(--av) 45%, #c084fc)); }
    .sr-org-cover::after { content: ''; position: absolute; inset: 0; background: radial-gradient(circle at 85% 20%, rgba(255,255,255,.35), transparent 45%); }
    .sr-org-av { position: absolute; left: 14px; bottom: -22px; z-index: 1; width: 52px; height: 52px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 800; color: var(--av); background: var(--surface-bg); box-shadow: 0 6px 16px -6px rgba(15, 23, 42, .35); }
    [dir="rtl"] .sr-org-av { left: auto; right: 14px; }
    .sr-org-body { display: flex; flex-direction: column; gap: 4px; padding: 30px 14px 14px; }
    .sr-org-name { font-size: 15.5px; font-weight: 800; color: var(--text-primary); }
    .sr-org-loc { font-size: 12px; color: var(--text-tertiary); }
    .sr-org-desc { font-size: 12.5px; line-height: 1.5; color: var(--text-secondary); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .sr-org-stats { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
    .sr-org-stats span { display: inline-flex; align-items: center; gap: 5px; padding: 4px 9px; border-radius: 999px; background: var(--surface-hover); font-size: 11.5px; font-weight: 700; color: var(--text-secondary); }

    .sr-empty { text-align: center; padding: 20px 12px; color: var(--text-secondary); }
    .sr-empty h3 { margin: 10px 0 4px; font-size: 16px; font-weight: 800; color: var(--text-primary); }
    .sr-empty p { margin: 0 auto 12px; max-width: 340px; font-size: 13px; line-height: 1.6; }
    .sr-reset { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 999px; background: var(--surface-hover); color: var(--text-primary); font-weight: 700; font-size: 13px; text-decoration: none; }

    .sr-pages { display: flex; align-items: center; justify-content: center; gap: 12px; margin: 18px 0 4px; }
    .sr-page { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: var(--surface-bg); border: 1px solid var(--surface-border); color: var(--text-primary); text-decoration: none; }
    .sr-page.is-off { opacity: .4; }
    .sr-page-info { font-size: 13px; font-weight: 600; color: var(--text-secondary); }

    @media (min-width: 640px) { .sp-hero { padding: 22px 22px 20px; } .sp-hero h1 { font-size: 1.5rem; } }
    @media (prefers-reduced-motion: reduce) { .sp-spin { animation: none; } }
@endsection

@section('content')
<div class="sp">
    <section class="sp-hero reveal">
        <div class="sp-hero-top">
            <h1>{{ __('Find a place to follow') }}<small>{{ __('Hotels, stores, schools — search by name, city or country.') }}</small></h1>
            <x-illustration name="search" size="92" />
        </div>
        <form class="sp-form" id="spForm" action="{{ route('guest.search-organizations') }}" method="GET" role="search" data-no-loader>
            @if ($country !== '')<input type="hidden" name="country" value="{{ $country }}">@endif
            <div class="sp-box">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="q" id="spInput" value="{{ $search }}" placeholder="{{ __('Search organizations or channels…') }}"
                       aria-label="{{ __('Search organizations or channels') }}" autocomplete="off" enterkeyhint="search">
                <span class="sp-spin" aria-hidden="true"></span>
                <button type="button" class="sp-clear" id="spClear" aria-label="{{ __('Clear') }}" @if ($search === '') hidden @endif><i class="bi bi-x-lg"></i></button>
                <button type="submit" class="sp-go"><span>{{ __('Search') }}</span></button>
            </div>
        </form>
    </section>

    <nav class="sp-chips" aria-label="{{ __('Filter by country') }}">
        <a href="{{ route('guest.search-organizations', array_filter(['q' => $search])) }}" class="sp-chip {{ $country === '' ? 'active' : '' }}"><i class="bi bi-globe2"></i> {{ __('All') }}</a>
        @foreach ($countries as $c)
            <a href="{{ route('guest.search-organizations', array_filter(['q' => $search, 'country' => $c])) }}" class="sp-chip {{ $country === $c ? 'active' : '' }}">{{ $c }}</a>
        @endforeach
    </nav>

    <div class="sr" id="spResults" aria-live="polite">
        @include('guest.partials._search-results')
    </div>
</div>
@endsection

@section('scripts')
<script>
    /* Live search: results update while typing (debounced); Enter or the button still submit normally. */
    (function () {
        const form = document.getElementById('spForm');
        const input = document.getElementById('spInput');
        const clear = document.getElementById('spClear');
        const results = document.getElementById('spResults');
        let timer = null, controller = null;

        async function run() {
            const url = new URL(form.action, window.location.href);
            new FormData(form).forEach((v, k) => { if (v !== '') url.searchParams.set(k, v); });
            history.replaceState(null, '', url);
            url.searchParams.set('partial', '1');
            controller?.abort();
            controller = new AbortController();
            form.classList.add('is-loading');
            results.classList.add('is-loading');
            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' }, signal: controller.signal, credentials: 'same-origin' });
                if (!res.ok) throw new Error(res.status);
                results.innerHTML = (await res.json()).html;
                window.ipReveal && window.ipReveal(results);
                document.querySelectorAll('.sp-chip').forEach(chip => {
                    const u = new URL(chip.href);
                    input.value ? u.searchParams.set('q', input.value) : u.searchParams.delete('q');
                    chip.href = u;
                });
            } catch (e) {
                if (e.name !== 'AbortError') form.submit();
            } finally {
                form.classList.remove('is-loading');
                results.classList.remove('is-loading');
            }
        }

        input.addEventListener('input', () => {
            clear.hidden = input.value === '';
            clearTimeout(timer);
            timer = setTimeout(run, 300);
        });
        clear.addEventListener('click', () => { input.value = ''; clear.hidden = true; input.focus(); run(); });
        form.addEventListener('submit', e => { e.preventDefault(); clearTimeout(timer); run(); input.blur(); });
    })();
</script>
@endsection
