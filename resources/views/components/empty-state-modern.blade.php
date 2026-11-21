@props([
    'title',
    'message',
    'icon' => 'inbox',
    'action' => null,
    'illustration' => null,
])

<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: var(--space-16) var(--space-8); text-align: center;" {{ $attributes }}>
    @php
        $scene = $illustration ?? match (true) {
            str_contains($icon, 'search') => 'search',
            str_contains($icon, 'bell') => 'notifications',
            str_contains($icon, 'chat') || str_contains($icon, 'diagram') => 'channels',
            str_contains($icon, 'folder') || str_contains($icon, 'bookmark') || str_contains($icon, 'star') => 'collection',
            str_contains($icon, 'hourglass') || str_contains($icon, 'clock') => 'notifications',
            default => 'empty',
        };
    @endphp
    <div style="margin-bottom: var(--space-4);"><x-illustration :name="$scene" size="170" /></div>

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
