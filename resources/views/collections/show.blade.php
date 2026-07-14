@extends('layouts.mobile-shell')

@section('title', $collection->name . ' - i-Page')

@section('app-bar')
    <a href="{{ route('user.feed') }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-bar-title" style="display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 20px;">{{ $collection->icon }}</span>
        {{ $collection->name }}
    </div>
    <div class="app-bar-actions">
        <button type="button" class="app-bar-icon-btn" onclick="toggleManageChannels()" aria-label="{{ __('Manage channels') }}">
            <i class="bi bi-sliders"></i>
        </button>
    </div>
@endsection

@section('extra-styles')
    .collection-hero {
        background-color: {{ $collection->color }};
        color: white;
        padding: var(--space-6) var(--space-4);
        text-align: center;
    }

    .collection-hero-icon { font-size: 40px; margin-bottom: var(--space-2); }
    .collection-hero-meta { font-size: var(--text-sm); opacity: 0.9; }

    .manage-panel {
        display: none;
        padding: var(--space-4);
        background-color: var(--surface-bg);
        border-bottom: 8px solid var(--surface-bg-secondary);
    }

    .manage-panel.show { display: block; }

    .manage-panel h3 { font-size: var(--text-sm); font-weight: var(--font-weight-bold); margin-bottom: var(--space-3); color: var(--text-primary); }

    .manage-channel-row {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        padding: var(--space-2) 0;
    }

    .manage-channel-avatar {
        width: 36px; height: 36px; border-radius: var(--radius-md);
        background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
        display: flex; align-items: center; justify-content: center;
        color: white; font-weight: var(--font-weight-bold); font-size: var(--text-xs); flex-shrink: 0;
    }

    .manage-channel-name { flex: 1; font-size: var(--text-sm); color: var(--text-primary); font-weight: var(--font-weight-medium); }

    .manage-remove-btn {
        border: none; background: none; color: var(--danger-600); cursor: pointer; padding: var(--space-2);
    }

    /* Post cards (shared) */
    .feed-list { padding: var(--space-4); display: flex; flex-direction: column; gap: var(--space-4); }

    .post-card { background-color: var(--surface-bg); border: 1px solid var(--surface-border); border-radius: var(--radius-lg); overflow: hidden; }
    .post-header { display: flex; align-items: flex-start; gap: var(--space-3); padding: var(--space-4); }
    .post-avatar { width: 44px; height: 44px; border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); flex-shrink: 0; font-size: var(--text-lg); }
    .post-info { flex: 1; min-width: 0; }
    .post-author { font-weight: var(--font-weight-bold); font-size: var(--text-sm); color: var(--text-primary); }
    .post-subtitle { font-size: var(--text-xs); color: var(--text-tertiary); margin-top: 1px; }
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
@endsection

