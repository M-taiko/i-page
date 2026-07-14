@extends('layouts.mobile-shell')

@section('title', $channel->name . ' - i-Page')

@section('app-bar')
    <a href="{{ route('guest.organization-detail', $organization) }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-bar-title" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $channel->name }}</div>
    @auth
        <div class="app-bar-actions">
            <button type="button" class="app-bar-icon-btn" onclick="openAddToCollection('channel', {{ $channel->id }}, '{{ $channel->name }}')" aria-label="{{ __('Add to Collection') }}">
                <i class="bi bi-folder-plus"></i>
            </button>
        </div>
    @endauth
@endsection

@section('extra-styles')
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
            @if($channel->brand) · {{ $channel->brand->name }} @endif
        </div>

        @auth
            @if($isSubscribed)
                <form action="{{ route('dashboard.channels.unsubscribe', [$organization->id, $channel->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="channel-hero-subscribe subscribed">
                        <i class="bi bi-check-lg"></i> {{ __('Subscribed') }}
                    </button>
                </form>
            @else
                <form action="{{ route('dashboard.channels.subscribe', [$organization->id, $channel->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="channel-hero-subscribe subscribe">
                        <i class="bi bi-plus-lg"></i> {{ __('Subscribe') }}
                    </button>
                </form>
            @endif
        @else
            <button type="button" class="channel-hero-subscribe subscribe" onclick="requireSignIn()">
                <i class="bi bi-plus-lg"></i> {{ __('Subscribe') }}
            </button>
        @endauth
    </div>

    <div class="feed-list">
        @forelse($posts as $post)
            @php
                $viewsCount = $post->receipts()->whereNotNull('first_viewed_at')->count();
                $likeCount = $post->reactions()->where('type', 'like')->count();
                $commentCount = $post->comments()->approved()->count();
                $userLiked = auth()->check() && $post->reactions()->where('user_id', auth()->id())->where('type', 'like')->exists();
            @endphp
            <article class="post-card">
                <div class="post-header">
                    <div class="post-avatar" style="background-color: {{ $colorFor($post->author->full_name ?? $channel->name) }};">
                        {{ $post->author->initials ?? substr($channel->name, 0, 1) }}
                    </div>
                    <div class="post-info">
                        <div class="post-author">{{ $post->author->full_name ?? __('i-Page') }}</div>
                        <div class="post-meta">
                            <span>{{ $post->published_at?->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('guest.post-detail', $post->id) }}" style="text-decoration: none; color: inherit; display: block;">
                    @if($post->title)
                        <div class="post-body" style="font-weight: var(--font-weight-bold); padding-bottom: 0;">{{ $post->title }}</div>
                    @endif
                    <div class="post-body">{{ Str::limit($post->body, 280) }}</div>

                    @if($post->image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($post->image_path) }}" alt="" class="post-image">
                    @endif
                </a>

                <div class="post-footer">
                    @auth
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
                    @else
                        <button type="button" class="post-action" onclick="requireSignIn()">
                            <i class="bi bi-hand-thumbs-up"></i>
                            <span>{{ $likeCount }}</span>
                        </button>
                        <button type="button" class="post-action" onclick="requireSignIn()">
                            <i class="bi bi-chat"></i>
                            <span>{{ $commentCount }}</span>
                        </button>
                    @endauth
                    <div class="post-views">
                        <i class="bi bi-eye"></i>
                        <span>{{ $viewsCount }}</span>
                    </div>
                </div>

                @auth
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
                @endauth
            </article>
        @empty
            <div class="empty-feed">
                <i class="bi bi-inbox" style="font-size: 2.5rem; display: block; margin-bottom: var(--space-3); opacity: 0.5;"></i>
                <p>{{ __('No posts in this channel yet') }}</p>
            </div>
        @endforelse

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
<script>
    function requireSignIn() {
        document.getElementById('signinModal').classList.add('show');
    }

    function closeSignInModal() {
        document.getElementById('signinModal').classList.remove('show');
    }

    function toggleFeedComments(button) {
        const card = button.closest('.post-card');
        const section = card.querySelector('.feed-comments');
        section.style.display = section.style.display === 'none' || !section.style.display ? 'block' : 'none';
    }
</script>
@endsection
