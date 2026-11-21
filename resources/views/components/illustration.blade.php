{{-- Decorative animated SVG scene (docs/12 "Illustrations"). Needs public/css/illustrations.css.
     Usage: <x-illustration name="discover" size="180" /> --}}
@props(['name' => 'empty', 'size' => 160])
@php $h = (int) round($size * 0.8); @endphp
<svg {{ $attributes->merge(['class' => 'ill ill-' . $name]) }} width="{{ $size }}" height="{{ $h }}" viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
    <ellipse cx="100" cy="146" rx="62" ry="7" fill="var(--ill-softer)"/>
    @switch($name)
        @case('discover')
            {{-- A phone of cards with a compass orbiting --}}
            <circle cx="100" cy="76" r="62" fill="var(--ill-soft)"/>
            <g class="ill-spin-slow"><circle cx="100" cy="76" r="70" stroke="var(--ill-primary)" stroke-opacity=".25" stroke-dasharray="2 8" stroke-linecap="round"/></g>
            <g class="ill-float">
                <rect x="72" y="22" width="56" height="104" rx="12" fill="var(--ill-paper)" stroke="var(--ill-ink)" stroke-opacity=".12"/>
                <rect x="80" y="34" width="40" height="22" rx="5" fill="var(--ill-primary)"/>
                <rect x="80" y="62" width="40" height="5" rx="2.5" fill="var(--ill-line)"/>
                <rect x="80" y="71" width="28" height="5" rx="2.5" fill="var(--ill-line)"/>
                <rect x="80" y="84" width="40" height="22" rx="5" fill="var(--ill-accent)" fill-opacity=".85"/>
                <rect x="80" y="111" width="22" height="5" rx="2.5" fill="var(--ill-line)"/>
            </g>
            <g class="ill-float-delay">
                <circle cx="150" cy="44" r="15" fill="var(--ill-paper)" stroke="var(--ill-primary)" stroke-width="3"/>
                <path d="M150 34 L154 44 L150 54 L146 44 Z" fill="var(--ill-accent)"/>
                <circle cx="150" cy="44" r="2" fill="var(--ill-ink)"/>
            </g>
            <path class="ill-twinkle" d="M44 40 l3 7 7 3 -7 3 -3 7 -3 -7 -7 -3 7 -3z" fill="var(--ill-warm)"/>
            <circle class="ill-twinkle-2" cx="160" cy="104" r="4" fill="var(--ill-green)"/>
            <circle class="ill-twinkle-3" cx="40" cy="98" r="3" fill="var(--ill-accent)"/>
            @break

        @case('feed')
            {{-- Stacked post cards with reactions rising --}}
            <circle cx="100" cy="80" r="58" fill="var(--ill-soft)"/>
            <g class="ill-float-slow"><rect x="52" y="30" width="96" height="38" rx="10" fill="var(--ill-paper)" stroke="var(--ill-ink)" stroke-opacity=".1"/>
                <circle cx="68" cy="49" r="8" fill="var(--ill-accent)"/><rect x="82" y="42" width="52" height="5" rx="2.5" fill="var(--ill-line)"/><rect x="82" y="51" width="36" height="5" rx="2.5" fill="var(--ill-line)"/></g>
            <g class="ill-float"><rect x="40" y="76" width="120" height="50" rx="12" fill="var(--ill-paper)" stroke="var(--ill-ink)" stroke-opacity=".12"/>
                <circle cx="58" cy="94" r="9" fill="var(--ill-primary)"/><rect x="74" y="87" width="66" height="5" rx="2.5" fill="var(--ill-line)"/><rect x="74" y="96" width="44" height="5" rx="2.5" fill="var(--ill-line)"/>
                <rect x="52" y="110" width="22" height="8" rx="4" fill="var(--ill-soft)"/><rect x="80" y="110" width="22" height="8" rx="4" fill="var(--ill-soft)"/></g>
            <path class="ill-rise" d="M150 70 c-4-6-12-3-10 3 c1 4 10 9 10 9 s9-5 10-9 c2-6-6-9-10-3z" fill="var(--ill-accent)"/>
            <path class="ill-rise-2" d="M164 86 c-3-4-9-2-7 2 c1 3 7 6 7 6 s6-3 7-6 c2-4-4-6-7-2z" fill="var(--ill-primary)"/>
            <circle class="ill-rise-3" cx="142" cy="92" r="4" fill="var(--ill-warm)"/>
            @break

        @case('caught-up')
            {{-- Check mark drawing itself, with confetti --}}
            <circle class="ill-pulse" cx="100" cy="76" r="44" fill="var(--ill-soft)"/>
            <circle class="ill-pop" cx="100" cy="76" r="40" fill="var(--ill-primary)"/>
            <path class="ill-draw" d="M82 77 l12 12 24-26" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
            <rect class="ill-twinkle" x="42" y="40" width="8" height="8" rx="2" fill="var(--ill-accent)" transform="rotate(20 46 44)"/>
            <rect class="ill-twinkle-2" x="152" y="34" width="7" height="7" rx="2" fill="var(--ill-warm)"/>
            <circle class="ill-twinkle-3" cx="156" cy="108" r="5" fill="var(--ill-green)"/>
            <circle class="ill-twinkle" cx="46" cy="112" r="4" fill="var(--ill-primary)"/>
            <path class="ill-twinkle-2" d="M60 22 l2 5 5 2 -5 2 -2 5 -2 -5 -5 -2 5 -2z" fill="var(--ill-warm)"/>
            @break

        @case('channels')
            {{-- A small tree of channels connected together --}}
            <circle cx="100" cy="78" r="60" fill="var(--ill-soft)"/>
            <path class="ill-draw" d="M100 50 V72 M100 72 H58 V96 M100 72 H142 V96 M100 72 V96" stroke="var(--ill-primary)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            <g class="ill-pop"><rect x="78" y="22" width="44" height="30" rx="10" fill="var(--ill-primary)"/><path d="M92 37 h16 M92 31 h10" stroke="#fff" stroke-width="3" stroke-linecap="round"/></g>
            <g class="ill-pop-2 ill-float"><rect x="38" y="96" width="40" height="28" rx="9" fill="var(--ill-paper)" stroke="var(--ill-accent)" stroke-width="2.5"/><circle cx="50" cy="110" r="4" fill="var(--ill-accent)"/><rect x="58" y="108" width="12" height="4" rx="2" fill="var(--ill-line)"/></g>
            <g class="ill-pop-3 ill-float-delay"><rect x="80" y="96" width="40" height="28" rx="9" fill="var(--ill-paper)" stroke="var(--ill-primary)" stroke-width="2.5"/><circle cx="92" cy="110" r="4" fill="var(--ill-primary)"/><rect x="100" y="108" width="12" height="4" rx="2" fill="var(--ill-line)"/></g>
            <g class="ill-pop-3 ill-float"><rect x="122" y="96" width="40" height="28" rx="9" fill="var(--ill-paper)" stroke="var(--ill-green)" stroke-width="2.5"/><circle cx="134" cy="110" r="4" fill="var(--ill-green)"/><rect x="142" y="108" width="12" height="4" rx="2" fill="var(--ill-line)"/></g>
            <circle class="ill-twinkle" cx="160" cy="40" r="4" fill="var(--ill-warm)"/>
            <circle class="ill-twinkle-2" cx="40" cy="52" r="3" fill="var(--ill-accent)"/>
            @break

        @case('search')
            {{-- Magnifier sweeping over results --}}
            <circle cx="100" cy="78" r="58" fill="var(--ill-soft)"/>
            <rect x="48" y="40" width="104" height="14" rx="7" fill="var(--ill-paper)" stroke="var(--ill-ink)" stroke-opacity=".1"/>
            <rect x="48" y="64" width="104" height="14" rx="7" fill="var(--ill-paper)" stroke="var(--ill-ink)" stroke-opacity=".1"/>
            <rect x="48" y="88" width="104" height="14" rx="7" fill="var(--ill-paper)" stroke="var(--ill-ink)" stroke-opacity=".1"/>
            <circle class="ill-blink" cx="58" cy="47" r="3" fill="var(--ill-primary)"/><circle class="ill-blink-2" cx="58" cy="71" r="3" fill="var(--ill-accent)"/><circle class="ill-blink-3" cx="58" cy="95" r="3" fill="var(--ill-green)"/>
            <g class="ill-sweep"><circle cx="112" cy="80" r="20" fill="var(--ill-paper)" fill-opacity=".6" stroke="var(--ill-primary)" stroke-width="6"/>
                <path d="M127 95 l16 16" stroke="var(--ill-primary)" stroke-width="8" stroke-linecap="round"/><path d="M104 72 a10 10 0 0 1 10 -4" stroke="#fff" stroke-width="3" stroke-linecap="round"/></g>
            @break

        @case('notifications')
            {{-- A ringing bell --}}
            <circle cx="100" cy="76" r="58" fill="var(--ill-soft)"/>
            <circle class="ill-pulse" cx="100" cy="70" r="40" stroke="var(--ill-primary)" stroke-width="2"/>
            <circle class="ill-pulse-2" cx="100" cy="70" r="40" stroke="var(--ill-accent)" stroke-width="2"/>
            <g class="ill-wiggle">
                <path d="M100 32 c-17 0-28 13-28 30 v18 l-8 12 h72 l-8-12 v-18 c0-17-11-30-28-30z" fill="var(--ill-primary)"/>
                <circle cx="100" cy="100" r="8" fill="var(--ill-warm)"/>
                <path d="M84 52 a18 18 0 0 1 10 -10" stroke="#fff" stroke-opacity=".6" stroke-width="4" stroke-linecap="round"/>
            </g>
            <circle class="ill-pop-3" cx="124" cy="40" r="9" fill="var(--ill-accent)"/>
            @break

        @case('collection')
            {{-- A folder with saved stars --}}
            <circle cx="100" cy="78" r="58" fill="var(--ill-soft)"/>
            <path class="ill-rise" d="M100 30 l4 9 10 1 -8 7 3 10 -9 -5 -9 5 3 -10 -8 -7 10 -1z" fill="var(--ill-warm)"/>
            <path class="ill-rise-2" d="M128 44 l3 6 7 1 -5 5 2 7 -7 -4 -6 4 2 -7 -5 -5 7 -1z" fill="var(--ill-accent)"/>
            <g class="ill-float"><path d="M50 62 h34 l8 10 h58 v50 a8 8 0 0 1 -8 8 h-84 a8 8 0 0 1 -8 -8z" fill="var(--ill-primary)"/>
                <path d="M50 80 h100 v42 a8 8 0 0 1 -8 8 h-84 a8 8 0 0 1 -8 -8z" fill="var(--ill-primary)" fill-opacity=".75"/>
                <rect x="66" y="96" width="44" height="6" rx="3" fill="#fff" fill-opacity=".8"/><rect x="66" y="108" width="30" height="6" rx="3" fill="#fff" fill-opacity=".55"/></g>
            @break

        @case('sunrise')
            {{-- Morning: a sun rising behind a phone, news bubbles popping in --}}
            <circle cx="100" cy="80" r="62" fill="var(--ill-soft)"/>
            <g class="ill-spin-slow">
                @foreach (range(0, 330, 30) as $deg)
                    <rect x="98" y="16" width="4" height="12" rx="2" fill="var(--ill-warm)" transform="rotate({{ $deg }} 100 66)" opacity=".7"/>
                @endforeach
            </g>
            <circle class="ill-pulse" cx="100" cy="66" r="26" fill="var(--ill-warm)" opacity=".5"/>
            <circle cx="100" cy="66" r="22" fill="var(--ill-warm)"/>
            <g class="ill-float">
                <rect x="70" y="62" width="60" height="76" rx="12" fill="var(--ill-paper)" stroke="var(--ill-ink)" stroke-opacity=".12"/>
                <rect x="78" y="72" width="44" height="6" rx="3" fill="var(--ill-line)"/>
                <rect x="78" y="82" width="30" height="6" rx="3" fill="var(--ill-line)"/>
                <rect x="78" y="96" width="44" height="22" rx="6" fill="var(--ill-primary)"/>
            </g>
            <g class="ill-pop-2 ill-float-delay"><rect x="132" y="70" width="40" height="22" rx="11" fill="var(--ill-accent)"/><circle cx="144" cy="81" r="3" fill="#fff"/><circle cx="152" cy="81" r="3" fill="#fff"/><circle cx="160" cy="81" r="3" fill="#fff"/></g>
            <g class="ill-pop-3 ill-float"><rect x="26" y="88" width="40" height="22" rx="11" fill="var(--ill-green)"/><path d="M38 99 l5 5 9-9" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/></g>
            @break

        @case('night')
            {{-- Evening: a moon and twinkling stars over a glowing phone --}}
            <circle cx="100" cy="80" r="62" fill="var(--ill-soft)"/>
            <path class="ill-float-slow" d="M124 26 a24 24 0 1 0 22 34 a19 19 0 1 1 -22 -34z" fill="var(--ill-warm)"/>
            <path class="ill-twinkle" d="M50 34 l3 7 7 3 -7 3 -3 7 -3 -7 -7 -3 7 -3z" fill="var(--ill-warm)"/>
            <circle class="ill-twinkle-2" cx="168" cy="96" r="3.5" fill="var(--ill-paper)"/>
            <circle class="ill-twinkle-3" cx="34" cy="76" r="3" fill="var(--ill-paper)"/>
            <circle class="ill-twinkle" cx="80" cy="22" r="2.5" fill="var(--ill-paper)"/>
            <g class="ill-float">
                <rect x="70" y="58" width="60" height="80" rx="12" fill="var(--ill-paper)" stroke="var(--ill-ink)" stroke-opacity=".12"/>
                <rect x="78" y="68" width="44" height="24" rx="6" fill="var(--ill-primary)"/>
                <rect x="78" y="98" width="44" height="6" rx="3" fill="var(--ill-line)"/>
                <rect x="78" y="108" width="28" height="6" rx="3" fill="var(--ill-line)"/>
            </g>
            <path class="ill-rise" d="M142 112 c-4-6-12-3-10 3 c1 4 10 9 10 9 s9-5 10-9 c2-6-6-9-10-3z" fill="var(--ill-accent)"/>
            @break

        @default
            {{-- empty: an open box with floating dots --}}
            <circle cx="100" cy="78" r="58" fill="var(--ill-soft)"/>
            <circle class="ill-rise" cx="92" cy="64" r="5" fill="var(--ill-accent)"/>
            <circle class="ill-rise-2" cx="110" cy="62" r="4" fill="var(--ill-warm)"/>
            <circle class="ill-rise-3" cx="102" cy="58" r="3.5" fill="var(--ill-green)"/>
            <g class="ill-float">
                <path d="M56 82 l44 -16 44 16 -44 16z" fill="var(--ill-primary)" fill-opacity=".55"/>
                <path d="M56 82 v34 l44 16 v-34z" fill="var(--ill-primary)"/>
                <path d="M144 82 v34 l-44 16 v-34z" fill="var(--ill-primary)" fill-opacity=".8"/>
                <path d="M56 82 l-12 14 44 16 12 -14z" fill="var(--ill-paper)" stroke="var(--ill-ink)" stroke-opacity=".1"/>
                <path d="M144 82 l12 14 -44 16 -12 -14z" fill="var(--ill-paper)" stroke="var(--ill-ink)" stroke-opacity=".1"/>
            </g>
    @endswitch
</svg>