@section('content')
    <div class="collection-hero">
        <div class="collection-hero-icon">{{ $collection->icon }}</div>
        <div class="collection-hero-meta">{{ $collection->channels_count }} {{ __('channels') }} · {{ __('Personal Collection') }}</div>
    </div>

    <div class="manage-panel" id="managePanel">
        <h3>{{ __('Channels in this Collection') }}</h3>
        @forelse($collectionChannels as $channel)
            <div class="manage-channel-row">
                <div class="manage-channel-avatar">{{ substr($channel->name, 0, 1) }}</div>
                <div class="manage-channel-name">{{ $channel->name }}</div>
                <form action="{{ route('collections.channels.remove', [$collection, $channel]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="manage-remove-btn" title="{{ __('Remove') }}">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </form>
            </div>
        @empty
            <p style="font-size: var(--text-sm); color: var(--text-tertiary);">
                {{ __('No channels yet. Add channels from') }}
                <a href="{{ route('user.explore-channels') }}" style="color: var(--primary-600);">{{ __('your subscriptions') }}</a>.
            </p>
        @endforelse
    </div>

    @php
        $avatarPalette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777'];
        $colorFor = fn($seed) => $avatarPalette[crc32($seed) % count($avatarPalette)];
    @endphp

    <div class="feed-list">
        @forelse($posts as $post)
            @php
                $displayName = $post->channel->name ?? __('i-Page');
                $subtitle = $post->channel->brand->name ?? ucfirst(str_replace('_', ' ', $post->post_type ?? 'update'));
                $isPublic = !$post->channel || $post->channel->type === 'public';
                $viewsCount = $post->receipts()->whereNotNull('first_viewed_at')->count();
                $userLiked = $post->reactions()->where('user_id', auth()->id())->where('type', 'like')->exists();
                $likeCount = $post->reactions()->where('type', 'like')->count();
                $commentCount = $post->comments()->approved()->count();
            @endphp
            <article class="post-card">
                <div class="post-header">
                    <div class="post-avatar" style="background-color: {{ $colorFor($displayName) }};">
                        {{ substr($displayName, 0, 1) }}
                    </div>
                    <div class="post-info">
                        <div class="post-author">{{ $displayName }}</div>
                        <div class="post-subtitle">{{ $subtitle }}</div>
                        <div class="post-meta">
                            <span>{{ $post->published_at?->diffForHumans() }}</span>
                            <span>&middot;</span>
                            <i class="bi {{ $isPublic ? 'bi-globe2' : 'bi-lock-fill' }}"></i>
                            <span>{{ $isPublic ? __('Public') : __('Private') }}</span>
                        </div>
                    </div>
                </div>

                @php
                    $postDetailUrl = $isPublic ? route('guest.post-detail', $post->id) : route('posts.show', $post->id);
                @endphp
                <a href="{{ $postDetailUrl }}" style="text-decoration: none; color: inherit; display: block;">
                    @if($post->title)
                        <div class="post-body" style="font-weight: var(--font-weight-bold); padding-bottom: 0;">{{ $post->title }}</div>
                    @endif
                    <div class="post-body">{{ Str::limit($post->body, 280) }}</div>

                    @if($post->image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($post->image_path) }}" alt="" class="post-image">
                    @endif
                </a>

                <div class="post-footer">
                    <form action="{{ route('posts.like', $post->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="post-action {{ $userLiked ? 'liked' : '' }}">
                            <i class="bi {{ $userLiked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }}"></i>
                            <span>{{ $likeCount }}</span>
                        </button>
                    </form>
                    <button type="button" class="post-action" onclick="toggleFeedComments(this)">
                        <i class="bi bi-chat"></i>
                        <span>{{ $commentCount }}</span>
                    </button>
                    <div class="post-views">
                        <i class="bi bi-eye"></i>
                        <span>{{ $viewsCount }}</span>
                    </div>
                </div>

                <div class="feed-comments">
                    @foreach($post->comments()->approved()->latest()->take(3)->get() as $comment)
                        <div class="comment-item">
                            <strong style="color: var(--text-primary);">{{ $comment->user->full_name }}</strong>
                            <p style="margin-top: 2px; color: var(--text-secondary);">{{ $comment->body }}</p>
                        </div>
                    @endforeach
                    <form action="{{ route('posts.comments.store', $post->id) }}" method="POST" class="comment-form">
                        @csrf
                        <input type="text" name="body" placeholder="{{ __('Add a comment...') }}" required>
                        <button type="submit"><i class="bi bi-send"></i></button>
                    </form>
                </div>
            </article>
        @empty
            <div class="empty-feed">
                <i class="bi bi-inbox" style="font-size: 2.5rem; display: block; margin-bottom: var(--space-3); opacity: 0.5;"></i>
                <p>{{ __('No posts from this collection yet') }}</p>
            </div>
        @endforelse

        @if($posts->hasPages())
            <div style="display: flex; justify-content: center; padding-top: var(--space-2);">
                {{ $posts->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    function toggleManageChannels() {
        document.getElementById('managePanel').classList.toggle('show');
    }

    function toggleFeedComments(button) {
        const card = button.closest('.post-card');
        const section = card.querySelector('.feed-comments');
        section.style.display = section.style.display === 'none' || !section.style.display ? 'block' : 'none';
    }
</script>
@endsection
