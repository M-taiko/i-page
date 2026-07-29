@props(['size' => 64])

<div {{ $attributes->merge(['class' => 'app-loader']) }} style="width: {{ $size }}px; height: {{ $size }}px;" role="status" aria-label="{{ __('Loading') }}">
    <svg viewBox="0 0 240 240" width="100%" height="100%">
        <defs>
            <linearGradient id="appLoaderGrad" x1="40" y1="185" x2="205" y2="45" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#1e3a8a"/>
                <stop offset="45%" stop-color="#2563eb"/>
                <stop offset="100%" stop-color="#2dd4bf"/>
            </linearGradient>
        </defs>

        <path class="app-loader-path"
              d="M60,170 C60,125 100,125 100,150 C100,175 140,175 140,150 C140,120 175,100 195,55"
              fill="none" stroke="url(#appLoaderGrad)" stroke-width="26"
              stroke-linecap="round" stroke-linejoin="round"/>

        <polygon class="app-loader-arrow" points="200,48 200.6,81.1 175,69.7" fill="url(#appLoaderGrad)"/>
    </svg>
</div>

<style>
    .app-loader-path {
        stroke-dasharray: 320;
        stroke-dashoffset: 320;
        animation: app-loader-draw 1.7s ease-in-out infinite;
    }

    .app-loader-arrow {
        transform-origin: 197px 63px;
        opacity: 0;
        animation: app-loader-arrow-in 1.7s ease-in-out infinite;
    }

    @keyframes app-loader-draw {
        0%   { stroke-dashoffset: 320; }
        55%  { stroke-dashoffset: 0; }
        85%  { stroke-dashoffset: 0; }
        100% { stroke-dashoffset: -320; }
    }

    @keyframes app-loader-arrow-in {
        0%, 45%  { opacity: 0; transform: scale(0.4); }
        60%      { opacity: 1; transform: scale(1.2); }
        75%, 85% { opacity: 1; transform: scale(1); }
        95%, 100% { opacity: 0; transform: scale(0.4); }
    }
</style>
