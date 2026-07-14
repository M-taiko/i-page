@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $alertClass = 'alert alert-' . $type;
    $icons = [
        'success' => 'check-circle-fill',
        'danger' => 'exclamation-circle-fill',
        'warning' => 'exclamation-triangle-fill',
        'info' => 'info-circle-fill',
    ];
    $icon = $icons[$type] ?? 'info-circle-fill';
@endphp

<div @class([$alertClass, 'alert-dismissible' => $dismissible]) role="alert" {{ $attributes }}>
    <i class="bi bi-{{ $icon }}" style="flex-shrink: 0;"></i>
    <div style="flex: 1;">
        @if ($title)
            <strong>{{ $title }}</strong>
            <p style="margin: var(--space-2) 0 0; font-size: var(--text-sm);">{{ $slot }}</p>
        @else
            {{ $slot }}
        @endif
    </div>
    @if ($dismissible)
        <button type="button" class="alert-close" data-dismiss="alert" aria-label="Close">
            <i class="bi bi-x"></i>
        </button>
    @endif
</div>
