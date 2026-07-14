@extends('layouts.mobile-shell')

@section('title', ($post->title ?? Str::limit($post->body, 50)) . ' - i-Page')

@section('app-bar')
    <button type="button" class="app-bar-icon-btn" onclick="history.back()" aria-label="{{ __('Back') }}">
        <i class="bi bi-arrow-left"></i>
    </button>
    <div class="app-bar-title" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
        {{ $post->channel->name ?? ($post->organization->name ?? 'Post') }}
    </div>
    <div class="app-bar-actions">
        <button type="button" class="app-bar-icon-btn" onclick="sharePost()" aria-label="{{ __('Share') }}">
            <i class="bi bi-share"></i>
        </button>
    </div>
@endsection

@section('extra-styles')
    .post-detail-card { background-color: var(--surface-bg); }

    .post-header { display: flex; align-items: flex-start; gap: var(--space-3); padding: var(--space-4); }
    .post-avatar { width: 46px; height: 46px; border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); flex-shrink: 0; font-size: var(--text-lg); }
    .post-info { flex: 1; min-width: 0; }
    .post-author { font-weight: var(--font-weight-bold); font-size: var(--text-base); color: var(--text-primary); }
    .post-subtitle { font-size: var(--text-xs); color: var(--text-tertiary); margin-top: 1px; }
    .post-meta { font-size: var(--text-xs); color: var(--text-tertiary); margin-top: 2px; display: flex; align-items: center; gap: 4px; }

    .post-title-text { padding: 0 var(--space-4); font-size: var(--text-lg); font-weight: var(--font-weight-bold); color: var(--text-primary); margin-bottom: var(--space-2); }
    .post-body-text { padding: 0 var(--space-4) var(--space-4); font-size: var(--text-sm); line-height: var(--line-height-relaxed); color: var(--text-primary); white-space: pre-line; }
    .post-image { width: 100%; max-height: 420px; object-fit: cover; display: block; background-color: var(--surface-hover); }

    .post-footer { display: flex; align-items: center; gap: var(--space-5); padding: var(--space-3) var(--space-4); border-top: 1px solid var(--surface-border); border-bottom: 8px solid var(--surface-bg-secondary); }
    .post-action { display: flex; align-items: center; gap: 6px; cursor: pointer; background: none; border: none; color: var(--text-secondary); font-size: var(--text-sm); font-weight: var(--font-weight-medium); padding: 0; }
    .post-action i { font-size: var(--text-lg); }
    .post-action.liked { color: var(--primary-600); }
    .post-views { margin-inline-start: auto; display: flex; align-items: center; gap: 6px; color: var(--text-tertiary); font-size: var(--text-sm); }

    .comments-section { padding: var(--space-4); }
    .comments-title { font-size: var(--text-sm); font-weight: var(--font-weight-bold); color: var(--text-primary); margin-bottom: var(--space-3); }

    .comment-item { display: flex; gap: var(--space-3); margin-bottom: var(--space-3); }
    .comment-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-500), var(--secondary-500)); display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); font-size: var(--text-xs); flex-shrink: 0; }
    .comment-bubble { background-color: var(--surface-bg); border: 1px solid var(--surface-border); border-radius: var(--radius-lg); padding: var(--space-3); flex: 1; }
    .comment-author { font-weight: var(--font-weight-semibold); font-size: var(--text-xs); color: var(--text-primary); }
    .comment-body { font-size: var(--text-sm); color: var(--text-primary); margin-top: 2px; }
    .comment-time { font-size: 11px; color: var(--text-tertiary); margin-top: 4px; }

    .comment-form { display: flex; gap: var(--space-2); margin-top: var(--space-4); }
    .comment-form input { flex: 1; padding: var(--space-3) var(--space-4); border: 1px solid var(--surface-border); border-radius: var(--radius-full); font-size: var(--text-sm); background-color: var(--surface-bg-secondary); color: var(--text-primary); }
    .comment-form button { width: 42px; height: 42px; border-radius: var(--radius-full); background-color: var(--primary-600); color: white; border: none; cursor: pointer; flex-shrink: 0; }

    .empty-comments { text-align: center; padding: var(--space-6) 0; color: var(--text-tertiary); font-size: var(--text-sm); }
    .pending-note { font-size: var(--text-xs); color: var(--text-tertiary); text-align: center; margin-top: var(--space-3); }

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

    .toast-copied {
        position: fixed; bottom: 90px; left: 50%; transform: translateX(-50%) translateY(20px);
        background-color: var(--text-primary); color: var(--surface-bg); padding: 10px 20px;
        border-radius: 999px; font-size: var(--text-sm); opacity: 0; transition: all 0.25s ease;
        z-index: 100; pointer-events: none;
    }
    .toast-copied.show { opacity: 1; transform: translateX(-50%) translateY(0); }
