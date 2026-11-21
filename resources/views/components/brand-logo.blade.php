{{-- i-Page logo (docs/27 "Brand Logo"): a page with a folded corner holding a white "i" whose dot is a gold
     notification dot, followed by the wordmark "i-page" (the p in gold, slightly larger).
     variant: full | mark · animate: live (default, endless) | none · height in px.
     live: the page rocks and grows a little, the i bounces, the word types itself, then deletes and retypes. --}}
@props(['variant' => 'full', 'animate' => 'live', 'height' => 32])
@php
    $uid = 'bl' . substr(md5(uniqid('', true)), 0, 6);
    $full = $variant !== 'mark';
    $w = $full ? 196 : 64;
    $width = (int) round($height * $w / 64);
    $live = $animate !== 'none';
    $font = "Inter, 'Segoe UI', system-ui, -apple-system, sans-serif";
    // Letter positions are fixed so the caret can follow the typing.
    $letters = [['i', 66, 35, null], ['-', 77, 35, null], ['p', 90, 41, '#fbbf24'], ['a', 115, 35, null], ['g', 135, 35, null], ['e', 156, 35, null]];
@endphp
<svg {{ $attributes->merge(['class' => 'brand-logo' . ($live ? ' bl-live' : '')]) }} width="{{ $width }}" height="{{ $height }}" viewBox="0 0 {{ $w }} 64"
     xmlns="http://www.w3.org/2000/svg" role="img" aria-label="i-page">
    <defs>
        <linearGradient id="{{ $uid }}g" x1="8" y1="58" x2="56" y2="6" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#4338ca"/>
            <stop offset=".55" stop-color="#6d5dfc"/>
            <stop offset="1" stop-color="#c084fc"/>
        </linearGradient>
        <linearGradient id="{{ $uid }}t" x1="60" y1="0" x2="190" y2="0" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="var(--bl-ink-1, #312e81)"/>
            <stop offset="1" stop-color="var(--bl-ink-2, #6d28d9)"/>
        </linearGradient>
    </defs>
    <g class="bl-mark">
        <path d="M16 6 H38 L54 22 V52 a6 6 0 0 1 -6 6 H16 a6 6 0 0 1 -6 -6 V12 a6 6 0 0 1 6 -6 Z" fill="url(#{{ $uid }}g)"/>
        <path d="M38 6 V18 a4 4 0 0 0 4 4 H54 Z" fill="#fff" fill-opacity=".38"/>
        <g class="bl-i">
            <rect x="27" y="28" width="10" height="21" rx="5" fill="#fff"/>
            <circle cx="32" cy="18.5" r="5.5" fill="#fbbf24"/>
        </g>
    </g>
    @if ($full)
        <g class="bl-word">
            @foreach ($letters as $i => [$char, $x, $size, $color])
                <text class="bl-ch bl-ch{{ $i + 1 }}" x="{{ $x }}" y="45" font-family="{{ $font }}" font-size="{{ $size }}" font-weight="800"
                      fill="{{ $color ?? 'url(#' . $uid . 't)' }}">{{ $char }}</text>
            @endforeach
            @if ($live)
                <g class="bl-caret-blink"><rect class="bl-caret" x="63" y="17" width="3" height="32" rx="1.5" fill="#6d5dfc"/></g>
            @endif
        </g>
    @endif
</svg>

