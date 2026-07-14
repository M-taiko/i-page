@extends('layouts.app-modern')

@section('title', $channel->name)

@section('content')
<style>
    .post-card {
        background: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-lg);
        padding: var(--space-6);
        transition: all var(--transition-fast);
    }

    .post-card:hover {
        border-color: var(--primary-300);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .post-header {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        margin-bottom: var(--space-4);
    }

    .post-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: var(--font-weight-bold);
        font-size: var(--text-sm);
        flex-shrink: 0;
    }

    .post-meta {
        flex: 1;
    }

    .post-meta-name {
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
        font-size: var(--text-sm);
    }

    .post-meta-time {
        color: var(--text-tertiary);
        font-size: var(--text-xs);
        margin-top: var(--space-1);
    }

    .post-body {
        color: var(--text-primary);
        line-height: var(--line-height-relaxed);
        margin-bottom: var(--space-4);
        font-size: var(--text-sm);
    }

    .post-actions {
        display: flex;
        gap: var(--space-4);
        padding-top: var(--space-4);
        border-top: 1px solid var(--surface-border);
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: var(--space-1);
        background: none;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        font-size: var(--text-sm);
        padding: var(--space-2);
        border-radius: var(--radius-md);
        transition: all var(--transition-fast);
    }

    .action-btn:hover {
        color: var(--primary-600);
        background: var(--primary-50);
    }

    .action-btn.liked {
        color: var(--success-600);
        background: var(--success-50);
    }

    .comments-container {
        margin-top: var(--space-4);
        padding-top: var(--space-4);
        border-top: 1px solid var(--surface-border);
    }

    .comment-item {
        background: var(--surface-hover);
        border-radius: var(--radius-md);
        padding: var(--space-3);
        margin-bottom: var(--space-2);
    }

    .comment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--space-1);
    }

    .comment-author {
        font-weight: var(--font-weight-semibold);
        font-size: var(--text-sm);
        color: var(--text-primary);
    }

    .comment-time {
        font-size: var(--text-xs);
        color: var(--text-tertiary);
    }

    .comment-body {
        font-size: var(--text-sm);
        color: var(--text-primary);
        margin: 0;
    }

    .comment-form {
        display: flex;
        gap: var(--space-2);
        margin-top: var(--space-3);
    }

    .comment-input {
        flex: 1;
        padding: var(--space-2) var(--space-3);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-md);
        font-size: var(--text-sm);
        background-color: var(--surface-bg);
        color: var(--text-primary);
        font-family: inherit;
    }

    .comment-submit {
        padding: var(--space-2) var(--space-4);
        background-color: var(--primary-600);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        cursor: pointer;
        font-weight: var(--font-weight-medium);
        font-size: var(--text-sm);
        transition: all var(--transition-fast);
    }

    .comment-submit:hover {
        background-color: var(--primary-700);
    }

    .channel-header {
        background: linear-gradient(135deg, var(--primary-600), var(--secondary-600));
        border-radius: var(--radius-lg);
        padding: var(--space-8);
        color: white;
        margin-bottom: var(--space-8);
    }

    .channel-title {
        font-size: var(--text-2xl);
        font-weight: var(--font-weight-bold);
        margin: 0 0 var(--space-2) 0;
        color: #ffffff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .channel-subtitle {
        font-size: var(--text-sm);
        color: rgba(255, 255, 255, 0.95);
        margin: 0;
        font-weight: var(--font-weight-medium);
    }

    .channel-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: var(--space-4);
        margin-top: var(--space-4);
    }

    .stat-item {
        background: rgba(255, 255, 255, 0.15);
        border-radius: var(--radius-md);
        padding: var(--space-4);
        text-align: center;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .stat-number {
        font-size: var(--text-xl);
        font-weight: var(--font-weight-bold);
        display: block;
        color: #ffffff;
    }

    .stat-label {
        font-size: var(--text-xs);
        color: rgba(255, 255, 255, 0.9);
        font-weight: var(--font-weight-medium);
        margin-top: var(--space-1);
    }

    .section-title {
        font-size: var(--text-lg);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin-bottom: var(--space-4);
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    .auth-required {
        background: var(--warning-50);
        border: 1px solid var(--warning-300);
        border-radius: var(--radius-md);
        padding: var(--space-4);
        text-align: center;
        color: var(--warning-700);
    }
</style>

<!-- Channel Header -->
<div class="channel-header">
    <div style="display: flex; align-items: center; gap: var(--space-4); margin-bottom: var(--space-4);">
        @if ($channel->logo_path)
            <img src="{{ Storage::url($channel->logo_path) }}" alt="{{ $channel->name }}"
                 style="width: 80px; height: 80px; border-radius: var(--radius-lg); background: rgba(255,255,255,0.1);">
        @else
            <div style="width: 80px; height: 80px; border-radius: var(--radius-lg); background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 32px;">
                <i class="bi bi-chat-dots"></i>
            </div>
        @endif
        <div>
            <h1 class="channel-title">{{ $channel->name }}</h1>
            <p class="channel-subtitle">{{ ucfirst($channel->type) }} {{ __('Channel') }} • {{ ucfirst($channel->audience_profile) }}</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="channel-stats">
        <div class="stat-item">
            <span class="stat-number">{{ $channel->users_count ?? $channel->users->count() }}</span>
            <div class="stat-label">{{ __('Members') }}</div>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ $posts->total() }}</span>
            <div class="stat-label">{{ __('Posts') }}</div>
        </div>
        @if(auth()->check())
            @php
                $subscription = auth()->user()->subscribedChannels()
                    ->where('channel_id', $channel->id)
                    ->first();
                $isSubscribed = $subscription !== null;
                $notificationsEnabled = $isSubscribed && $subscription->pivot->muted_at === null;
            @endphp
            <div class="stat-item" style="grid-column: 1 / -1; display: flex; gap: var(--space-2); flex-wrap: wrap; justify-content: center; align-items: center;">
                @if($isSubscribed)
                    <div style="display: flex; gap: var(--space-2); align-items: center;">
                        <form action="{{ route('dashboard.channels.unsubscribe', [session('current_organization_id'), $channel->id]) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.3); color: white; padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); cursor: pointer; font-size: var(--text-sm); font-weight: var(--font-weight-semibold); transition: all var(--transition-fast);"
                                onmouseover="this.style.background='rgba(255,0,0,0.4)'; this.style.borderColor='rgba(255,0,0,0.5)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.3)'; this.style.borderColor='rgba(255,255,255,0.3)'">
                                <i class="bi bi-check-lg"></i> {{ __('Subscribed') }}
                            </button>
                        </form>
                        <form action="{{ route('dashboard.channels.toggle-notifications', [session('current_organization_id'), $channel->id]) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: {{ $notificationsEnabled ? 'rgba(76,175,80,0.3)' : 'rgba(255,152,0,0.3)' }}; border: 1px solid {{ $notificationsEnabled ? 'rgba(76,175,80,0.5)' : 'rgba(255,152,0,0.5)' }}; color: white; padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); cursor: pointer; font-size: var(--text-sm); font-weight: var(--font-weight-semibold); transition: all var(--transition-fast);"
                                onmouseover="this.style.opacity='0.8'"
                                onmouseout="this.style.opacity='1'">
                                <i class="bi {{ $notificationsEnabled ? 'bi-bell-fill' : 'bi-bell-slash' }}"></i>
                                <span class="hidden-xs">{{ $notificationsEnabled ? __('Notifications On') : __('Notifications Off') }}</span>
                            </button>
                        </form>
                    </div>
                @else
                    <form action="{{ route('dashboard.channels.subscribe', [session('current_organization_id'), $channel->id]) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); cursor: pointer; font-size: var(--text-sm); font-weight: var(--font-weight-semibold); transition: all var(--transition-fast);"
                            onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                            onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                            <i class="bi bi-plus-lg"></i> {{ __('Subscribe') }}
                        </button>
                    </form>
                @endif
            </div>
        @else
            <div class="stat-item">
                <a href="{{ route('login') }}" style="display: inline-block; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); text-decoration: none; font-size: var(--text-sm); font-weight: var(--font-weight-semibold); transition: all var(--transition-fast);"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    <i class="bi bi-lock"></i> {{ __('Login to Subscribe') }}
                </a>
            </div>
        @endif
    </div>

    <!-- Admin Actions (Only for authenticated users with permission) -->
    @auth
        @can('update', $channel)
        <div style="margin-top: var(--space-4); display: flex; gap: var(--space-3);">
            <a href="{{ route('dashboard.channels.edit', [session('current_organization_id'), $channel->id]) }}"
               style="display: inline-flex; align-items: center; gap: var(--space-2); background: rgba(255,255,255,0.2); padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); color: white; text-decoration: none; transition: all var(--transition-fast);"
               onmouseover="this.style.background='rgba(255,255,255,0.3)'"
               onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="bi bi-pencil"></i>
                <span>{{ __('Edit Channel') }}</span>
            </a>
        </div>
        @endcan
    @endauth