@endsection

@section('content')
    @php
        $avatarPalette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777'];
        $colorFor = fn($seed) => $avatarPalette[crc32($seed) % count($avatarPalette)];
        $displayName = $post->channel->name ?? $post->organization->name ?? __('i-Page');
        $subtitle = $post->channel?->brand->name ?? ucfirst(str_replace('_', ' ', $post->post_type ?? 'update'));
        $viewsCount = $post->receipts()->whereNotNull('first_viewed_at')->count();
        $likeCount = $post->reactions()->where('type', 'like')->count();
        $userLiked = auth()->check() && $post->reactions()->where('user_id', auth()->id())->where('type', 'like')->exists();
    @endphp

    <article class="post-detail-card">
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
                    <i class="bi bi-globe2"></i>
                    <span>{{ __('Public') }}</span>
                </div>
            </div>
        </div>

        @if($post->title)
            <div class="post-title-text">{{ $post->title }}</div>
        @endif
        <div class="post-body-text">{{ $post->body }}</div>

        @if($post->image_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($post->image_path) }}" alt="" class="post-image">
        @endif

        <div class="post-footer">
            @auth
                <form action="{{ route('posts.like', $post->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="post-action {{ $userLiked ? 'liked' : '' }}">
                        <i class="bi {{ $userLiked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }}"></i>
                        <span>{{ $likeCount }}</span>
                    </button>
                </form>
            @else
                <button type="button" class="post-action" onclick="requireSignIn()">
                    <i class="bi bi-hand-thumbs-up"></i>
                    <span>{{ $likeCount }}</span>
                </button>
            @endauth
            <div class="post-action" style="cursor: default;">
                <i class="bi bi-chat"></i>
                <span>{{ $comments->count() }}</span>
            </div>
            <button type="button" class="post-action" onclick="sharePost()">
                <i class="bi bi-share"></i>
            </button>
            <div class="post-views">
                <i class="bi bi-eye"></i>
                <span>{{ $viewsCount }}</span>
            </div>
        </div>
    </article>

    <div class="comments-section">
        <div class="comments-title">{{ __('Comments') }} ({{ $comments->count() }})</div>

        @forelse($comments as $comment)
            <div class="comment-item">
                <div class="comment-avatar">{{ $comment->user->initials ?? '?' }}</div>
                <div class="comment-bubble">
                    <div class="comment-author">{{ $comment->user->full_name ?? __('User') }}</div>
                    <div class="comment-body">{{ $comment->body }}</div>
                    <div class="comment-time">{{ $comment->created_at->diffForHumans() }}</div>
                </div>
            </div>
        @empty
            <div class="empty-comments">{{ __('No comments yet. Be the first to comment.') }}</div>
        @endforelse

        @auth
            <form action="{{ route('posts.comments.store', $post->id) }}" method="POST" class="comment-form">
                @csrf
                <input type="text" name="body" placeholder="{{ __('Add a comment...') }}" required>
                <button type="submit"><i class="bi bi-send"></i></button>
            </form>
            <p class="pending-note">{{ __('Your comment will appear after moderator approval.') }}</p>
        @else
            <button type="button" class="comment-form" style="width: 100%; border: 1px dashed var(--surface-border); border-radius: var(--radius-full); padding: var(--space-3); background: none; color: var(--text-tertiary); cursor: pointer;" onclick="requireSignIn()">
                <i class="bi bi-lock"></i> {{ __('Sign in to comment') }}
            </button>
        @endauth
    </div>

    <div class="toast-copied" id="copyToast">{{ __('Link copied!') }}</div>
@endsection

@section('modals')
    <div class="signin-modal-overlay" id="signinModal" onclick="if(event.target===this) closeSignInModal()">
        <div class="signin-modal">
            <i class="bi bi-lock-fill lock-icon"></i>
            <h3>{{ __('Sign in required') }}</h3>
            <p>{{ __('Create a free account to like and comment on posts.') }}</p>
            <div class="signin-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeSignInModal()">{{ __('Cancel') }}</button>
                <a href="{{ route('login') }}" class="btn-signin">{{ __('Sign In') }}</a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function requireSignIn() {
        document.getElementById('signinModal').classList.add('show');
    }

    function closeSignInModal() {
        document.getElementById('signinModal').classList.remove('show');
    }

    function sharePost() {
        const url = window.location.href;
        if (navigator.share) {
            navigator.share({ title: document.title, url: url }).catch(() => {});
            return;
        }
        navigator.clipboard?.writeText(url);
        const toast = document.getElementById('copyToast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2000);
    }
</script>
@endsection
