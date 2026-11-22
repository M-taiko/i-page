{{-- Pin / edit / delete menu for a channel or tenant post, shown only to people allowed to do it (ADR-014/016). Expects $post. --}}
@php
    $mChannel = $post->channel;
    $mUser = auth()->user();
    $mTenant = $post->isTenantPost();
    if ($mTenant) {
        $mCanEdit = $mCanDelete = $mUser && \App\Models\Channel::isOrgManager($mUser, $post->organization_id);
    } else {
        $mCanEdit = $mChannel && $mUser && $mChannel->userCan($mUser, 'edit');
        $mCanDelete = $mChannel && $mUser && $mChannel->userCan($mUser, 'delete');
    }
@endphp
@if ($mCanEdit || $mCanDelete)
@once
<style>
    .pm { position: relative; margin-inline-start: auto; }
    .pm > summary { list-style: none; cursor: pointer; width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; line-height: 1; color: inherit; opacity: .7; }
    .pm > summary::-webkit-details-marker { display: none; }
    .pm > summary:hover, .pm[open] > summary { opacity: 1; background: rgba(100,116,139,.14); }
    .pm-menu { position: absolute; top: 36px; inset-inline-end: 0; z-index: 30; min-width: 190px; padding: 6px; border-radius: 14px; background: #fff; color: #0f172a; box-shadow: 0 14px 40px rgba(15,23,42,.18); border: 1px solid rgba(15,23,42,.08); }
    [data-theme="dark"] .pm-menu, .dark-theme .pm-menu { background: #1e2740; color: #e5e9f5; border-color: rgba(255,255,255,.08); }
    .pm-menu a, .pm-menu button { display: flex; align-items: center; gap: 10px; width: 100%; padding: 9px 12px; border: 0; border-radius: 10px; background: none; color: inherit !important; font: inherit; font-size: 14px; text-align: start; cursor: pointer; text-decoration: none !important; }
    .pm-menu a:hover, .pm-menu button:hover { background: rgba(100,116,139,.12); }
    .pm-menu .pm-danger { color: #dc2626 !important; }
    .pm-menu form { margin: 0; }
</style>
@endonce
<details class="pm">
    <summary aria-label="{{ __('Post actions') }}"><svg width="18" height="18" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><circle cx="8" cy="3" r="1.6"/><circle cx="8" cy="8" r="1.6"/><circle cx="8" cy="13" r="1.6"/></svg></summary>
    <div class="pm-menu">
        @if ($mTenant)
            <form action="{{ route('organizations.posts.pin', $post->id) }}" method="POST">
                @csrf
                <button type="submit">{{ $post->isPinned() ? '✖️ ' . __('Unpin highlight') : '📌 ' . __('Pin on the tenant page and its channels') }}</button>
            </form>
            <a href="{{ route('organizations.posts.edit', $post->id) }}">✏️ {{ __('Edit post') }}</a>
            <form action="{{ route('organizations.posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('{{ __('Delete this post everywhere it appears? This cannot be undone.') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="pm-danger">🗑️ {{ __('Delete post') }}</button>
            </form>
        @else
        @if ($mCanEdit)
            @php
                $mPinned = $post->isPinned();
                $mScope = $post->pinScope();
                $mHasChildren = $mChannel->childChannels()->exists();
            @endphp
            @foreach (['channel' => __('Pin in this channel only'), 'all' => __('Pin here + all sub-channels')] as $scopeKey => $scopeLabel)
                @continue($scopeKey === 'all' && ! $mHasChildren && ! ($mPinned && $mScope === 'all'))
                @continue($mPinned && $mScope === $scopeKey)
                <form action="{{ route('tenant.channels.posts.pin', [$mChannel->id, $post->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="scope" value="{{ $scopeKey }}">
                    <button type="submit">📌 {{ $mPinned ? __('Change to') . ': ' : '' }}{{ $scopeLabel }}</button>
                </form>
            @endforeach
            @if ($mPinned)
                <form action="{{ route('tenant.channels.posts.pin', [$mChannel->id, $post->id]) }}" method="POST">
                    @csrf
                    <button type="submit">✖️ {{ __('Unpin highlight') }}</button>
                </form>
            @endif
            <a href="{{ route('tenant.channels.posts.edit', [$mChannel->id, $post->id]) }}">✏️ {{ __('Edit post') }}</a>
        @endif
        @if ($mCanDelete)
            <form action="{{ route('tenant.channels.posts.destroy', [$mChannel->id, $post->id]) }}" method="POST" onsubmit="return confirm('{{ __('Delete this post? This cannot be undone.') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="pm-danger">🗑️ {{ __('Delete post') }}</button>
            </form>
        @endif
        @endif
    </div>
</details>
@endif
