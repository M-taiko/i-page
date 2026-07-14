@extends('layouts.mobile-shell')

@section('title', __('i-Page') . ' - ' . __('Discover Organizations'))

@section('app-bar')
    <button type="button" class="app-bar-icon-btn" onclick="toggleDrawer(true)" aria-label="{{ __('Menu') }}">
        <i class="bi bi-list"></i>
    </button>
    <div class="app-bar-title">i-Page</div>
    <div class="app-bar-actions">
        <a href="{{ route('guest.search-organizations') }}" class="app-bar-icon-btn" aria-label="{{ __('Search') }}">
            <i class="bi bi-search"></i>
        </a>
        <a href="{{ route('login') }}" class="app-bar-icon-btn" aria-label="{{ __('Sign in') }}" style="color: var(--primary-600);">
            <i class="bi bi-box-arrow-in-right"></i>
        </a>
    </div>
@endsection

@section('extra-styles')
    /* Tabs */
    .feed-tabs {
        display: flex;
        background-color: var(--primary-100);
        border: 1px solid var(--primary-400);
        border-top: none;
        border-bottom-left-radius: var(--radius-xl);
        border-bottom-right-radius: var(--radius-xl);
        position: sticky;
        top: 0;
        z-index: 10;
        overflow: hidden;
    }

    [data-theme="dark"] .feed-tabs {
        background-color: #1b2559;
        border-color: var(--primary-500);
    }

    .feed-tab {
        flex: 1;
        text-align: center;
        padding: var(--space-3) var(--space-2);
        text-decoration: none;
        color: var(--text-tertiary);
        font-size: var(--text-sm);
        font-weight: var(--font-weight-semibold);
        border-bottom: 2px solid transparent;
        white-space: nowrap;
    }

    .feed-tab.active {
        color: var(--primary-600);
        border-bottom-color: var(--primary-600);
    }

    /* Featured organizations strip */
    .groups-section {
        padding: var(--space-4);
        background-color: var(--surface-bg);
        border-bottom: 8px solid var(--surface-bg-secondary);
    }

    .groups-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: var(--space-3);
    }

    .groups-header h2 {
        font-size: var(--text-sm);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
    }

    .groups-header a {
        font-size: var(--text-xs);
        color: var(--primary-600);
        text-decoration: none;
        font-weight: var(--font-weight-medium);
    }

    .groups-scroll {
        display: flex;
        gap: var(--space-4);
        overflow-x: auto;
        scrollbar-width: none;
        padding-bottom: 2px;
    }

    .groups-scroll::-webkit-scrollbar { display: none; }

    .group-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: var(--space-1);
        text-decoration: none;
        flex-shrink: 0;
        width: 64px;
    }

    .group-avatar-wrap { position: relative; }

    .group-avatar {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-xl);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: var(--text-xl);
        font-weight: var(--font-weight-bold);
    }

    .group-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background-color: var(--primary-600);
        color: white;
        font-size: 10px;
        font-weight: var(--font-weight-bold);
        min-width: 18px;
        height: 18px;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        border: 2px solid var(--surface-bg);
    }

    .group-name {
        font-size: 11px;
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 64px;
    }

    .group-sub { font-size: 10px; color: var(--text-tertiary); }

    .group-add .group-avatar {
        background-color: var(--surface-hover);
        color: var(--primary-600);
        border: 2px dashed var(--surface-border);
    }

    /* Post cards */
    .feed-list { padding: var(--space-4); display: flex; flex-direction: column; gap: var(--space-4); }

    .post-card {
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }

    .post-header {
        display: flex;
        align-items: flex-start;
        gap: var(--space-3);
        padding: var(--space-4);
    }

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

    .post-author {
        font-weight: var(--font-weight-bold);
        font-size: var(--text-sm);
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .post-subtitle { font-size: var(--text-xs); color: var(--text-tertiary); margin-top: 1px; }

    .post-meta {
        font-size: var(--text-xs);
        color: var(--text-tertiary);
        margin-top: 2px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .post-body {
        padding: 0 var(--space-4) var(--space-3);
        font-size: var(--text-sm);
        line-height: var(--line-height-relaxed);
        color: var(--text-primary);
    }

    .post-image {
        width: 100%;
        max-height: 320px;
        object-fit: cover;
        display: block;
        background-color: var(--surface-hover);
    }

    .post-footer {
        display: flex;
        align-items: center;
        gap: var(--space-4);
        padding: var(--space-3) var(--space-4);
        border-top: 1px solid var(--surface-border);
    }

    .post-action {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: var(--text-xs);
        font-weight: var(--font-weight-medium);
        padding: 0;
    }

    .post-action i { font-size: var(--text-base); }
    .post-action.liked { color: var(--primary-600); }

    .feed-comments { display: none; padding: 0 var(--space-4) var(--space-4); border-top: 1px solid var(--surface-border); padding-top: var(--space-3); }
    .comment-item { background: var(--surface-hover); border-radius: var(--radius-md); padding: var(--space-2) var(--space-3); margin-bottom: var(--space-2); font-size: var(--text-xs); }
    .comment-form { display: flex; gap: var(--space-2); margin-top: var(--space-2); }
    .comment-form input { flex: 1; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-full); font-size: var(--text-xs); background-color: var(--surface-bg-secondary); color: var(--text-primary); }
    .comment-form button { width: 34px; height: 34px; border-radius: var(--radius-full); background-color: var(--primary-600); color: white; border: none; cursor: pointer; flex-shrink: 0; }

    .post-views {
        margin-inline-start: auto;
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--text-tertiary);
        font-size: var(--text-xs);
    }

    .empty-feed { text-align: center; padding: var(--space-8) var(--space-4); color: var(--text-secondary); }

    /* Drawer */
    .drawer-overlay { display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 40; }
    .drawer-overlay.show { display: block; }

    .drawer {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        width: 280px;
        max-width: 82vw;
        background-color: var(--surface-bg);
        z-index: 41;
        transform: translateX(-100%);
        transition: transform 0.25s ease;
        display: flex;
        flex-direction: column;
        padding: var(--space-6) var(--space-4);
        padding-top: max(var(--space-6), env(safe-area-inset-top));
    }

    html[dir="rtl"] .drawer { left: auto; right: 0; transform: translateX(100%); }
    .drawer.show { transform: translateX(0); }

    .drawer-brand {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        margin-bottom: var(--space-6);
    }

    .drawer-brand-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, var(--primary-600), var(--secondary-600));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: var(--font-weight-bold);
        font-size: var(--text-xl);
    }

    .drawer-link {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        padding: var(--space-3);
        text-decoration: none;
        color: var(--text-primary);
        border-radius: var(--radius-md);
        font-size: var(--text-sm);
        font-weight: var(--font-weight-medium);
    }

    .drawer-link:hover { background-color: var(--surface-hover); }
    .drawer-link i { font-size: var(--text-lg); color: var(--text-secondary); width: 22px; }

    /* Sign-in modal */
    .signin-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 50;
        align-items: center;
        justify-content: center;
        padding: var(--space-4);
    }

    .signin-modal-overlay.show { display: flex; }

    .signin-modal {
        background-color: var(--surface-bg);
        border-radius: var(--radius-xl);
        padding: var(--space-8) var(--space-6);
        max-width: 340px;
        width: 100%;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
    }

    .signin-modal i.lock-icon { font-size: 2.5rem; color: var(--primary-600); display: block; margin-bottom: var(--space-3); }
    .signin-modal h3 { font-size: var(--text-lg); margin-bottom: var(--space-2); color: var(--text-primary); }
    .signin-modal p { color: var(--text-secondary); margin-bottom: var(--space-5); font-size: var(--text-sm); }
    .signin-modal-actions { display: flex; gap: var(--space-3); }

    .signin-modal-actions button, .signin-modal-actions a {
        flex: 1;
        padding: var(--space-3);
        border-radius: var(--radius-md);
        font-weight: var(--font-weight-medium);
        text-decoration: none;
        font-size: var(--text-sm);
        cursor: pointer;
        border: none;
    }

    .signin-modal-actions .btn-cancel { background-color: var(--surface-hover); color: var(--text-primary); }
    .signin-modal-actions .btn-signin { background-color: var(--primary-600); color: white; display: flex; align-items: center; justify-content: center; }
