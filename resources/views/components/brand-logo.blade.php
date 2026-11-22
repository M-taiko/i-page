{{-- i-Page logo (docs/27 "Brand Logo"): a document holding a white "i" (its dot is the gold notification dot)
     followed by the word "Page" — capital P in gold — so the whole reads "iPage".
     variant: full | mark · animate: live (default, endless) | none · height in px.
     live: the i keeps bouncing; "Page" types itself, then the document slides over it like an eraser,
     rubbing the letters out one by one, goes back to its place, and the word types again. --}}
@props(['variant' => 'full', 'animate' => 'live', 'height' => 32])
@php
    $uid = 'bl' . substr(md5(uniqid('', true)), 0, 6);
    $full = $variant !== 'mark';
    $w = $full ? 166 : 64;
    $width = (int) round($height * $w / 64);
    $live = $animate !== 'none';
    $font = "Inter, 'Segoe UI', system-ui, -apple-system, sans-serif";
    // Fixed letter positions so the caret and the eraser can follow them.
    $letters = [['P', 67, 42, '#fbbf24'], ['a', 95, 36, null], ['g', 116, 36, null], ['e', 138, 36, null]];
@endphp
<svg {{ $attributes->merge(['class' => 'brand-logo' . ($live ? ' bl-live' : '') . ($full ? ' bl-full' : ' bl-only-mark')]) }} width="{{ $width }}" height="{{ $height }}" viewBox="0 0 {{ $w }} 64"
     xmlns="http://www.w3.org/2000/svg" role="img" aria-label="iPage" direction="ltr">
    <defs>
        <linearGradient id="{{ $uid }}g" x1="8" y1="58" x2="56" y2="6" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#4338ca"/>
            <stop offset=".55" stop-color="#6d5dfc"/>
            <stop offset="1" stop-color="#c084fc"/>
        </linearGradient>
        <linearGradient id="{{ $uid }}t" x1="60" y1="0" x2="166" y2="0" gradientUnits="userSpaceOnUse">
            <stop offset="0" style="stop-color: var(--bl-ink-1, #312e81)"/>
            <stop offset="1" style="stop-color: var(--bl-ink-2, #6d28d9)"/>
        </linearGradient>
    </defs>
    @if ($full)
        <g class="bl-word">
            @foreach ($letters as $i => [$char, $x, $size, $color])
                <text class="bl-ch bl-ch{{ $i + 1 }}" x="{{ $x }}" y="46" font-family="{{ $font }}" font-size="{{ $size }}" font-weight="800"
                      fill="{{ $color ?? 'url(#' . $uid . 't)' }}">{{ $char }}</text>
            @endforeach
            @if ($live)
                <g class="bl-caret-blink"><rect class="bl-caret" x="66" y="17" width="3" height="33" rx="1.5" fill="#6d5dfc"/></g>
            @endif
        </g>
    @endif
    {{-- The document is drawn last so it passes over the letters while erasing. --}}
    <g class="bl-move"><g class="bl-scrub"><g class="bl-mark">
        <path d="M16 6 H38 L54 22 V52 a6 6 0 0 1 -6 6 H16 a6 6 0 0 1 -6 -6 V12 a6 6 0 0 1 6 -6 Z" fill="url(#{{ $uid }}g)"/>
        <path d="M38 6 V18 a4 4 0 0 0 4 4 H54 Z" fill="#fff" fill-opacity=".38"/>
        <g class="bl-i">
            <rect x="27" y="28" width="10" height="21" rx="5" fill="#fff"/>
            <circle cx="32" cy="18.5" r="5.5" fill="#fbbf24"/>
        </g>
    </g></g></g>
</svg>

