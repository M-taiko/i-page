@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'elevated' => false,
    'headerAction' => null,
])

@php
    $cardClasses = 'card' . ($elevated ? ' card-elevated' : '');
@endphp

<div @class([$cardClasses]) {{ $attributes }}>
    @if ($title || $icon || $headerAction)
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: var(--space-3); flex: 1;">
                @if ($icon)
                    <i class="bi bi-{{ $icon }}" style="font-size: var(--text-2xl); color: var(--primary-600);"></i>
                @endif
                <div>
                    @if ($title)
                        <h3 style="margin: 0; font-size: var(--text-lg);">{{ $title }}</h3>
                    @endif
                    @if ($subtitle)
                        <p style="margin: var(--space-1) 0 0; font-size: var(--text-sm); color: var(--text-tertiary);">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            @if ($headerAction)
                <div>{{ $headerAction }}</div>
            @endif
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>
</div>