</div>

<!-- Posts Section -->
<div>
    <h2 class="section-title">
        <i class="bi bi-newspaper"></i>
        {{ __('Channel Posts') }}
    </h2>

    @forelse ($posts as $post)
        <div class="post-card">
            <!-- Post Header -->
            <div class="post-header">
                <div class="post-avatar" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <i class="bi bi-building"></i>
                </div>
                <div class="post-meta">
                    <a href="{{ route('dashboard.home', $channel->organization_id) }}" style="text-decoration: none; color: inherit; cursor: pointer;">
                        <h4 class="post-meta-name">{{ $post->organization->name ?? $channel->organization->name }}</h4>
                    </a>
                    <div class="post-meta-time">{{ $post->published_at?->diffForHumans() ?? __('Just now') }}</div>
                </div>
            </div>

            <!-- Post Body -->
            <div class="post-body">
                {{ $post->body }}
            </div>

            <!-- Post Image -->
            @if ($post->image_path)
                <img src="{{ Storage::url($post->image_path) }}" alt="Post image"
                     style="width: 100%; height: 300px; object-fit: cover; border-radius: var(--radius-lg); margin-bottom: var(--space-4);">
            @endif

            <!-- Post Actions -->
            <div class="post-actions">
                @php
                    $userLiked = auth()->check() ? $post->reactions()->where('user_id', auth()->id())->where('type', 'like')->exists() : false;
                    $likeCount = $post->reactions()->where('type', 'like')->count();
                @endphp

                @if(auth()->check())
                    <form action="{{ route('posts.like', $post->id) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="action-btn {{ $userLiked ? 'liked' : '' }}">
                            <i class="bi {{ $userLiked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }}"></i>
                            <span>{{ $likeCount > 0 ? $likeCount . ' ' . __('Likes') : __('Like') }}</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="action-btn" title="{{ __('Login to like') }}">
                        <i class="bi bi-hand-thumbs-up"></i>
                        <span>{{ $likeCount > 0 ? $likeCount . ' ' . __('Likes') : __('Like') }}</span>
                    </a>
                @endif

                <button type="button" class="action-btn" onclick="toggleComments(this, 'post-{{ $post->id }}')">
                    <i class="bi bi-chat"></i>
                    <span>{{ $post->comments()->approved()->count() }} {{ __('Comments') }}</span>
                </button>
            </div>

            <!-- Comments Section -->
            <div id="post-{{ $post->id }}" class="comments-container" style="display: none;">
                <!-- Approved Comments -->
                @forelse($post->comments()->approved()->latest()->get() as $comment)
                    <div class="comment-item">
                        <div class="comment-header">
                            <span class="comment-author">{{ $comment->user->full_name }}</span>
                            <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="comment-body">{{ $comment->body }}</p>
                        @can('delete', $comment)
                            <form action="{{ route('posts.comments.destroy', $comment->id) }}" method="POST" style="display: inline; margin-top: var(--space-2);">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: none; border: none; color: var(--danger-600); cursor: pointer; font-size: var(--text-xs);">
                                    <i class="bi bi-trash"></i> {{ __('Delete') }}
                                </button>
                            </form>
                        @endcan
                    </div>
                @empty
                    <p style="text-align: center; color: var(--text-tertiary); padding: var(--space-3);">{{ __('No comments yet') }}</p>
                @endforelse

                <!-- Comment Form -->
                @if(auth()->check())
                    <form action="{{ route('posts.comments.store', $post->id) }}" method="POST" class="comment-form">
                        @csrf
                        <input type="text" name="body" class="comment-input" placeholder="{{ __('Add a comment...') }}" required>
                        <button type="submit" class="comment-submit">
                            <i class="bi bi-send"></i>
                        </button>
                    </form>
                @else
                    <div class="auth-required">
                        <p style="margin: 0; font-size: var(--text-sm);">
                            {{ __('You need to') }} <a href="{{ route('login') }}" style="color: inherit; font-weight: var(--font-weight-semibold); text-decoration: underline;">{{ __('login') }}</a> {{ __('to comment') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <x-empty-state-modern
            title="{{ __('No Posts Yet') }}"
            message="{{ __('This channel has no posts yet. Be the first to share something!') }}"
            icon="newspaper"
        />
    @endforelse

    <!-- Pagination -->
    @if ($posts->hasPages())
        <div style="display: flex; justify-content: center; margin-top: var(--space-6);">
            {{ $posts->links() }}
        </div>
    @endif
</div>

<script>
function toggleComments(button, containerId) {
    const container = document.getElementById(containerId);
    if (container) {
        container.style.display = container.style.display === 'none' ? 'block' : 'none';
        button.style.background = container.style.display === 'none' ? 'none' : 'var(--primary-50)';
        button.style.color = container.style.display === 'none' ? 'var(--text-secondary)' : 'var(--primary-600)';
    }
}
</script>

@endsection
