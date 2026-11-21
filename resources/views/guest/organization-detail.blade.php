@extends('layouts.mobile-shell')

@section('title', $organization->name . ' - i-Page')
@section('meta_description', \Illuminate\Support\Str::limit($organization->description ?: __('Follow :name on i-Page for news, announcements and updates.', ['name' => $organization->name]), 180))
@php
    $ogCover = \App\Support\Designs::coverPaths($organization->design_options ?? [])[0] ?? null;
@endphp
@if ($ogCover)
    @section('meta_image', url(\Illuminate\Support\Facades\Storage::url($ogCover)))
@endif

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
        padding: var(--space-6) var(--space-4) 1.25rem;
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

    /* Section headings */
    .section-head { display: flex; align-items: center; justify-content: space-between; gap: var(--space-2); margin: 0 var(--space-4) var(--space-3); }
    .section-head h2 { display: flex; align-items: center; gap: var(--space-2); margin: 0; font-size: var(--text-base); font-weight: var(--font-weight-bold); color: var(--text-primary); }
    .section-head h2 i { color: var(--primary-600); }
    .section-hint { font-size: var(--text-xs); color: var(--text-tertiary); display: inline-flex; align-items: center; gap: 4px; }
    [dir="rtl"] .rtl-flip { transform: scaleX(-1); display: inline-block; }

    /* Channel spotlights */
    .spot-section { padding: 1.25rem 0 var(--space-4); border-bottom: 8px solid var(--surface-bg-secondary); scroll-margin-top: 70px; }
    .spot-strip { display: flex; gap: 12px; overflow-x: auto; scroll-snap-type: x mandatory; padding: 4px var(--space-4) 12px; scrollbar-width: none; scroll-padding-inline: var(--space-4); }
    .spot-strip::-webkit-scrollbar { display: none; }
    .spot { flex: 0 0 auto; width: 132px; scroll-snap-align: start; text-decoration: none !important; color: inherit; border-radius: 20px; }
    .spot:focus-visible { outline: 3px solid var(--primary-500); outline-offset: 3px; }
    .spot-tile {
        position: relative; width: 132px; height: 184px; border-radius: 20px; overflow: hidden;
        background: linear-gradient(160deg, var(--spot-tone) 0%, color-mix(in srgb, var(--spot-tone) 55%, #0f172a) 100%);
        box-shadow: 0 8px 22px rgba(15, 23, 42, .16);
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .spot:hover .spot-tile, .spot:focus-visible .spot-tile { transform: translateY(-4px); box-shadow: 0 14px 30px rgba(15, 23, 42, .24); }
    .spot:active .spot-tile { transform: scale(.97); }
    .spot-tile img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .spot-tile::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,.72) 100%); }
    .spot-letter { position: absolute; inset: 0 0 34%; display: flex; align-items: center; justify-content: center; font-size: 56px; font-weight: 800; color: rgba(255,255,255,.92); text-shadow: 0 4px 18px rgba(0,0,0,.18); }
    .spot-badge { position: absolute; z-index: 2; top: 9px; inset-inline-start: 9px; display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 999px; background: rgba(255,255,255,.92); color: #0f172a; font-size: 11px; font-weight: 700; }
    .spot-cap { position: absolute; z-index: 2; inset-inline: 10px; bottom: 10px; display: flex; flex-direction: column; gap: 2px; color: #fff; }
    .spot-name { font-size: 13px; font-weight: 800; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .spot-meta { font-size: 10.5px; opacity: .85; }
    .spot-strip.nudge .spot-tile { animation: spotPulse 1.1s ease 2; }
    @keyframes spotPulse { 0%, 100% { transform: none; } 40% { transform: translateY(-6px) scale(1.03); box-shadow: 0 16px 34px rgba(98, 89, 202, .35); } }

    /* Posts */
    .posts-section { padding: 1.25rem 0 var(--space-4); }
    .posts-section .feed-list { padding: 0 var(--space-4); }

    /* Friendly empty states */
    .soft-empty {
        margin: 0 var(--space-4); padding: var(--space-6) 1.25rem; text-align: center;
        border-radius: 20px; background: var(--surface-bg); border: 1px dashed var(--surface-border); color: var(--text-secondary);
    }
    .soft-empty-invite {
        border-style: solid; border-color: var(--primary-200, var(--surface-border));
        background: linear-gradient(160deg, var(--primary-50, var(--surface-bg)) 0%, var(--surface-bg) 70%);
    }
    [data-theme="dark"] .soft-empty-invite { background: linear-gradient(160deg, rgba(98, 89, 202, .18) 0%, var(--surface-bg) 70%); border-color: rgba(98, 89, 202, .35); }
    .soft-empty-icon { font-size: 40px; line-height: 1; margin-bottom: var(--space-3); }
    .soft-empty-title { font-size: var(--text-lg); font-weight: var(--font-weight-bold); color: var(--text-primary); margin-bottom: var(--space-2); }
    .soft-empty p { font-size: var(--text-sm); line-height: 1.7; margin: 0 auto var(--space-4); max-width: 420px; }
    .soft-empty-btn {
        display: inline-flex; align-items: center; gap: var(--space-2); border: 0; cursor: pointer;
        padding: var(--space-3) 1.25rem; border-radius: 999px; background: var(--primary-600); color: #fff;
        font-weight: var(--font-weight-semibold); font-size: var(--text-sm); box-shadow: 0 8px 20px rgba(69, 87, 245, .28);
    }
    .soft-empty-btn:hover { background: var(--primary-700); }
    .soft-empty-chips { display: flex; flex-wrap: wrap; justify-content: center; gap: var(--space-2); margin-top: var(--space-4); }
    .soft-empty-chips .chip { padding: 6px 12px; border-radius: 999px; background: var(--surface-bg-secondary); color: var(--text-primary); font-size: var(--text-xs); font-weight: var(--font-weight-semibold); text-decoration: none; border: 1px solid var(--surface-border); }
    .soft-empty-chips .chip:hover { border-color: var(--primary-500); color: var(--primary-600); }

    @media (min-width: 768px) {
        .spot, .spot-tile { width: 150px; }
        .spot-tile { height: 208px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .spot-tile { transition: none; }
        .spot-strip.nudge .spot-tile { animation: none; }
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
    .signin-modal p { color: var(--text-secondary); margin-bottom: 1.25rem; font-size: var(--text-sm); }
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

    {{-- Channels as spotlights: tall tiles in a swipeable strip (ADR-016 tenant page). --}}
    <section class="spot-section" id="org-channels" aria-labelledby="spotTitle">
        <div class="section-head">
            <h2 id="spotTitle"><i class="bi bi-stars"></i> {{ __('Channels') }}</h2>
            @if($channels->count() > 2)
                <span class="section-hint">{{ __('Swipe to see all') }} <i class="bi bi-arrow-right rtl-flip"></i></span>
            @endif
        </div>

        @if($channels->isNotEmpty())
            <div class="spot-strip" role="list">
                @foreach($channels as $channel)
                    @php
                        $cover = \App\Support\Designs::coverPaths($channel->design_options ?? [])[0] ?? $channel->logo_path;
                        $tone = $colorFor($channel->name);
                        $subCount = $channel->childChannels->count();
                    @endphp
                    <a href="{{ route('guest.channel-detail', [$organization, $channel->slug]) }}" class="spot" role="listitem"
                       style="--spot-tone: {{ $tone }};" aria-label="{{ $channel->name }}"
                       data-tip="{{ $channel->name }}" data-tip-sub="{{ $channel->users_count }} {{ __('members') }}{{ $subCount ? ' · ' . trans_choice(':count sub-channel|:count sub-channels', $subCount) : '' }}">
                        <div class="spot-tile">
                            @if($cover)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($cover) }}" alt="" loading="lazy">
                            @else
                                <span class="spot-letter" aria-hidden="true">{{ mb_substr($channel->name, 0, 1) }}</span>
                            @endif
                            @if($subCount)
                                <span class="spot-badge"><i class="bi bi-diagram-3"></i> {{ $subCount }}</span>
                            @endif
                            <span class="spot-cap">
                                <span class="spot-name">{{ $channel->name }}</span>
                                <span class="spot-meta">{{ $channel->users_count }} {{ __('members') }} · {{ $channel->posts_count }} {{ __('posts') }}</span>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="soft-empty">
                <x-illustration name="channels" size="150" />
                <div class="soft-empty-title">{{ __(':name is just getting started', ['name' => $organization->name]) }}</div>
                <p>{{ __('Channels will appear here soon. Follow the page so you hear about them first.') }}</p>
            </div>
        @endif
    </section>

    {{-- Posts under the channels; a friendly nudge toward the channels when there are none. --}}
    <section class="posts-section" aria-labelledby="postsTitle">
        <div class="section-head">
            <h2 id="postsTitle"><i class="bi bi-newspaper"></i> {{ __('Latest posts') }}</h2>
        </div>

        @if($posts->isNotEmpty())
            <div class="feed-list">
                @foreach($posts as $post)
                    @php
                        $displayName = $post->sourceName() ?? $organization->name;
                        $subtitle = ($post->isTenantPost() ? __('Official post') : null) ?? ucfirst(str_replace('_', ' ', $post->post_type ?? 'update'));
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
    
                        @include('posts.partials._content', ['post' => $post, 'limit' => 280, 'url' => route('guest.post-detail', $post->id), 'showWidgets' => $post->hasWidgets()])
    
                        <div class="post-footer">
                            @auth
                                @unless ($post->hasWidgets())
                                <form action="{{ route('posts.like', $post->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="post-action {{ $userLiked ? 'liked' : '' }}">
                                        <i class="bi {{ $userLiked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }}"></i>
                                        <span>{{ $likeCount }}</span>
                                    </button>
                                </form>
                                @endunless
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
            @endforeach

                @if($posts->hasPages())
                    <div style="display: flex; justify-content: center; padding-top: var(--space-2);">
                        {{ $posts->onEachSide(1)->links() }}
                    </div>
                @endif
            </div>
        @elseif($channels->isNotEmpty())
            <div class="soft-empty soft-empty-invite">
                <x-illustration name="feed" size="150" />
                <div class="soft-empty-title">{{ __('The good stuff is inside the channels') }}</div>
                <p>{{ __('No posts on this page yet — but every channel above has its own news, offers and updates. Tap one to look inside, and subscribe so you never miss a thing.') }}</p>
                <button type="button" class="soft-empty-btn" onclick="spotlightChannels()">
                    <i class="bi bi-hand-index-thumb"></i> {{ __('Explore the channels') }}
                </button>
                <div class="soft-empty-chips">
                    @foreach($channels->take(4) as $channel)
                        <a href="{{ route('guest.channel-detail', [$organization, $channel->slug]) }}" class="chip">{{ $channel->name }}</a>
                    @endforeach
                </div>
            </div>
        @else
            <div class="soft-empty">
                <x-illustration name="empty" size="150" />
                <div class="soft-empty-title">{{ __('Nothing here yet') }}</div>
                <p>{{ __('Come back soon — news from :name will show up right here.', ['name' => $organization->name]) }}</p>
            </div>
        @endif
    </section>
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

    /* Empty-state button: bring the channel spotlights into view and give them a little bounce. */
    function spotlightChannels() {
        const section = document.getElementById('org-channels');
        const strip = section.querySelector('.spot-strip');
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (strip) {
            strip.classList.remove('nudge');
            void strip.offsetWidth;
            strip.classList.add('nudge');
        }
    }
</script>
@endsection
