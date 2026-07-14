@props([
    'title',
    'value',
    'icon' => null,
    'trend' => null,
    'trendValue' => null,
    'color' => 'primary',
])

@php
    $bgColor = match($color) {
        'success' => 'var(--success-50)',
        'danger' => 'var(--danger-50)',
        'warning' => 'var(--warning-50)',
        'info' => 'var(--info-50)',
        default => 'var(--primary-50)',
    };

    $iconColor = match($color) {
        'success' => 'var(--success-600)',
        'danger' => 'var(--danger-600)',
        'warning' => 'var(--warning-600)',
        'info' => 'var(--info-600)',
        default => 'var(--primary-600)',
    };
@endphp

<div class="card" {{ $attributes }}>
    <div class="card-body" style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <p style="margin: 0 0 var(--space-2); font-size: var(--text-sm); color: var(--text-secondary); font-weight: var(--font-weight-medium);">
                {{ $title }}
            </p>
            <h3 style="margin: 0; font-size: var(--text-3xl); font-weight: var(--font-weight-bold);">
                {{ $value }}
            </h3>
            @if ($trend)
                <small style="margin-top: var(--space-2); display: flex; align-items: center; gap: var(--space-1); color: {{ $trend === 'up' ? 'var(--success-600)' : 'var(--danger-600)' }};">
                    <i class="bi bi-{{ $trend === 'up' ? 'arrow-up' : 'arrow-down' }}"></i>
                    {{ $trendValue }}
                </small>
            @endif
        </div>
        @if ($icon)
            <div style="width: 56px; height: 56px; border-radius: var(--radius-xl); background-color: {{ $bgColor }}; display: flex; align-items: center; justify-content: center; font-size: var(--text-2xl); color: {{ $iconColor }};">
                <i class="bi bi-{{ $icon }}"></i>
            </div>
        @endif
    </div>
</div>