@once
<style>
    /* Always left-to-right, also on Arabic pages, so the letters keep their places next to the document. */
    .brand-logo { display: inline-block; vertical-align: middle; overflow: visible; direction: ltr; unicode-bidi: isolate; }
    .brand-logo text { direction: ltr; unicode-bidi: plaintext; }
    .brand-logo * { transform-box: fill-box; }
    [data-theme="dark"] .brand-logo, .dark-theme .brand-logo { --bl-ink-1: #e0e7ff; --bl-ink-2: #c4b5fd; }

    /* The i bounces inside the document, all the time. */
    .bl-live .bl-i { transform-origin: 50% 100%; animation: blBounce 1.2s cubic-bezier(.3, .7, .4, 1) infinite; }
    @keyframes blBounce {
        0%, 100% { transform: translateY(0) scale(1, 1); }
        12% { transform: translateY(0) scale(1.12, .86); }
        40% { transform: translateY(-6px) scale(.94, 1.08); }
        62% { transform: translateY(0) scale(1.08, .92); }
        78% { transform: translateY(-1.5px) scale(1); }
    }
    /* Mark only (icons, collapsed sidebar): a gentle sway with the bouncing i. */
    .bl-live.bl-only-mark .bl-mark { transform-origin: 50% 90%; animation: blSway 2.4s ease-in-out infinite; }
    @keyframes blSway { 0%, 100% { transform: rotate(0); } 25% { transform: rotate(-7deg) scale(1.05); } 75% { transform: rotate(7deg) scale(1.05); } }

    /* Full logo, one 6 s cycle:
       0–8% rest · 10–30% "Page" types · 30–52% hold · 52–74% the document erases it · 74–88% it goes back · rest. */
    .bl-live.bl-full .bl-move { animation: blMove 6s cubic-bezier(.45, 0, .25, 1) infinite; }
    .bl-live.bl-full .bl-scrub { transform-origin: 50% 100%; animation: blScrub 6s linear infinite; }
    @keyframes blMove {
        0%, 52% { transform: translateX(0); }
        74% { transform: translateX(102px); }
        80% { transform: translateX(102px) translateY(-6px); }
        88%, 100% { transform: translateX(0); }
    }
    @keyframes blScrub {
        0%, 52% { transform: rotate(0); }
        55% { transform: rotate(-12deg) translateY(1px); }
        58% { transform: rotate(-4deg); }
        61% { transform: rotate(-12deg) translateY(1px); }
        64% { transform: rotate(-4deg); }
        67% { transform: rotate(-12deg) translateY(1px); }
        70% { transform: rotate(-4deg); }
        73% { transform: rotate(-10deg); }
        76%, 100% { transform: rotate(0); }
    }

    /* Typing: each letter appears in turn, and disappears when the eraser reaches it. */
    .bl-live .bl-ch { opacity: 0; animation: 6s linear infinite; transform-origin: 50% 70%; }
    .bl-live .bl-ch1 { animation-name: blC1; } .bl-live .bl-ch2 { animation-name: blC2; }
    .bl-live .bl-ch3 { animation-name: blC3; } .bl-live .bl-ch4 { animation-name: blC4; }
    @keyframes blC1 { 0%, 10% { opacity: 0; transform: scale(.5); } 11% { opacity: 1; transform: scale(1.2); } 13%, 57% { opacity: 1; transform: none; } 59%, 100% { opacity: 0; transform: scale(.4) rotate(-15deg); } }
    @keyframes blC2 { 0%, 16% { opacity: 0; } 16.5%, 62% { opacity: 1; transform: none; } 64%, 100% { opacity: 0; transform: scale(.4) rotate(-15deg); } }
    @keyframes blC3 { 0%, 22% { opacity: 0; } 22.5%, 66.5% { opacity: 1; transform: none; } 68.5%, 100% { opacity: 0; transform: scale(.4) rotate(-15deg); } }
    @keyframes blC4 { 0%, 28% { opacity: 0; } 28.5%, 71% { opacity: 1; transform: none; } 73%, 100% { opacity: 0; transform: scale(.4) rotate(-15deg); } }

    /* The caret follows the typing, blinks while the word rests, hides while erasing. */
    .bl-live .bl-caret { animation: blCaret 6s steps(1, end) infinite; }
    .bl-live .bl-caret-blink { animation: blBlink .8s steps(1, end) infinite; }
    @keyframes blCaret {
        0%, 10% { opacity: 1; transform: translateX(0); }
        10.01%, 16% { opacity: 1; transform: translateX(28px); }
        16.01%, 22% { opacity: 1; transform: translateX(50px); }
        22.01%, 28% { opacity: 1; transform: translateX(71px); }
        28.01%, 52% { opacity: 1; transform: translateX(93px); }
        52.01%, 88% { opacity: 0; transform: translateX(93px); }
        88.01%, 100% { opacity: 1; transform: translateX(0); }
    }
    @keyframes blBlink { 0%, 50% { opacity: 1; } 50.01%, 100% { opacity: 0; } }

    @media (prefers-reduced-motion: reduce) {
        .brand-logo * { animation: none !important; }
        .bl-live .bl-ch { opacity: 1; }
        .bl-live .bl-caret-blink { display: none; }
    }
</style>
@endonce
