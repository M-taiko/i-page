@extends('layouts.mobile-shell')

@section('title', $organization->name . ' - i-Page')

@section('app-bar')
    <a href="{{ route('guest.home') }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-bar-title" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $organization->name }}</div>
    <div class="app-bar-actions">
        <a href="{{ route('guest.search-organizations') }}" class="app-bar-icon-btn" aria-label="{{ __('Search') }}">
            <i class="bi bi-search"></i>
        </a>
    </div>
@endsection

@section('extra-styles')
    /* Org header */
    .org-hero {
        background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
        color: white;
        padding: var(--space-6) var(--space-4) var(--space-5);
        text-align: center;
    }

    .org-hero-avatar {
        width: 72px;
        height: 72px;
        border-radius: var(--radius-xl);
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: var(--font-weight-bold);
        font-size: var(--text-3xl);
        margin: 0 auto var(--space-3);
    }

    .org-hero-name { font-size: var(--text-xl); font-weight: var(--font-weight-bold); margin-bottom: var(--space-1); }
    .org-hero-desc { font-size: var(--text-sm); opacity: 0.9; margin-bottom: var(--space-4); }

    .org-hero-stats {
        display: flex;
        justify-content: center;
        gap: var(--space-6);
        margin-bottom: var(--space-4);
    }

    .org-hero-stat { text-align: center; }
    .org-hero-stat-value { font-size: var(--text-lg); font-weight: var(--font-weight-bold); }
    .org-hero-stat-label { font-size: 11px; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.5px; }

    .org-hero-follow {
        background-color: white;
        color: var(--primary-700);
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

    /* Tabs */
    .feed-tabs {
        display: flex;
        background-color: var(--surface-bg);
        border-bottom: 1px solid var(--surface-border);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .feed-tab {
        flex: 1;
        text-align: center;
        padding: var(--space-3) var(--space-2);
        background: none;
        border: none;
        color: var(--text-tertiary);
        font-size: var(--text-sm);
        font-weight: var(--font-weight-semibold);
        border-bottom: 2px solid transparent;
        cursor: pointer;
    }

    .feed-tab.active { color: var(--primary-600); border-bottom-color: var(--primary-600); }

    /* Brand sections */
    .brand-section { padding: var(--space-4); border-bottom: 8px solid var(--surface-bg-secondary); }
    .brand-section-title {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        font-size: var(--text-sm);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin-bottom: var(--space-3);
    }

    .brand-add-btn {
        border: none;
        background: none;
        color: var(--primary-600);
        cursor: pointer;
        padding: 4px;
        font-size: var(--text-lg);
    }

    .brand-badge {
        width: 28px;
        height: 28px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: var(--text-xs);
        font-weight: var(--font-weight-bold);
        flex-shrink: 0;
    }

    .channels-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: var(--space-3);
    }

    .channel-card {
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-lg);
        padding: var(--space-3);
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: var(--space-2);
    }

    .channel-card:active { background-color: var(--surface-hover); }

    .channel-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-lg);
        background-color: var(--primary-50);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-600);
        font-size: var(--text-lg);
    }

    .channel-card-name { font-weight: var(--font-weight-semibold); font-size: var(--text-xs); color: var(--text-primary); }
    .channel-card-meta { font-size: 10px; color: var(--text-tertiary); }

    .empty-state {
        text-align: center;
        padding: var(--space-8) var(--space-4);
        color: var(--text-secondary);
        font-size: var(--text-sm);
    }

    /* Post cards (shared style) */
    .feed-list { padding: var(--space-4); display: flex; flex-direction: column; gap: var(--space-4); }

    .post-card {
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }

    .post-header { display: flex; align-items: flex-start; gap: var(--space-3); padding: var(--space-4); }

    .post-avatar {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: var(--font-weight-bold);
        flex-shrink: 0;
        font-size: var(--text-lg);
    }

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

    .feed-comments { display: none; padding: 0 var(--space-4) var(--space-4); border-top: 1px solid var(--surface-border); padding-top: var(--space-3); }
    .comment-item { background: var(--surface-hover); border-radius: var(--radius-md); padding: var(--space-2) var(--space-3); margin-bottom: var(--space-2); font-size: var(--text-xs); }
    .comment-form { display: flex; gap: var(--space-2); margin-top: var(--space-2); }
    .comment-form input { flex: 1; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-full); font-size: var(--text-xs); background-color: var(--surface-bg-secondary); color: var(--text-primary); }
    .comment-form button { width: 34px; height: 34px; border-radius: var(--radius-full); background-color: var(--primary-600); color: white; border: none; cursor: pointer; flex-shrink: 0; }

    .post-views { margin-inline-start: auto; display: flex; align-items: center; gap: 6px; color: var(--text-tertiary); font-size: var(--text-xs); }

    /* Sign-in modal (shared) */
    .signin-modal-overlay { display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 50; align-items: center; justify-content: center; padding: var(--space-4); }
    .signin-modal-overlay.show { display: flex; }
    .signin-modal { background-color: var(--surface-bg); border-radius: var(--radius-xl); padding: var(--space-8) var(--space-6); max-width: 340px; width: 100%; text-align: center; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25); }
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

    <!-- Org Hero -->
    <div class="org-hero">
        <div class="org-hero-avatar">{{ substr($organization->name, 0, 1) }}</div>
        <div class="org-hero-name">{{ $organization->name }}</div>
        @if($organization->description)
            <div class="org-hero-desc">{{ Str::limit($organization->description, 100) }}</div>
        @endif

        <div class="org-hero-stats">
            <div class="org-hero-stat">
                <div class="org-hero-stat-value">{{ $organization->users_count }}</div>
                <div class="org-hero-stat-label">{{ __('Members') }}</div>
            </div>
            <div class="org-hero-stat">
                <div class="org-hero-stat-value">{{ $organization->channels_count }}</div>
                <div class="org-hero-stat-label">{{ __('Channels') }}</div>
            </div>
            <div class="org-hero-stat">
                <div class="org-hero-stat-value">{{ $organization->posts_count }}</div>
                <div class="org-hero-stat-label">{{ __('Posts') }}</div>
            </div>
        </div>

        <div style="display: flex; gap: var(--space-2); justify-content: center;">
            @auth
                @php
                    $isFollowingOrg = auth()->user()->followedOrganizations()->where('organization_id', $organization->id)->exists();
                @endphp
                <form action="{{ $isFollowingOrg ? route('organizations.unfollow', $organization->id) : route('organizations.follow', $organization->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="org-hero-follow" style="{{ $isFollowingOrg ? 'background-color: rgba(255,255,255,0.2); color: white;' : '' }}">
                        <i class="bi {{ $isFollowingOrg ? 'bi-check-lg' : 'bi-plus-lg' }}"></i>
                        {{ $isFollowingOrg ? __('Following') : __('Follow') }}
                    </button>
                </form>
                <button type="button" class="org-hero-follow" style="background-color: rgba(255,255,255,0.2); color: white;" onclick="openAddToCollection('organization', {{ $organization->id }}, '{{ $organization->name }}')">
                    <i class="bi bi-folder-plus"></i>
                </button>
            @else
                <button type="button" class="org-hero-follow" onclick="requireSignIn()">
                    <i class="bi bi-plus-lg"></i> {{ __('Follow') }}
                </button>
            @endauth
        </div>
    </div>

    <!-- Tabs -->
    <nav class="feed-tabs">
        <button type="button" class="feed-tab active" id="tab-btn-channels" onclick="showOrgTab('channels')">
            <i class="bi bi-chat-dots"></i> {{ __('Channels') }}
        </button>
        <button type="button" class="feed-tab" id="tab-btn-posts" onclick="showOrgTab('posts')">
            <i class="bi bi-newspaper"></i> {{ __('Posts') }}
        </button>
    </nav>

    <!-- Channels Tab -->
    <div id="org-tab-channels">
        @forelse($brands as $brand)
            @if($brand->channels->count() > 0)
                <div class="brand-section">
                    <div class="brand-section-title">
                        <span class="brand-badge" style="background-color: {{ $colorFor($brand->name) }};">{{ substr($brand->name, 0, 1) }}</span>
                        <span style="flex: 1;">{{ $brand->name }}</span>
                        @auth
                            <button type="button" class="brand-add-btn" onclick="openAddToCollection('brand', {{ $brand->id }}, '{{ $brand->name }}')" title="{{ __('Add to Collection') }}">
                                <i class="bi bi-folder-plus"></i>
                            </button>
                        @endauth
                    </div>
                    <div class="channels-grid">
                        @foreach($brand->channels as $channel)
                            <a href="{{ route('guest.channel-detail', [$organization, $channel->slug]) }}" class="channel-card">
                                <div class="channel-icon"><i class="bi bi-chat-dots"></i></div>
                                <div class="channel-card-name">{{ $channel->name }}</div>
                                <div class="channel-card-meta">{{ $channel->users_count }} {{ __('members') }} · {{ $channel->posts_count }} {{ __('posts') }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @empty
        @endforelse

        @if($unbrandedChannels->count() > 0)
            <div class="brand-section">
                <div class="brand-section-title">
                    <span class="brand-badge" style="background-color: var(--neutral-500);"><i class="bi bi-chat-dots"></i></span>
                    {{ __('Other Channels') }}
                </div>
                <div class="channels-grid">
                    @foreach($unbrandedChannels as $channel)
                        <a href="{{ route('guest.channel-detail', [$organization, $channel->slug]) }}" class="channel-card">
                            <div class="channel-icon"><i class="bi bi-chat-dots"></i></div>
                            <div class="channel-card-name">{{ $channel->name }}</div>
                            <div class="channel-card-meta">{{ $channel->users_count }} {{ __('members') }} · {{ $channel->posts_count }} {{ __('posts') }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($brands->every(fn($b) => $b->channels->isEmpty()) && $unbrandedChannels->isEmpty())
            <div class="empty-state">{{ __('No public channels available') }}</div>
        @endif
    </div>

    <!-- Posts Tab -->
    <div id="org-tab-posts" style="display: none;">
        <div class="feed-list">
            @forelse($posts as $post)
                @php
                    $displayName = $post->channel->name ?? $organization->name;
                    $subtitle = $post->channel->brand->name ?? ucfirst(str_replace('_', ' ', $post->post_type ?? 'update'));
                    $viewsCount = $post->receipts()->whereNotNull('first_viewed_at')->count();
                    $likeCount = $post->reactions()->where('type', 'like')->count();
                    $commentCount = $post->comments()->approved()->count();
                    $userLiked = auth()->check() && $post->reactions()->where('user_id', auth()->id())->where('type', 'like')->exists();
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
                                <i class="bi bi-globe2"></i>
                                <span>{{ __('Public') }}</span>
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
                <div class="empty-state">{{ __('No posts yet') }}</div>
            @endforelse

            @if($posts->hasPages())
                <div style="display: flex; justify-content: center; padding-top: var(--space-2);">
                    {{ $posts->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@section('modals')
    <div class="signin-modal-overlay" id="signinModal" onclick="if(event.target===this) closeSignInModal()">
        <div class="signin-modal">
            <i class="bi bi-lock-fill lock-icon"></i>
            <h3>{{ __('Sign in required') }}</h3>
            <p>{{ __('Create a free account to follow this organization and interact with posts.') }}</p>
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
    function showOrgTab(tab) {
        document.getElementById('org-tab-channels').style.display = tab === 'channels' ? 'block' : 'none';
        document.getElementById('org-tab-posts').style.display = tab === 'posts' ? 'block' : 'none';
        document.getElementById('tab-btn-channels').classList.toggle('active', tab === 'channels');
        document.getElementById('tab-btn-posts').classList.toggle('active', tab === 'posts');
    }

    function toggleFeedComments(button) {
        const card = button.closest('.post-card');
        const section = card.querySelector('.feed-comments');
        section.style.display = section.style.display === 'none' || !section.style.display ? 'block' : 'none';
    }

    function requireSignIn() {
        document.getElementById('signinModal').classList.add('show');
    }

    function closeSignInModal() {
        document.getElementById('signinModal').classList.remove('show');
    }
</script>
@endsection
