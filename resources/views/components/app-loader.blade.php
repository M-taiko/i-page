{{-- Page loader: the animated i-page logo (docs/27 "Brand Logo"). size = logo height in px. --}}
@props(['size' => 64, 'caption' => true])

<div {{ $attributes->merge(['class' => 'app-loader']) }} role="status" aria-live="polite" aria-label="{{ __('Loading') }}">
    <x-brand-logo variant="full" animate="loop" :height="$size" />
    @if ($caption)
        <div class="app-loader-bar" aria-hidden="true"><span></span></div>
    @endif
</div>

@once
<style>
    .app-loader { display: flex; flex-direction: column; align-items: center; gap: 18px; }
    .app-loader-bar { width: 140px; height: 4px; border-radius: 999px; overflow: hidden; background: color-mix(in srgb, #6d5dfc 16%, transparent); }
    .app-loader-bar span { display: block; width: 40%; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #6d5dfc, #c084fc, #fbbf24); animation: appLoaderBar 1.2s ease-in-out infinite; }
    @keyframes appLoaderBar { 0% { transform: translateX(-110%); } 100% { transform: translateX(260%); } }
    @media (prefers-reduced-motion: reduce) { .app-loader-bar span { animation: none; width: 100%; opacity: .6; } }
</style>
@endonce
