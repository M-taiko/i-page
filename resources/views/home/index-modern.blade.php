@extends('layouts.app-modern')

@section('title', __('Home'))

@section('content')
<style>
    .channel-card {
        background: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-lg);
        padding: var(--space-4);
        transition: all var(--transition-fast);
        cursor: pointer;
    }

    .channel-card:hover {
        border-color: var(--primary-300);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .channel-avatar {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-lg);
        background: linear-gradient(135deg, var(--primary-600), var(--secondary-600));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        flex-shrink: 0;
    }

    .channel-info {
        flex: 1;
    }

    .channel-name {
        font-size: var(--text-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
    }

    .channel-stats {
        font-size: var(--text-xs);
        color: var(--text-tertiary);
        margin-top: var(--space-1);
    }

    .channel-actions {
        display: flex;
        gap: var(--space-2);
        margin-top: var(--space-3);
    }

    .btn-subscribe {
        flex: 1;
        padding: var(--space-2) var(--space-3);
        border: 1px solid var(--primary-600);
        border-radius: var(--radius-md);
        background: var(--primary-600);
        color: white;
        font-size: var(--text-xs);
        font-weight: var(--font-weight-medium);
        cursor: pointer;
        transition: all var(--transition-fast);
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-1);
    }

    .btn-subscribe:hover {
        background: var(--primary-700);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
    }

    .btn-subscribed {
        background: var(--surface-hover);
        color: var(--primary-600);
        border-color: var(--primary-200);
    }

    .btn-subscribed:hover {
        background: var(--primary-50);
    }

    .btn-notify {
        flex: 1;
        padding: var(--space-2) var(--space-3);
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-md);
        background: var(--surface-bg);
        color: var(--text-primary);
        font-size: var(--text-xs);
        font-weight: var(--font-weight-medium);
        cursor: pointer;
        transition: all var(--transition-fast);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-1);
    }

    .btn-notify:hover {
        background: var(--surface-hover);
        border-color: var(--primary-300);
        color: var(--primary-600);
    }

    .btn-notify.active {
        background: var(--success-50);
        border-color: var(--success-300);
        color: var(--success-600);
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-top">
        <div class="page-header-info">
            <h1>{{ __('Welcome to') }} {{ $organizationModel->name }}</h1>
            <p>{{ __('Discover channels and latest posts') }}</p>
        </div>
        <div class="page-header-actions">
            @if(auth()->check())
                @php
                    $isFollowing = auth()->user()->followedOrganizations()->where('organization_id', $organizationModel->id)->exists();
                @endphp
                <form action="{{ $isFollowing ? route('organizations.unfollow', $organizationModel->id) : route('organizations.follow', $organizationModel->id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn {{ $isFollowing ? 'btn-secondary' : 'btn-primary' }}" style="text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-2); border: none; cursor: pointer;">
                        <i class="bi {{ $isFollowing ? 'bi-check' : 'bi-star' }}"></i>
                        <span>{{ $isFollowing ? __('Following') : __('Follow') }}</span>
                    </button>
                </form>
            @endif
            @can('feed.publish')
            <a href="{{ route('dashboard.feeds.create', session('current_organization_id')) }}" class="btn btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-2);">
                <i class="bi bi-plus-circle"></i>
                <span>{{ __('Create Post') }}</span>
            </a>
            @endcan
        </div>
    </div>
</div>

<!-- Brands Section -->
@if($brands->count() > 0)
<div style="margin-bottom: var(--space-8);">
    <h2 style="font-size: var(--text-lg); font-weight: var(--font-weight-bold); margin-bottom: var(--space-4); color: var(--text-primary);">
        <i class="bi bi-bookmark-star"></i> {{ __('Brands') }}
    </h2>
    <div style="display: flex; flex-wrap: wrap; gap: var(--space-3);">
        @foreach($brands as $brand)
            <div style="display: flex; align-items: center; gap: var(--space-2); padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-full); background-color: var(--surface-bg);">
                <span style="font-size: var(--text-sm); font-weight: var(--font-weight-medium); color: var(--text-primary);">{{ $brand->name }}</span>
                @if(auth()->check())
                    @if(in_array($brand->id, $followedBrandIds ?? []))
                        <form action="{{ route('brands.unfollow', $brand->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" style="border: none; background: none; cursor: pointer; color: var(--success-600); font-size: var(--text-xs); display: inline-flex; align-items: center; gap: 4px;">
                                <i class="bi bi-check-circle-fill"></i> {{ __('Following') }}
                            </button>
                        </form>
                    @else
                        <form action="{{ route('brands.follow', $brand->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" style="border: none; background: none; cursor: pointer; color: var(--primary-600); font-size: var(--text-xs); display: inline-flex; align-items: center; gap: 4px;">
                                <i class="bi bi-plus-circle"></i> {{ __('Follow') }}
                            </button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login') }}" style="color: var(--text-tertiary); font-size: var(--text-xs); text-decoration: none;">
                        <i class="bi bi-lock"></i> {{ __('Follow') }}
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- Channels Section -->
@if($channels->count() > 0)
<div style="margin-bottom: var(--space-8);">
    <h2 style="font-size: var(--text-lg); font-weight: var(--font-weight-bold); margin-bottom: var(--space-4); color: var(--text-primary);">
        <i class="bi bi-chat-dots"></i> {{ __('Available Channels') }}
    </h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--space-4);">
        @foreach($channels as $channel)
        <div class="channel-card">
            <div style="display: flex; gap: var(--space-3); margin-bottom: var(--space-3);">
                <div class="channel-avatar">
                    <i class="bi bi-chat-dots"></i>
                </div>
                <div class="channel-info">
                    <h3 class="channel-name">{{ $channel->name }}</h3>
                    <div class="channel-stats">
                        <span><i class="bi bi-people"></i> {{ $channel->users_count }} members</span>
                        <span style="margin-left: var(--space-2);"><i class="bi bi-chat"></i> {{ $channel->posts_count }} posts</span>
                    </div>
                </div>
            </div>

            @if($channel->description)
            <p style="font-size: var(--text-sm); color: var(--text-secondary); margin: 0 0 var(--space-3) 0; line-height: var(--line-height-relaxed);">
                {{ Str::limit($channel->description, 100) }}
            </p>
            @endif

            <div class="channel-actions">
                <a href="{{ route('dashboard.channels.show', [session('current_organization_id'), $channel->id]) }}" class="btn-subscribe btn-subscribed" style="text-decoration: none; flex: 1;">
                    <i class="bi bi-eye"></i>
                    <span>{{ __('View Posts') }}</span>
                </a>
                @if(in_array($channel->id, $subscribedChannelIds))
                    <button class="btn-notify active" title="Notifications enabled">
                        <i class="bi bi-bell-fill"></i>
                        <span>{{ __('Notified') }}</span>
                    </button>
                @else
                    @if(auth()->check())
                    <form action="{{ route('dashboard.channels.subscribe', [session('current_organization_id'), $channel->id]) }}" method="POST" style="flex: 1;">
                        @csrf
                        <button type="submit" class="btn-subscribe" style="width: 100%; border: none;">
                            <i class="bi bi-plus-lg"></i>
                            <span>{{ __('Subscribe') }}</span>
                        </button>
                    </form>
                    @else
                    <a href="{{ route('login') }}" class="btn-subscribe" style="text-decoration: none; flex: 1; text-align: center;">
                        <i class="bi bi-lock"></i>
                        <span>{{ __('Login to Subscribe') }}</span>
                    </a>
                    @endif
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Posts Section -->
<div>
    <h2 style="font-size: var(--text-lg); font-weight: var(--font-weight-bold); margin-bottom: var(--space-4); color: var(--text-primary);">
        <i class="bi bi-newspaper"></i> {{ __('Latest Posts') }}
    </h2>

    @if ($posts->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-6);">
            @foreach ($posts as $post)
                <x-card-modern style="display: flex; flex-direction: column;">
                    <!-- Post Header -->
                    <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-4);">
                        @if($post->channel)
                            <!-- Channel Post - Show Author -->
                            <div style="width: 44px; height: 44px; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--primary-500), var(--secondary-500)); display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); font-size: var(--text-sm); flex-shrink: 0;">
                                {{ $post->author->initials }}
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h4 style="margin: 0; font-size: var(--text-sm); font-weight: var(--font-weight-semibold); color: var(--text-primary); word-break: break-word;">
                                    {{ $post->author->full_name }}
                                </h4>
                                <small style="color: var(--text-tertiary); display: block;">
                                    {{ $post->published_at?->diffForHumans() ?? __('Just now') }}
                                </small>
                            </div>
                        @elseif($post->organization)
                            <!-- Organization Post - Show Organization -->
                            <a href="{{ route('dashboard.home', $post->organization->id) }}" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: var(--space-3); flex: 1; cursor: pointer;">
                                <div style="width: 44px; height: 44px; border-radius: var(--radius-lg); background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); font-size: var(--text-sm); flex-shrink: 0;">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <h4 style="margin: 0; font-size: var(--text-sm); font-weight: var(--font-weight-semibold); color: var(--text-primary); word-break: break-word;">
                                        {{ $post->organization->name }}
                                    </h4>
                                    <small style="color: var(--text-tertiary); display: block;">
                                        {{ $post->published_at?->diffForHumans() ?? __('Just now') }}
                                    </small>
                                </div>
                            </a>
                        @endif
                    </div>

                    <!-- Channel Badge -->
                    @if ($post->channel)
                        <div style="display: flex; align-items: center; gap: var(--space-1); padding: var(--space-2) var(--space-3); background-color: var(--primary-50); border-radius: var(--radius-md); margin-bottom: var(--space-3); width: fit-content;">
                            <i class="bi bi-chat-dots" style="color: var(--primary-600); font-size: var(--text-xs);"></i>
                            <span style="font-size: var(--text-xs); font-weight: var(--font-weight-medium); color: var(--primary-700);">
                                {{ $post->channel->name }}
                            </span>
                        </div>
                    @endif

                    <!-- Post Content -->
                    <div style="flex: 1; margin-bottom: var(--space-4);">
                        <p style="margin: 0 0 var(--space-3); color: var(--text-primary); line-height: var(--line-height-relaxed); word-break: break-word;">
                            {{ Str::limit($post->body, 200) }}
                        </p>

                        @if ($post->image_path)
                            <img src="{{ Storage::url($post->image_path) }}"
                                alt="Post image"
                                style="width: 100%; height: 200px; object-fit: cover; border-radius: var(--radius-lg);">
                        @endif
                    </div>

                    <!-- Post Actions -->
                    <div style="display: flex; gap: var(--space-3); padding-top: var(--space-4); border-top: 1px solid var(--surface-border);">
                        @php
                            $userLiked = $post->reactions()->where('user_id', auth()->id())->where('type', 'like')->exists();
                            $likeCount = $post->reactions()->where('type', 'like')->count();
                        @endphp
                        <form action="{{ route('posts.like', $post->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: {{ $userLiked ? 'var(--success-600)' : 'var(--text-secondary)' }}; font-size: var(--text-sm); cursor: pointer; display: flex; align-items: center; gap: var(--space-1); transition: color var(--transition-fast);" onmouseover="this.style.color='var(--success-600)'" onmouseout="this.style.color='{{ $userLiked ? 'var(--success-600)' : 'var(--text-secondary)' }}'">
                                <i class="bi {{ $userLiked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }}"></i>
                                <span>{{ $likeCount > 0 ? $likeCount . ' ' . __('Likes') : __('Like') }}</span>
                            </button>
                        </form>
                        <button type="button" onclick="toggleComments(this)" style="background: none; border: none; color: var(--text-secondary); font-size: var(--text-sm); cursor: pointer; display: flex; align-items: center; gap: var(--space-1); transition: color var(--transition-fast);" onmouseover="this.style.color='var(--primary-600)'" onmouseout="this.style.color='var(--text-secondary)'">
                            <i class="bi bi-chat"></i>
                            <span>{{ $post->comments()->approved()->count() }} {{ __('Comments') }}</span>
                        </button>
                    </div>

                    <!-- Comments Section -->
                    <div class="comments-section" style="display: none; margin-top: var(--space-4); padding-top: var(--space-4); border-top: 1px solid var(--surface-border);">
                        <!-- Approved Comments -->
                        @if($post->comments()->approved()->count() > 0)
                        <div style="margin-bottom: var(--space-4);">
                            @foreach($post->comments()->approved()->latest()->get() as $comment)
                            <div style="background: var(--surface-hover); border-radius: var(--radius-md); padding: var(--space-3); margin-bottom: var(--space-2);">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div>
                                        <strong style="font-size: var(--text-sm); color: var(--text-primary);">{{ $comment->user->full_name }}</strong>
                                        <small style="display: block; color: var(--text-tertiary); font-size: var(--text-xs);">{{ $comment->created_at->diffForHumans() }}</small>
                                    </div>
                                    @can('delete', $comment)
                                    <form action="{{ route('posts.comments.destroy', $comment->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: var(--danger-600); cursor: pointer; font-size: var(--text-xs);">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                                <p style="margin: var(--space-2) 0 0 0; font-size: var(--text-sm); color: var(--text-primary);">{{ $comment->body }}</p>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <!-- Comment Form -->
                        <form action="{{ route('posts.comments.store', $post->id) }}" method="POST">
                            @csrf
                            <div style="display: flex; gap: var(--space-2);">
                                <input type="text" name="body" placeholder="Add a comment..." required style="flex: 1; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg); color: var(--text-primary);">
                                <button type="submit" style="padding: var(--space-2) var(--space-4); background-color: var(--primary-600); color: white; border: none; border-radius: var(--radius-md); cursor: pointer; font-weight: var(--font-weight-medium); font-size: var(--text-sm);">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                        </form>

                        <!-- Pending Comments (Admin Only) -->
                        @php
                            $pendingComments = $post->comments()->pending()->get();
                        @endphp
                        @if($pendingComments->count() > 0 && $post->channel->admin_user_id === auth()->id())
                        <div style="margin-top: var(--space-4); padding: var(--space-3); background: var(--warning-50); border-left: 3px solid var(--warning-500); border-radius: var(--radius-md);">
                            <strong style="color: var(--warning-700); font-size: var(--text-sm);">⏳ {{ $pendingComments->count() }} Pending Comments (Admin)</strong>
                            @foreach($pendingComments as $comment)
                            <div style="margin-top: var(--space-2); padding: var(--space-2); background: white; border-radius: var(--radius-sm);">
                                <div style="font-weight: 500; font-size: var(--text-sm); color: var(--text-primary);">{{ $comment->user->full_name }}</div>
                                <p style="margin: var(--space-1) 0; font-size: var(--text-sm); color: var(--text-secondary);">{{ $comment->body }}</p>
                                <div style="display: flex; gap: var(--space-2);">
                                    <form action="{{ route('posts.comments.approve', $comment->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" style="padding: var(--space-1) var(--space-2); background: var(--success-600); color: white; border: none; border-radius: var(--radius-sm); cursor: pointer; font-size: var(--text-xs);">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('posts.comments.reject', $comment->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" style="padding: var(--space-1) var(--space-2); background: var(--danger-600); color: white; border: none; border-radius: var(--radius-sm); cursor: pointer; font-size: var(--text-xs);">
                                            ✕ Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </x-card-modern>
            @endforeach
        </div>

        <!-- Pagination -->
        <div style="display: flex; justify-content: center; padding-top: var(--space-6);">
            {{ $posts->links('pagination::bootstrap-5') }}
        </div>
    @else
        <x-empty-state-modern
            title="{{ __('No Posts Yet') }}"
            message="{{ __('There are no posts in public channels. Be the first to share something!') }}"
            icon="newspaper"
        >
            @can('feed.publish')
            <a href="{{ route('dashboard.feeds.create', session('current_organization_id')) }}" class="btn btn-primary mt-4" style="text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-2);">
                <i class="bi bi-plus-circle"></i>
                <span>{{ __('Create First Post') }}</span>
            </a>
            @endcan
        </x-empty-state-modern>
    @endif
</div>

<style>
.comments-section {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
function toggleComments(button) {
    const card = button.closest('[style*="display: flex"]').closest('div');
    const commentSection = card.querySelector('.comments-section');

    if (commentSection) {
        commentSection.style.display = commentSection.style.display === 'none' ? 'block' : 'none';
    }
}
</script>

@endsection
