@props([
    'title',
    'message',
    'icon' => 'inbox',
    'action' => null,
])

<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: var(--space-16) var(--space-8); text-align: center;" {{ $attributes }}>
    <div style="font-size: 64px; color: var(--neutral-300); margin-bottom: var(--space-6);">
        <i class="bi bi-{{ $icon }}"></i>
    </div>

    <h3 style="font-size: var(--text-xl); color: var(--text-primary); margin: 0 0 var(--space-2);">
        {{ $title }}
    </h3>

    <p style="font-size: var(--text-sm); color: var(--text-secondary); margin: 0 0 var(--space-6); max-width: 300px;">
        {{ $message }}
    </p>

    @if ($action)
        <div>{{ $action }}</div>
    @endif
</div>
