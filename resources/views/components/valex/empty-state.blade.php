{{-- Empty state for dashboard pages: an animated illustration (docs/12 "Illustrations") instead of a plain icon.
     Pass illustration="" to fall back to the icon. --}}
@props(['title', 'message' => null, 'icon' => 'inbox', 'illustration' => null])
@php
    $illustration ??= match ($icon) {
        'message-circle', 'git-branch', 'grid' => 'channels',
        'search' => 'search',
        'bell' => 'notifications',
        'bookmark', 'folder', 'star' => 'collection',
        'file-text', 'send', 'edit-3' => 'feed',
        default => 'empty',
    };
@endphp
<div class="card">
    <div class="card-body text-center py-5">
        @if ($illustration)
            <x-illustration :name="$illustration" size="170" />
        @else
            <i class="fe fe-{{ $icon }} tx-40 text-muted"></i>
        @endif
        <h4 class="mt-3">{{ $title }}</h4>
        @if ($message)<p class="text-muted mb-3" style="max-width: 440px; margin-inline: auto;">{{ $message }}</p>@endif
        {{ $slot }}
    </div>
</div>
