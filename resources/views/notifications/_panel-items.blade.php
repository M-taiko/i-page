{{-- Items of the app-bar notification panel. Expects $items (Notification collection). --}}
@php
    $iconFor = fn (string $type) => match (true) {
        str_starts_with($type, 'post.published.manage') => ['bi-shield-check', '#4557f5'],
        str_starts_with($type, 'post.published') => ['bi-megaphone-fill', '#7c3aed'],
        str_starts_with($type, 'channel.role') => ['bi-person-badge-fill', '#059669'],
        str_contains($type, 'invite') => ['bi-envelope-paper-heart-fill', '#db2777'],
        str_contains($type, 'workflow') => ['bi-check2-square', '#d97706'],
        $type === 'like' => ['bi-heart-fill', '#e11d48'],
        default => ['bi-bell-fill', '#64748b'],
    };
@endphp
@forelse ($items as $n)
    @php
        [$icon, $color] = $iconFor((string) $n->type);
        $data = is_array($n->data) ? $n->data : (json_decode((string) $n->data, true) ?: []);
        $message = $data['message'] ?? $data['title'] ?? __('New notification');
        $link = $data['link'] ?? route('user.notifications');
    @endphp
    <a href="{{ $link }}" class="np-item {{ $n->read_at ? '' : 'is-unread' }}" data-id="{{ $n->id }}" data-read-url="{{ route('user.notifications.read', $n->id) }}">
        <span class="np-ic" style="--c: {{ $color }};"><i class="bi {{ $icon }}"></i></span>
        <span class="np-body">
            <span class="np-msg">{{ \Illuminate\Support\Str::limit($message, 110) }}</span>
            <span class="np-time">{{ $n->created_at?->diffForHumans() }}</span>
        </span>
        @unless ($n->read_at)<span class="np-dot" aria-label="{{ __('Unread') }}"></span>@endunless
    </a>
@empty
    <div class="np-empty">
        <x-illustration name="notifications" size="110" />
        <strong>{{ __('You\'re all caught up') }}</strong>
        <span>{{ __('New posts and updates from your channels will show up here.') }}</span>
    </div>
@endforelse
