@extends('layouts.mobile-shell')

@section('title', $channel->name . ' - i-Page')
@section('meta_description', \Illuminate\Support\Str::limit($channel->description ?? __(':channel — updates from :org on i-Page.', ['channel' => $channel->name, 'org' => $organization->name]), 180))
@section('seo_schema', json_encode(\App\Support\SeoSchema::channel($channel, $organization), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))

@section('app-bar')
    <a href="{{ route('guest.organization-detail', $organization) }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-bar-title" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $channel->name }}</div>
    @auth
        <div class="app-bar-actions">
            <form action="{{ route('collections.favorite', $channel->id) }}" method="POST">
                @csrf
                <button type="submit" class="app-bar-icon-btn" style="{{ ($isFavorited ?? false) ? 'color: #f59e0b;' : '' }}" aria-label="{{ __('Favorite') }}">
                    <i class="bi {{ ($isFavorited ?? false) ? 'bi-star-fill' : 'bi-star' }}"></i>
                </button>
            </form>
            <button type="button" class="app-bar-icon-btn" onclick="openAddToCollection('channel', {{ $channel->id }}, '{{ $channel->name }}')" aria-label="{{ __('Add to Collection') }}">
                <i class="bi bi-folder-plus"></i>
            </button>
        </div>
    @endauth
@endsection

