@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'icon' => null,
    'loading' => false,
    'href' => null,
])

@php
    $baseClasses = 'btn btn-' . $variant . ' btn-' . $size;
    $classes = $disabled ? $baseClasses . ' disabled' : $baseClasses;
@endphp

@if ($href)
    <a href="{{ $href }}" @class([$classes]) {{ $attributes }}>
        @if ($icon)
            <i class="bi bi-{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @class([$classes])
        @disabled($disabled)
        @if($loading) disabled @endif
        {{ $attributes }}
    >
        @if ($loading)
            <i class="bi bi-arrow-repeat" style="animation: spin 0.8s linear infinite;"></i>
        @elseif ($icon)
            <i class="bi bi-{{ $icon }}"></i>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