@endsection

@section('content')
    <!-- Tabs -->
    <nav class="feed-tabs">
        <a href="{{ route('guest.home', ['tab' => 'latest']) }}" class="feed-tab {{ $tab === 'latest' ? 'active' : '' }}">{{ __('Latest') }}</a>
        <a href="{{ route('guest.home', ['tab' => 'trending']) }}" class="feed-tab {{ $tab === 'trending' ? 'active' : '' }}">{{ __('Trending') }}</a>
    </nav>

    @php
        $avatarPalette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777'];
        $colorFor = fn($seed) => $avatarPalette[crc32($seed) % count($avatarPalette)];
    @endphp

    <!-- Featured Organizations -->
    <div class="groups-section">
        <div class="groups-header">
            <h2>{{ __('Featured Organizations') }}</h2>
            <a href="{{ route('guest.search-organizations') }}">{{ __('See all') }}</a>
        </div>
        <div class="groups-scroll">
            @foreach($organizations as $org)
                <a href="{{ route('guest.organization-detail', $org->id) }}" class="group-item">
                    <div class="group-avatar-wrap">
                        <div class="group-avatar" style="background-color: {{ $colorFor($org->name) }};">
                            {{ substr($org->name, 0, 1) }}
                        </div>
                        @if($org->channels_count > 0)
                            <span class="group-badge">{{ $org->channels_count }}</span>
                        @endif
                    </div>
                    <div class="group-name">{{ $org->name }}</div>
                    <div class="group-sub">{{ $org->channels_count }} {{ __('channels') }}</div>
                </a>
            @endforeach

            <a href="{{ route('guest.search-organizations') }}" class="group-item group-add">
                <div class="group-avatar-wrap">
                    <div class="group-avatar"><i class="bi bi-search"></i></div>
                </div>
                <div class="group-name">{{ __('See More') }}</div>
            </a>
        </div>
    </div>

    <!-- Feed -->
    <div class="feed-list">
        @forelse($posts as $post)
            @php
                $displayName = $post->channel->name ?? __('i-Page');
                $subtitle = $post->channel->brand->name ?? ucfirst(str_replace('_', ' ', $post->post_type ?? 'update'));
                $viewsCount = $post->receipts()->whereNotNull('first_viewed_at')->count();
                $likeCount = $post->reactions()->where('type', 'like')->count();
                $commentCount = $post->comments()->approved()->count();
                $userLiked = auth()->check() && $post->reactions()->where('user_id', auth()->id())->where('type', 'like')->exists();
            @endphp
            <article class="post-card">
                <div class="post-header">
                    <a href="{{ route('guest.organization-detail', $post->channel->organization_id) }}" style="text-decoration:none;">
                        <div class="post-avatar" style="background-color: {{ $colorFor($displayName) }};">
                            {{ substr($displayName, 0, 1) }}
                        </div>
                    </a>
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
                    <button type="button" class="post-action" onclick="copyPostLink('{{ route('guest.organization-detail', $post->channel->organization_id) }}')">
                        <i class="bi bi-share"></i>
                    </button>
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
                <p style="margin-bottom: var(--space-3);">{{ __('No public posts yet') }}</p>
                <a href="{{ route('guest.search-organizations') }}" style="color: var(--primary-600); text-decoration: none; font-weight: 500;">
                    {{ __('Browse Organizations') }} →
                </a>
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
    <!-- Slide-in Drawer -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="toggleDrawer(false)"></div>
    <div class="drawer" id="drawer">
        <div class="drawer-brand">
            <div class="drawer-brand-icon"><i class="bi bi-globe"></i></div>
            <div>
                <div style="font-weight: var(--font-weight-bold); font-size: var(--text-base);">i-Page</div>
                <div style="font-size: var(--text-xs); color: var(--text-tertiary);">{{ __('The Digital Front Door') }}</div>
            </div>
        </div>

        <a href="{{ route('guest.home') }}" class="drawer-link"><i class="bi bi-house"></i> {{ __('Home') }}</a>
        <a href="{{ route('guest.search-organizations') }}" class="drawer-link"><i class="bi bi-building"></i> {{ __('Explore Organizations') }}</a>

        <div style="flex: 1;"></div>

        <a href="{{ route('login') }}" class="drawer-link" style="background-color: var(--primary-50); color: var(--primary-700);">
            <i class="bi bi-box-arrow-in-right" style="color: var(--primary-600);"></i> {{ __('Sign In') }}
        </a>
        <a href="{{ route('register') }}" class="drawer-link">
            <i class="bi bi-person-plus"></i> {{ __('Create Account') }}
        </a>
    </div>

    <!-- Sign-in required modal -->
    <div class="signin-modal-overlay" id="signinModal" onclick="if(event.target===this) closeSignInModal()">
        <div class="signin-modal">
            <i class="bi bi-lock-fill lock-icon"></i>
            <h3>{{ __('Sign in required') }}</h3>
            <p>{{ __('Create a free account to like, comment, and follow organizations.') }}</p>
            <div class="signin-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeSignInModal()">{{ __('Cancel') }}</button>
                <a href="{{ route('login') }}" class="btn-signin">{{ __('Sign In') }}</a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function toggleDrawer(show) {
        document.getElementById('drawer').classList.toggle('show', show);
        document.getElementById('drawerOverlay').classList.toggle('show', show);
    }

    function requireSignIn() {
        document.getElementById('signinModal').classList.add('show');
    }

    function toggleFeedComments(button) {
        const card = button.closest('.post-card');
        const section = card.querySelector('.feed-comments');
        section.style.display = section.style.display === 'none' || !section.style.display ? 'block' : 'none';
    }

    function copyPostLink(url) {
        navigator.clipboard?.writeText(url);
        alert('{{ __('Link copied!') }}');
    }

    function closeSignInModal() {
        document.getElementById('signinModal').classList.remove('show');
    }
</script>
@endsection