@once
<style>
    .brand-logo { display: inline-block; vertical-align: middle; overflow: visible; }
    .brand-logo * { transform-box: fill-box; }
    [data-theme="dark"] .brand-logo, .dark-theme .brand-logo { --bl-ink-1: #e0e7ff; --bl-ink-2: #c4b5fd; }

    /* The page rocks left and right and grows a little; the i bounces inside it. */
    .bl-live .bl-mark { transform-origin: 50% 90%; animation: blRock 2.4s ease-in-out infinite; }
    .bl-live .bl-i { transform-origin: 50% 100%; animation: blBounce 1.2s cubic-bezier(.3, .7, .4, 1) infinite; }
    @keyframes blRock {
        0%, 100% { transform: rotate(0) scale(1); }
        25% { transform: rotate(-9deg) scale(1.07); }
        50% { transform: rotate(0) scale(1); }
        75% { transform: rotate(9deg) scale(1.07); }
    }
    @keyframes blBounce {
        0%, 100% { transform: translateY(0) scale(1, 1); }
        12% { transform: translateY(0) scale(1.12, .86); }
        40% { transform: translateY(-6px) scale(.94, 1.08); }
        62% { transform: translateY(0) scale(1.08, .92); }
        78% { transform: translateY(-1.5px) scale(1); }
    }

    /* Typewriter: letters appear one by one, hold, are deleted from the end, and the cycle repeats. */
    .bl-live .bl-ch { opacity: 0; animation: 6s steps(1, end) infinite; }
    .bl-live .bl-ch1 { animation-name: blT1; } .bl-live .bl-ch2 { animation-name: blT2; } .bl-live .bl-ch3 { animation-name: blT3; }
    .bl-live .bl-ch4 { animation-name: blT4; } .bl-live .bl-ch5 { animation-name: blT5; } .bl-live .bl-ch6 { animation-name: blT6; }
    @keyframes blT1 { 0%, 8% { opacity: 0; } 8.01%, 93% { opacity: 1; } 93.01%, 100% { opacity: 0; } }
    @keyframes blT2 { 0%, 14% { opacity: 0; } 14.01%, 89% { opacity: 1; } 89.01%, 100% { opacity: 0; } }
    @keyframes blT3 { 0%, 20% { opacity: 0; } 20.01%, 85% { opacity: 1; } 85.01%, 100% { opacity: 0; } }
    @keyframes blT4 { 0%, 26% { opacity: 0; } 26.01%, 81% { opacity: 1; } 81.01%, 100% { opacity: 0; } }
    @keyframes blT5 { 0%, 32% { opacity: 0; } 32.01%, 77% { opacity: 1; } 77.01%, 100% { opacity: 0; } }
    @keyframes blT6 { 0%, 38% { opacity: 0; } 38.01%, 73% { opacity: 1; } 73.01%, 100% { opacity: 0; } }
    /* The yellow p pops a little as it is typed. */
    .bl-live .bl-ch3 { transform-origin: 50% 80%; animation: blT3 6s steps(1, end) infinite, blPop 6s ease-out infinite; }
    @keyframes blPop { 0%, 20% { transform: scale(.6); } 23% { transform: scale(1.18); } 27%, 100% { transform: scale(1); } }

    /* The caret follows the last typed letter and blinks. */
    .bl-live .bl-caret { animation: blCaret 6s steps(1, end) infinite; }
    .bl-live .bl-caret-blink { animation: blBlink .8s steps(1, end) infinite; }
    @keyframes blCaret {
        0%, 8% { transform: translateX(0); }
        8.01%, 14% { transform: translateX(12px); }
        14.01%, 20% { transform: translateX(25px); }
        20.01%, 26% { transform: translateX(51px); }
        26.01%, 32% { transform: translateX(71px); }
        32.01%, 38% { transform: translateX(92px); }
        38.01%, 73% { transform: translateX(113px); }
        73.01%, 77% { transform: translateX(92px); }
        77.01%, 81% { transform: translateX(71px); }
        81.01%, 85% { transform: translateX(51px); }
        85.01%, 89% { transform: translateX(25px); }
        89.01%, 93% { transform: translateX(12px); }
        93.01%, 100% { transform: translateX(0); }
    }
    @keyframes blBlink { 0%, 50% { opacity: 1; } 50.01%, 100% { opacity: 0; } }

    @media (prefers-reduced-motion: reduce) {
        .brand-logo * { animation: none !important; }
        .bl-live .bl-ch { opacity: 1; }
        .bl-live .bl-caret-blink { display: none; }
    }
</style>
@endonce