@section('extra-styles')
    @include('partials.feed._styles')
    .channel-hero {
        background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
        color: white;
        padding: var(--space-6) var(--space-4);
        text-align: center;
    }

    .channel-hero-icon {
        width: 64px; height: 64px; border-radius: var(--radius-xl);
        background: rgba(255,255,255,0.2);
        display: flex; align-items: center; justify-content: center;
        font-size: 28px; margin: 0 auto var(--space-3);
    }

    .channel-hero-name { font-size: var(--text-xl); font-weight: var(--font-weight-bold); margin-bottom: var(--space-1); }
    .channel-hero-meta { font-size: var(--text-sm); opacity: 0.9; margin-bottom: var(--space-4); }

    .channel-hero-subscribe {
        border: none;
        border-radius: var(--radius-full);
        padding: var(--space-2) var(--space-6);
        font-weight: var(--font-weight-semibold);
        font-size: var(--text-sm);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
    }

    .channel-hero-subscribe.subscribe { background-color: white; color: var(--primary-700); }
    .channel-hero-subscribe.subscribed { background-color: rgba(255,255,255,0.2); color: white; }
    .channel-hero-subscribe:disabled { opacity: .85; cursor: default; }
    .channel-hero-actions { display: inline-flex; align-items: center; gap: 8px; }
    .channel-hero-actions form { margin: 0; }
    .channel-hero-bell { width: 40px; height: 40px; border-radius: 50%; border: 0; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-size: 17px; background: rgba(255,255,255,.2); color: #fff; transition: background .2s, color .2s, transform .15s; }
    .channel-hero-bell:hover { transform: translateY(-1px); }
    .channel-hero-bell.is-on { background: #fff; color: #f59e0b; }
    .channel-hero-bell.ring i { animation: chRing .7s ease; display: inline-block; transform-origin: 50% 10%; }
    @keyframes chRing { 20% { transform: rotate(16deg); } 40% { transform: rotate(-14deg); } 60% { transform: rotate(8deg); } 80% { transform: rotate(-4deg); } }
    @media (prefers-reduced-motion: reduce) { .channel-hero-bell.ring i { animation: none; } }

    /* Instagram-style "stories" strip — all sibling channels of this org */
    .stories-strip {
        display: flex;
        gap: var(--space-2);
        overflow-x: auto;
        scrollbar-width: none;
        padding: var(--space-4);
        background-color: var(--surface-bg);
        border-bottom: 8px solid var(--surface-bg-secondary);
    }

    .stories-strip::-webkit-scrollbar { display: none; }

    .story-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        flex-shrink: 0;
        width: 84px;
        border-radius: 14px;
        padding: 4px 2px;
        outline-offset: 2px;
    }

    .story-item:hover .story-ring { transform: translateY(-2px); }
    .story-item:focus-visible { outline: 2px solid var(--primary-500); }

    .story-ring {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        padding: 3px;
        background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: transform 0.15s ease;
    }

    .story-item:active .story-ring { transform: scale(0.94); }

    .story-item.current .story-ring {
        background: var(--surface-border);
    }

    .story-avatar {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background-color: var(--surface-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: var(--font-weight-bold);
        color: white;
        border: 2.5px solid var(--surface-bg);
    }

    .story-name {
        font-size: 12px;
        font-weight: var(--font-weight-semibold);
        line-height: 1.3;
        color: var(--text-primary);
        text-align: center;
        max-width: 84px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        overflow-wrap: anywhere;
        min-height: 2.6em;
    }

    .story-item.current .story-name { color: var(--primary-600); }

    /* Post cards (shared) */
    .feed-list { padding: var(--space-4); display: flex; flex-direction: column; gap: var(--space-4); }
    .post-card { background-color: var(--surface-bg); border: 1px solid var(--surface-border); border-radius: var(--radius-lg); overflow: hidden; }
    .post-header { display: flex; align-items: flex-start; gap: var(--space-3); padding: var(--space-4); }
    .post-avatar { width: 44px; height: 44px; border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); flex-shrink: 0; font-size: var(--text-lg); }
    .post-info { flex: 1; min-width: 0; }
    .post-author { font-weight: var(--font-weight-bold); font-size: var(--text-sm); color: var(--text-primary); }
    .post-meta { font-size: var(--text-xs); color: var(--text-tertiary); margin-top: 2px; display: flex; align-items: center; gap: 4px; }
    .post-body { padding: 0 var(--space-4) var(--space-3); font-size: var(--text-sm); line-height: var(--line-height-relaxed); color: var(--text-primary); }
    .post-image { width: 100%; max-height: 320px; object-fit: cover; display: block; background-color: var(--surface-hover); }
    .post-footer { display: flex; align-items: center; gap: var(--space-4); padding: var(--space-3) var(--space-4); border-top: 1px solid var(--surface-border); }
    .post-action { display: flex; align-items: center; gap: 6px; cursor: pointer; background: none; border: none; color: var(--text-secondary); font-size: var(--text-xs); font-weight: var(--font-weight-medium); padding: 0; }
    .post-action i { font-size: var(--text-base); }
    .post-action.liked { color: var(--primary-600); }
    .post-views { margin-inline-start: auto; display: flex; align-items: center; gap: 6px; color: var(--text-tertiary); font-size: var(--text-xs); }
    .feed-comments { display: none; padding: 0 var(--space-4) var(--space-4); border-top: 1px solid var(--surface-border); padding-top: var(--space-3); }
    .comment-item { background: var(--surface-hover); border-radius: var(--radius-md); padding: var(--space-2) var(--space-3); margin-bottom: var(--space-2); font-size: var(--text-xs); }
    .comment-form { display: flex; gap: var(--space-2); margin-top: var(--space-2); }
    .comment-form input { flex: 1; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-full); font-size: var(--text-xs); background-color: var(--surface-bg-secondary); color: var(--text-primary); }
    .comment-form button { width: 34px; height: 34px; border-radius: var(--radius-full); background-color: var(--primary-600); color: white; border: none; cursor: pointer; flex-shrink: 0; }

    .empty-feed { text-align: center; padding: var(--space-8) var(--space-4); color: var(--text-secondary); }

    /* Sign-in modal */
    .signin-modal-overlay { display: none; position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); z-index: 50; align-items: center; justify-content: center; padding: var(--space-4); }
    .signin-modal-overlay.show { display: flex; }
    .signin-modal { background-color: var(--surface-bg); border-radius: var(--radius-xl); padding: var(--space-8) var(--space-6); max-width: 340px; width: 100%; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
    .signin-modal i.lock-icon { font-size: 2.5rem; color: var(--primary-600); display: block; margin-bottom: var(--space-3); }
    .signin-modal h3 { font-size: var(--text-lg); margin-bottom: var(--space-2); color: var(--text-primary); }
    .signin-modal p { color: var(--text-secondary); margin-bottom: var(--space-5); font-size: var(--text-sm); }
    .signin-modal-actions { display: flex; gap: var(--space-3); }
    .signin-modal-actions button, .signin-modal-actions a { flex: 1; padding: var(--space-3); border-radius: var(--radius-md); font-weight: var(--font-weight-medium); text-decoration: none; font-size: var(--text-sm); cursor: pointer; border: none; }
    .signin-modal-actions .btn-cancel { background-color: var(--surface-hover); color: var(--text-primary); }
    .signin-modal-actions .btn-signin { background-color: var(--primary-600); color: white; display: flex; align-items: center; justify-content: center; }
@endsection

@section('content')
    @php
        $avatarPalette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777'];
        $colorFor = fn($seed) => $avatarPalette[crc32($seed) % count($avatarPalette)];
    @endphp

    <div class="channel-hero">
        <div class="channel-hero-icon"><i class="bi bi-chat-dots"></i></div>
        <div class="channel-hero-name">{{ $channel->name }}</div>
        <div class="channel-hero-meta">
            {{ $channel->users_count }} {{ __('members') }}
            · {{ $organization->name }}
        </div>

        @auth
            <div class="channel-hero-actions">
                @if ($isSubscribed)
                    <form action="{{ route('subscriptions.destroy', $channel->id) }}" method="POST"
                          onsubmit="return confirm('{{ __('Unsubscribe from :name?', ['name' => $channel->name]) }}')">
                        @csrf
                        <button type="submit" class="channel-hero-subscribe subscribed" data-tip="{{ __('Subscribed') }}" data-tip-sub="{{ __('Tap to unsubscribe') }}">
                            <i class="bi bi-check-lg"></i> {{ __('Subscribed') }}
                        </button>
                    </form>
                    @include('channels._bell', ['class' => 'channel-hero-bell'])
                @elseif ($isPending)
                    <button type="button" class="channel-hero-subscribe subscribed" disabled>
                        <i class="bi bi-hourglass-split"></i> {{ __('Request sent') }}
                    </button>
                @else
                    <form action="{{ route('subscriptions.store', $channel->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="channel-hero-subscribe subscribe">
                            <i class="bi bi-{{ $channel->type === 'private' ? 'lock' : 'plus-lg' }}"></i> {{ $channel->type === 'private' ? __('Request to join') : __('Subscribe') }}
                        </button>
                    </form>
                @endif
            </div>
        @else
            <button type="button" class="channel-hero-subscribe subscribe" onclick="requireSignIn()">
                <i class="bi bi-plus-lg"></i> {{ __('Subscribe') }}
            </button>
        @endauth
    </div>

    @if($subChannels->isNotEmpty())
        <div class="stories-strip">
            @if($parentChannel)
                <a href="{{ route('guest.channel-detail', [$organization, $parentChannel->slug]) }}" class="story-item"
                   data-tip="{{ $parentChannel->name }}" data-tip-sub="{{ __('Back to the parent channel') }}">
                    <div class="story-ring">
                        <div class="story-avatar" style="background-color: {{ $colorFor($parentChannel->name) }};">
                            <i class="bi bi-arrow-up-left" style="font-size: 20px;"></i>
                        </div>
                    </div>
                    <div class="story-name">{{ $parentChannel->name }}</div>
                </a>
            @endif
            @foreach($subChannels as $sub)
                <a href="{{ route('guest.channel-detail', [$organization, $sub->slug]) }}"
                   class="story-item {{ $sub->id === $channel->id ? 'current' : '' }}"
                   @if ($sub->id === $channel->id) aria-current="page" @endif
                   data-tip="{{ $sub->name }}"
                   data-tip-sub="{{ $sub->id === $channel->id ? __('You are here') : trim(trans_choice(':count member|:count members', $sub->users_count ?? 0) . ($sub->child_channels_count ? ' · ' . trans_choice(':count sub-channel|:count sub-channels', $sub->child_channels_count) : '')) }}">
                    <div class="story-ring">
                        <div class="story-avatar" style="background-color: {{ $colorFor($sub->name) }};">
                            {{ mb_substr($sub->name, 0, 1) }}
                        </div>
                    </div>
                    <div class="story-name">{{ $sub->name }}</div>
                </a>
            @endforeach
        </div>
    @elseif($orgChannels->count() > 1)
        <div class="stories-strip">
            @foreach($orgChannels as $orgChannel)
                <a href="{{ route('guest.channel-detail', [$organization, $orgChannel->slug]) }}"
                   class="story-item {{ $orgChannel->id === $channel->id ? 'current' : '' }}"
                   @if ($orgChannel->id === $channel->id) aria-current="page" @endif
                   data-tip="{{ $orgChannel->name }}"
                   data-tip-sub="{{ $orgChannel->id === $channel->id ? __('You are here') : trans_choice(':count member|:count members', $orgChannel->users_count ?? 0) }}">
                    <div class="story-ring">
                        <div class="story-avatar" style="background-color: {{ $colorFor($orgChannel->name) }};">
                            {{ mb_substr($orgChannel->name, 0, 1) }}
                        </div>
                    </div>
                    <div class="story-name">{{ $orgChannel->name }}</div>
                </a>
            @endforeach
        </div>
    @endif

    @include('channels._highlights', ['channel' => $channel])

    @php
        $ps = $postSettings ?? \App\Models\Channel::POST_SETTING_DEFAULTS;
        $psStyle = $ps['card_style'] === 'post' ? null : $ps['card_style'];
    @endphp
    <style>
        .feed-list.layout-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: var(--space-3); align-items: start; }
        .feed-list.layout-compact .post-card { border-radius: var(--radius-md); }
        .feed-list.layout-compact .post-header { padding: var(--space-3) var(--space-3) var(--space-2); }
        .feed-list.layout-compact .pf-body { font-size: 13px; }
        .post-from { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 1px 8px; border-radius: 999px; background: rgba(98,89,202,.12); color: #6259ca; text-decoration: none; }
        .post-pinned { font-size: 11px; font-weight: 800; color: #6259ca; }
    </style>
    <div class="feed feed-list layout-{{ $ps['layout'] }}">
        @if ($posts->isNotEmpty())
            @include('guest.partials._feed-cards', ['posts' => $posts, 'context' => $channel, 'display' => [
                'show_author' => $ps['show_author'], 'show_date' => $ps['show_date'], 'show_reactions' => $ps['show_reactions'],
                'show_comments' => $ps['show_comments'], 'show_views' => $ps['show_views'], 'excerpt' => $ps['excerpt'], 'card_style' => $psStyle,
            ]])
        @else
            <div class="empty-feed">
                <x-illustration name="empty" size="150" style="margin-bottom: var(--space-3);" />
                <p>{{ __('No posts in this channel yet') }}</p>
            </div>
        @endif

        @if($posts->hasPages())
            <div style="display: flex; justify-content: center; padding-top: var(--space-2);">
                {{ $posts->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
@endsection

@section('modals')
    <div class="signin-modal-overlay" id="signinModal" onclick="if(event.target===this) closeSignInModal()">
        <div class="signin-modal">
            <i class="bi bi-lock-fill lock-icon"></i>
            <h3>{{ __('Sign in required') }}</h3>
            <p>{{ __('Create a free account to subscribe and interact with posts.') }}</p>
            <div class="signin-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeSignInModal()">{{ __('Cancel') }}</button>
                <a href="{{ route('login') }}" class="btn-signin">{{ __('Sign In') }}</a>
            </div>
        </div>
    </div>

    @include('collections._add-to-collection-modal')
@endsection

@section('scripts')
@include('partials.feed._script')
<script>
    function requireSignIn() {
        document.getElementById('signinModal').classList.add('show');
    }

    function closeSignInModal() {
        document.getElementById('signinModal').classList.remove('show');
    }

</script>
@endsection
