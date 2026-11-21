@extends('layouts.mobile-shell')

@section('title', __('Home') . ' - i-Page')

@section('app-bar')
    <button type="button" class="app-bar-icon-btn" onclick="toggleDrawer(true)" aria-label="{{ __('Menu') }}">
        <i class="bi bi-list"></i>
    </button>
    <div class="app-bar-title" style="display: flex; align-items: center; gap: var(--space-2);">
        <img src="{{ asset('images/brand/logo-mark.svg') }}" alt="" style="width: 24px; height: 24px;">
        {{ __('Home') }}
    </div>
    <div class="app-bar-actions">
        <a href="{{ route('user.explore-organizations') }}" class="app-bar-icon-btn" aria-label="{{ __('Search') }}">
            <i class="bi bi-search"></i>
        </a>
        <button type="button" class="app-bar-icon-btn" onclick="toggleAppMenu()" aria-label="{{ __('More') }}">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
    </div>
@endsection

@section('extra-styles')
    .home { padding-bottom: 96px; }
    @include('partials.feed._styles')
    .hero-stats { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 2px; }
    .hero-stat { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 999px; background: rgba(255, 255, 255, .16); font-size: 12px; font-weight: 700; color: #fff; text-decoration: none; }
    .hero-stat:hover { background: rgba(255, 255, 255, .26); }
    .hero-stat b { font-size: 13px; }
    .collections-section { margin-top: 4px; }
    /* Collections strip (Instagram Highlights style) */
    .collections-section {
        padding: var(--space-4) 0 var(--space-4) var(--space-4);
        background-color: var(--surface-bg);
        border-bottom: 8px solid var(--surface-bg-secondary);
    }

    .collections-scroll {
        display: flex;
        gap: var(--space-4);
        overflow-x: auto;
        scrollbar-width: none;
        padding-bottom: 2px;
        padding-inline-end: var(--space-4);
    }

    .collections-scroll::-webkit-scrollbar { display: none; }

    .collection-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        flex-shrink: 0;
        width: 72px;
        cursor: pointer;
        user-select: none;
        -webkit-user-select: none;
        touch-action: pan-y;
    }

    .collection-item.dragging { opacity: 0.4; }

    .collection-avatar-wrap { position: relative; }

    .collection-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        transition: transform 0.15s ease;
        position: relative;
    }

    .collection-item:active .collection-avatar { transform: scale(0.93); }

    .collection-avatar.pinned::before {
        content: '';
        position: absolute;
        inset: -3px;
        border-radius: 50%;
        border: 2px solid var(--warning-500);
    }

    .collection-badge {
        position: absolute;
        bottom: -2px;
        right: -2px;
        background-color: var(--primary-600);
        color: white;
        font-size: 10px;
        font-weight: var(--font-weight-bold);
        min-width: 20px;
        height: 20px;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        border: 2.5px solid var(--surface-bg);
    }

    .collection-mute-badge {
        position: absolute;
        top: -2px;
        left: -2px;
        width: 18px;
        height: 18px;
        border-radius: var(--radius-full);
        background-color: var(--text-tertiary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        border: 2px solid var(--surface-bg);
    }

    .collection-name {
        font-size: 11px;
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 72px;
    }

    .collection-add .collection-avatar {
        background-color: var(--surface-hover);
        color: var(--primary-600);
        border: 2px dashed var(--surface-border);
        box-shadow: none;
    }

    /* Bottom sheet (long-press actions) */
    .sheet-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 60;
        align-items: flex-end;
        justify-content: center;
    }

    .sheet-overlay.show { display: flex; }

    .bottom-sheet {
        background-color: var(--surface-bg);
        width: 100%;
        max-width: 480px;
        border-radius: 20px 20px 0 0;
        padding: var(--space-3) 0 max(var(--space-4), env(safe-area-inset-bottom));
        animation: sheetUp 0.2s ease;
    }

    @keyframes sheetUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    .sheet-handle { width: 40px; height: 4px; background-color: var(--surface-border); border-radius: var(--radius-full); margin: 0 auto var(--space-3); }

    .sheet-title {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: 0 var(--space-4) var(--space-3);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
    }

    .sheet-title .sheet-icon-preview {
        width: 32px; height: 32px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; font-size: 16px;
    }

    .sheet-action {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        width: 100%;
        padding: var(--space-3) var(--space-4);
        background: none;
        border: none;
        text-align: start;
        cursor: pointer;
        font-size: var(--text-sm);
        color: var(--text-primary);
    }

    .sheet-action:hover { background-color: var(--surface-hover); }
    .sheet-action i { font-size: var(--text-lg); color: var(--text-secondary); width: 22px; }
    .sheet-action.danger { color: var(--danger-600); }
    .sheet-action.danger i { color: var(--danger-600); }

    /* Collection create/edit modal */
    .cmodal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 60;
        align-items: center;
        justify-content: center;
        padding: var(--space-4);
    }

    .cmodal-overlay.show { display: flex; }

    .cmodal {
        background-color: var(--surface-bg);
        border-radius: var(--radius-xl);
        padding: var(--space-6);
        max-width: 360px;
        width: 100%;
        max-height: 85vh;
        overflow-y: auto;
    }

    .cmodal h3 { font-size: var(--text-lg); margin-bottom: var(--space-4); color: var(--text-primary); }

    .cmodal-preview {
        width: 72px; height: 72px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; margin: 0 auto var(--space-4);
        transition: background-color 0.15s ease;
    }

    .cmodal label { display: block; font-size: var(--text-xs); font-weight: var(--font-weight-semibold); color: var(--text-secondary); margin-bottom: var(--space-2); text-transform: uppercase; }

    .cmodal input[type="text"] {
        width: 100%;
        padding: var(--space-3);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-md);
        font-size: var(--text-sm);
        background-color: var(--surface-bg-secondary);
        color: var(--text-primary);
        margin-bottom: var(--space-4);
    }

    .icon-grid, .color-grid { display: flex; flex-wrap: wrap; gap: var(--space-2); margin-bottom: var(--space-4); }

    .icon-swatch {
        width: 40px; height: 40px; border-radius: var(--radius-md);
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; background-color: var(--surface-bg-secondary);
        border: 2px solid transparent; cursor: pointer;
    }

    .icon-swatch.selected { border-color: var(--primary-600); background-color: var(--primary-50); }

    .color-swatch {
        width: 32px; height: 32px; border-radius: 50%;
        cursor: pointer; border: 3px solid transparent;
    }

    .color-swatch.selected { border-color: var(--text-primary); }

    .cmodal-actions { display: flex; gap: var(--space-3); margin-top: var(--space-2); }

    .cmodal-actions button {
        flex: 1;
        padding: var(--space-3);
        border-radius: var(--radius-md);
        font-weight: var(--font-weight-medium);
        font-size: var(--text-sm);
        cursor: pointer;
        border: none;
    }

    .cmodal-actions .btn-cancel { background-color: var(--surface-hover); color: var(--text-primary); }
    .cmodal-actions .btn-save { background-color: var(--primary-600); color: white; }

    /* Drawer */
    .drawer-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 40;
    }

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

    .drawer-user { display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-6); }

    .drawer-avatar {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, var(--primary-600), var(--secondary-600));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: var(--font-weight-bold);
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

    /* App menu dropdown */
    .app-menu {
        display: none;
        position: absolute;
        top: 56px;
        inset-inline-end: var(--space-2);
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-md);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        z-index: 30;
        min-width: 180px;
        overflow: hidden;
    }

    .app-menu.show { display: block; }

    .app-menu button, .app-menu a {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        width: 100%;
        padding: var(--space-3);
        background: none;
        border: none;
        text-align: start;
        cursor: pointer;
        font-size: var(--text-sm);
        color: var(--text-primary);
        text-decoration: none;
    }

    .app-menu button:hover, .app-menu a:hover { background-color: var(--surface-hover); }
@endsection

@section('content')
@php
    $palette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777', '#0891b2'];
    $colorFor = fn ($seed) => $palette[crc32((string) $seed) % count($palette)];
    $hour = (int) now()->format('G');
    // Server-side fallback; the page script switches both to the visitor's own clock.
    $day = $hour >= 5 && $hour < 17;
    $greeting = $hour >= 5 && $hour < 12 ? __('Good morning') : ($hour >= 12 && $hour < 17 ? __('Good afternoon') : __('Good evening'));
    $me = auth()->user();
@endphp
<div class="home">
    <section class="hero reveal">
        <div class="hero-text">
            <div class="hero-kicker"><span class="js-greeting" data-morning="{{ __('Good morning') }}" data-afternoon="{{ __('Good afternoon') }}" data-evening="{{ __('Good evening') }}">{{ $greeting }}</span>, {{ $me->first_name }} 👋</div>
            @if ($hasFeed)
                <h1>{{ $stats['today'] ? trans_choice(':count new post today|:count new posts today', $stats['today'], ['count' => $stats['today']]) : __('Here\'s what\'s new') }}</h1>
                <p>{{ __('Everything from the channels you follow, newest first.') }}</p>
            @else
                <h1>{{ __('Let\'s fill your feed') }}</h1>
                <p>{{ __('Here are the most talked-about posts right now. Join a few channels and this page becomes yours.') }}</p>
            @endif
            <div class="hero-stats">
                <a href="{{ route('user.explore-channels') }}" class="hero-stat"><i class="bi bi-broadcast"></i> <b>{{ $stats['channels'] }}</b> {{ __('channels') }}</a>
                <a href="{{ route('user.notifications') }}" class="hero-stat"><i class="bi bi-bell"></i> <b>{{ $stats['unread'] }}</b> {{ __('unread') }}</a>
                <a href="{{ route('collections.index') }}" class="hero-stat"><i class="bi bi-bookmark-heart"></i> <b>{{ $collections->count() }}</b> {{ __('collections') }}</a>
            </div>
        </div>
        <x-illustration name="sunrise" size="132" data-daytime="day" :hidden="! $day" />
        <x-illustration name="night" size="132" data-daytime="night" :hidden="$day" />
    </section>

    <nav class="seg" aria-label="{{ __('Feed') }}">
        <div class="seg-inner">
            @if ($hasFeed)
                <a href="{{ route('user.feed', ['tab' => 'for-you']) }}" class="{{ $tab === 'for-you' ? 'active' : '' }}" @if ($tab === 'for-you') aria-current="page" @endif><i class="bi bi-house-heart"></i> {{ __('For you') }}</a>
            @endif
            <a href="{{ route('user.feed', ['tab' => 'popular']) }}" class="{{ $tab === 'popular' ? 'active' : '' }}" @if ($tab === 'popular') aria-current="page" @endif><i class="bi bi-fire"></i> {{ __('Popular') }}</a>
            <a href="{{ route('user.explore-channels') }}"><i class="bi bi-compass"></i> {{ __('Channels') }}</a>
        </div>
    </nav>

    <!-- Collections (Instagram-Highlight style personal folders) -->
    <div class="collections-section">
        <div class="collections-scroll" id="collectionsScroll">
            @foreach($collections as $collection)
                <div class="collection-item"
                     data-id="{{ $collection->id }}"
                     data-name="{{ $collection->name }}"
                     data-icon="{{ $collection->icon }}"
                     data-color="{{ $collection->color }}"
                     data-pinned="{{ $collection->is_pinned ? '1' : '0' }}"
                     data-muted="{{ $collection->is_muted ? '1' : '0' }}"
                     draggable="true"
                     onclick="openCollection(this)"
                     ontouchstart="startLongPress(event, this)"
                     ontouchend="cancelLongPress()"
                     ontouchmove="cancelLongPress()"
                     onmousedown="startLongPress(event, this)"
                     onmouseup="cancelLongPress()"
                     onmouseleave="cancelLongPress()"
                     oncontextmenu="event.preventDefault(); openSheet(this); return false;">
                    <div class="collection-avatar-wrap">
                        <div class="collection-avatar {{ $collection->is_pinned ? 'pinned' : '' }}" style="background-color: {{ $collection->color }};">
                            {{ $collection->icon }}
                        </div>
                        @if($collection->is_muted)
                            <span class="collection-mute-badge"><i class="bi bi-bell-slash-fill"></i></span>
                        @endif
                        @if($collection->channels_count > 0)
                            <span class="collection-badge">{{ $collection->channels_count }}</span>
                        @endif
                    </div>
                    <div class="collection-name">{{ $collection->name }}</div>
                </div>
            @endforeach

            <div class="collection-item collection-add" onclick="openCreateModal()">
                <div class="collection-avatar-wrap">
                    <div class="collection-avatar"><i class="bi bi-plus-lg"></i></div>
                </div>
                <div class="collection-name">{{ __('New') }}</div>
            </div>
        </div>
    </div>


    @unless ($hasFeed)
        <div class="nudge reveal">
            <x-illustration name="channels" size="70" />
            <div><strong>{{ __('Make it yours') }}</strong>{{ __('Join a channel and this page turns into your personal feed.') }}</div>
        </div>
        @if ($suggested->isNotEmpty())
            <section class="sec reveal" aria-labelledby="sugTitle">
                <div class="sec-head"><h2 id="sugTitle">{{ __('Channels to start with') }}</h2><a href="{{ route('user.explore-channels') }}">{{ __('See all') }}</a></div>
                <div class="hscroll">
                    @foreach ($suggested as $channel)
                        <a href="{{ route('guest.channel-detail', [$channel->organization_id, $channel->slug]) }}" class="sug" data-tip="{{ $channel->name }}" data-tip-sub="{{ $channel->organization?->name }}">
                            <div class="sug-av" style="--av: {{ $colorFor($channel->name) }};">{{ mb_substr($channel->name, 0, 1) }}</div>
                            <div class="sug-name">{{ $channel->name }}</div>
                            <div class="sug-org">{{ $channel->organization?->name }}</div>
                            <div class="sug-foot"><span><i class="bi bi-people"></i> {{ $channel->users_count }}</span><span class="sug-go">{{ __('Open') }}</span></div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    @endunless

    <section class="sec" aria-labelledby="feedTitle">
        <div class="sec-head"><h2 id="feedTitle">{{ $tab === 'for-you' ? __('Latest from your channels') : __('Popular right now') }}</h2></div>

        @if ($posts->isEmpty())
            <div class="feed-empty reveal">
                <x-illustration :name="$tab === 'for-you' ? 'empty' : 'search'" size="170" />
                <h3>{{ $tab === 'for-you' ? __('Your channels are quiet for now') : __('Nothing here yet') }}</h3>
                <p>{{ $tab === 'for-you' ? __('New posts from the channels you joined will land here. Meanwhile, see what\'s popular.') : __('Organizations haven\'t posted publicly yet. Check back soon!') }}</p>
                @if ($tab === 'for-you')<a href="{{ route('user.feed', ['tab' => 'popular']) }}" class="pill-btn"><i class="bi bi-fire"></i> {{ __('See popular posts') }}</a>@endif
            </div>
        @else
            @include('partials.feed._list', [
                'posts' => $posts,
                'endTitle' => $tab === 'for-you' ? __('You\'re all caught up!') : __('You\'ve seen the best of today'),
                'endText' => $tab === 'for-you' ? __('You\'ve seen every new post from your channels.') : __('Come back later for a fresh mix, or explore channels to follow.'),
                'endUrl' => $tab === 'for-you' ? route('user.feed', ['tab' => 'popular']) : route('user.explore-channels'),
                'endLabel' => $tab === 'for-you' ? __('See popular posts') : __('Explore channels'),
                'endIcon' => $tab === 'for-you' ? 'fire' : 'compass',
            ])
        @endif
    </section>
</div>
@endsection
@section('modals')

    <!-- Slide-in Drawer -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="toggleDrawer(false)"></div>
    <div class="drawer" id="drawer">
        <div class="drawer-user">
            <div class="drawer-avatar">{{ auth()->user()->initials }}</div>
            <div>
                <div style="font-weight: var(--font-weight-bold); font-size: var(--text-sm);">{{ auth()->user()->full_name }}</div>
                <div style="font-size: var(--text-xs); color: var(--text-tertiary);">{{ auth()->user()->email }}</div>
            </div>
        </div>

        <a href="{{ route('user.feed') }}" class="drawer-link"><i class="bi bi-house"></i> {{ __('Home') }}</a>
        <a href="{{ route('user.explore-channels') }}" class="drawer-link"><i class="bi bi-grid"></i> {{ __('Explore Channels') }}</a>
        <a href="{{ route('user.explore-organizations') }}" class="drawer-link"><i class="bi bi-building"></i> {{ __('Explore Organizations') }}</a>
        <a href="{{ route('user.notifications') }}" class="drawer-link"><i class="bi bi-bell"></i> {{ __('Notifications') }}</a>
        <a href="{{ route('profile.settings') }}" class="drawer-link"><i class="bi bi-gear"></i> {{ __('Settings') }}</a>

        @if(auth()->user()->hasRole('super_admin'))
            <a href="{{ route('admin.dashboard') }}" class="drawer-link"><i class="bi bi-shield-check"></i> {{ __('Admin Panel') }}</a>
        @endif
        @if(auth()->user()->organizations()->exists())
            <a href="{{ route('organizations.dashboard') }}" class="drawer-link"><i class="bi bi-graph-up"></i> {{ __('Organization Dashboard') }}</a>
        @endif

        <div style="flex: 1;"></div>

        <form id="logout-form-drawer" method="POST" action="{{ route('logout') }}">
            @csrf
        </form>
        <button type="button" class="drawer-link" style="border: none; background: none; width: 100%; cursor: pointer; color: var(--danger-600);" onclick="document.getElementById('logout-form-drawer').submit();">
            <i class="bi bi-box-arrow-right"></i> {{ __('Logout') }}
        </button>
    </div>

    <!-- Collection long-press bottom sheet -->
    <div class="sheet-overlay" id="collectionSheet" onclick="if(event.target===this) closeSheet()">
        <div class="bottom-sheet">
            <div class="sheet-handle"></div>
            <div class="sheet-title">
                <span class="sheet-icon-preview" id="sheetIconPreview"></span>
                <span id="sheetCollectionName"></span>
            </div>
            <button type="button" class="sheet-action" onclick="openEditModal('name')">
                <i class="bi bi-pencil"></i> {{ __('Rename') }}
            </button>
            <button type="button" class="sheet-action" onclick="openEditModal('icon')">
                <i class="bi bi-emoji-smile"></i> {{ __('Change Icon') }}
            </button>
            <button type="button" class="sheet-action" onclick="openEditModal('color')">
                <i class="bi bi-palette"></i> {{ __('Change Color') }}
            </button>
            <button type="button" class="sheet-action" id="sheetPinAction" onclick="sheetTogglePin()">
                <i class="bi bi-pin-angle"></i> <span id="sheetPinLabel">{{ __('Pin Collection') }}</span>
            </button>
            <button type="button" class="sheet-action" id="sheetMuteAction" onclick="sheetToggleMute()">
                <i class="bi bi-bell-slash"></i> <span id="sheetMuteLabel">{{ __('Mute Notifications') }}</span>
            </button>
            <button type="button" class="sheet-action" onclick="closeSheet(); alert('{{ __('Drag a collection circle to reorder.') }}')">
                <i class="bi bi-arrows-move"></i> {{ __('Reorder') }}
            </button>
            <button type="button" class="sheet-action danger" onclick="sheetDelete()">
                <i class="bi bi-trash"></i> {{ __('Delete') }}
            </button>
        </div>
    </div>

    <!-- Create / Edit Collection Modal -->
    <div class="cmodal-overlay" id="collectionModal" onclick="if(event.target===this) closeCreateModal()">
        <div class="cmodal">
            <h3 id="cmodalTitle">{{ __('New Collection') }}</h3>

            <div class="cmodal-preview" id="cmodalPreview" style="background-color: #4557f5;">💼</div>

            <form id="collectionForm" method="POST" onsubmit="submitCollectionForm(event)">
                @csrf
                <input type="hidden" name="_method" id="cmodalMethod" value="POST">
                <input type="hidden" name="icon" id="cmodalIconInput" value="💼">
                <input type="hidden" name="color" id="cmodalColorInput" value="#4557f5">

                <label>{{ __('Name') }}</label>
                <input type="text" name="name" id="cmodalNameInput" placeholder="{{ __('e.g. Work, Shopping, University') }}" maxlength="60" required>

                <label>{{ __('Icon') }}</label>
                <div class="icon-grid" id="cmodalIconGrid">
                    @foreach($icons ?? ['💼','🛒','🎓','🏋️','⚽','🍔','🏥','🏦','🎮','🎵','🎬','📰','✈️','❤️','📚','💻','📦','🛢','📁'] as $icon)
                        <div class="icon-swatch" data-icon="{{ $icon }}" onclick="selectIcon('{{ $icon }}')">{{ $icon }}</div>
                    @endforeach
                </div>

                <label>{{ __('Color') }}</label>
                <div class="color-grid" id="cmodalColorGrid">
                    @foreach(($colors ?? ['#4557f5'=>'Blue','#059669'=>'Green','#d97706'=>'Orange','#7c3aed'=>'Purple','#dc2626'=>'Red','#0d9488'=>'Teal','#db2777'=>'Pink','#f59e0b'=>'Amber']) as $hex => $label)
                        <div class="color-swatch" data-color="{{ $hex }}" style="background-color: {{ $hex }};" title="{{ $label }}" onclick="selectColor('{{ $hex }}')"></div>
                    @endforeach
                </div>

                <div class="cmodal-actions">
                    <button type="button" class="btn-cancel" onclick="closeCreateModal()">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-save">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
@include('partials.feed._script')
<script>
    function toggleDrawer(show) {
        document.getElementById('drawer').classList.toggle('show', show);
        document.getElementById('drawerOverlay').classList.toggle('show', show);
    }

    function toggleAppMenu() {
        const menu = document.getElementById('appMenu');
        if (menu) { menu.classList.toggle('show'); return; }

        const el = document.createElement('div');
        el.id = 'appMenu';
        el.className = 'app-menu show';
        el.innerHTML = `
            <button type="button" onclick="toggleShellTheme()"><i class="bi bi-moon-stars"></i> {{ __('Toggle Theme') }}</button>
            <a href="{{ route('profile.settings') }}"><i class="bi bi-gear"></i> {{ __('Settings') }}</a>
        `;
        document.querySelector('.app-bar-actions').appendChild(el);

        document.addEventListener('click', function closeMenu(e) {
            if (!el.contains(e.target) && !e.target.closest('.app-bar-icon-btn')) {
                el.classList.remove('show');
                document.removeEventListener('click', closeMenu);
            }
        });
    }

    /* ===================== Collections ===================== */
    const collectionsBaseUrl = "{{ url('/collections') }}";
    const collectionsStoreUrl = "{{ route('collections.store') }}";
    const collectionsReorderUrl = "{{ route('collections.reorder') }}";

    function csrfToken() {
        return document.querySelector('#collectionForm input[name="_token"]').value;
    }

    async function apiRequest(url, method, body) {
        const isForm = body instanceof FormData;
        if (isForm) { body.append('_token', csrfToken()); }

        const res = await fetch(url, {
            method: method === 'GET' ? 'GET' : 'POST',
            headers: isForm ? { 'X-Requested-With': 'XMLHttpRequest' } : {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: isForm ? body : JSON.stringify(body || {}),
        });
        return res;
    }

    // --- Long press detection ---
    let longPressTimer = null;
    let longPressFired = false;

    function startLongPress(event, el) {
        longPressFired = false;
        longPressTimer = setTimeout(() => {
            longPressFired = true;
            if (navigator.vibrate) navigator.vibrate(15);
            openSheet(el);
        }, 500);
    }

    function cancelLongPress() {
        clearTimeout(longPressTimer);
    }

    function openCollection(el) {
        if (longPressFired) { longPressFired = false; return; }
        const id = el.dataset.id;
        window.location.href = `${collectionsBaseUrl}/${id}`;
    }

    // --- Bottom sheet ---
    let activeCollection = null;

    function openSheet(el) {
        activeCollection = {
            id: el.dataset.id,
            name: el.dataset.name,
            icon: el.dataset.icon,
            color: el.dataset.color,
            pinned: el.dataset.pinned === '1',
            muted: el.dataset.muted === '1',
        };

        document.getElementById('sheetIconPreview').textContent = activeCollection.icon;
        document.getElementById('sheetIconPreview').style.backgroundColor = activeCollection.color;
        document.getElementById('sheetCollectionName').textContent = activeCollection.name;
        document.getElementById('sheetPinLabel').textContent = activeCollection.pinned ? '{{ __('Unpin Collection') }}' : '{{ __('Pin Collection') }}';
        document.getElementById('sheetMuteLabel').textContent = activeCollection.muted ? '{{ __('Unmute Notifications') }}' : '{{ __('Mute Notifications') }}';

        document.getElementById('collectionSheet').classList.add('show');
    }

    function closeSheet() {
        document.getElementById('collectionSheet').classList.remove('show');
    }

    async function sheetTogglePin() {
        await apiRequest(`${collectionsBaseUrl}/${activeCollection.id}/pin`, 'POST', new FormData());
        closeSheet();
        window.location.reload();
    }

    async function sheetToggleMute() {
        await apiRequest(`${collectionsBaseUrl}/${activeCollection.id}/mute`, 'POST', new FormData());
        closeSheet();
        window.location.reload();
    }

    async function sheetDelete() {
        if (!confirm('{{ __('Delete this collection? Channels stay subscribed, only the folder is removed.') }}')) return;
        const fd = new FormData();
        fd.append('_method', 'DELETE');
        await apiRequest(`${collectionsBaseUrl}/${activeCollection.id}`, 'POST', fd);
        closeSheet();
        window.location.reload();
    }

    // --- Create / Edit modal ---
    let modalMode = 'create';

    function openCreateModal() {
        modalMode = 'create';
        document.getElementById('cmodalTitle').textContent = '{{ __('New Collection') }}';
        document.getElementById('cmodalMethod').value = 'POST';
        document.getElementById('collectionForm').action = collectionsStoreUrl;
        document.getElementById('cmodalNameInput').value = '';
        selectIcon('💼');
        selectColor('#4557f5');
        document.getElementById('collectionModal').classList.add('show');
    }

    function openEditModal(focusField) {
        closeSheet();
        modalMode = 'edit';
        document.getElementById('cmodalTitle').textContent = '{{ __('Edit Collection') }}';
        document.getElementById('cmodalMethod').value = 'PUT';
        document.getElementById('collectionForm').action = `${collectionsBaseUrl}/${activeCollection.id}`;
        document.getElementById('cmodalNameInput').value = activeCollection.name;
        selectIcon(activeCollection.icon);
        selectColor(activeCollection.color);
        document.getElementById('collectionModal').classList.add('show');

        setTimeout(() => {
            if (focusField === 'name') document.getElementById('cmodalNameInput').focus();
        }, 100);
    }

    function closeCreateModal() {
        document.getElementById('collectionModal').classList.remove('show');
    }

    function selectIcon(icon) {
        document.getElementById('cmodalIconInput').value = icon;
        document.getElementById('cmodalPreview').textContent = icon;
        document.querySelectorAll('#cmodalIconGrid .icon-swatch').forEach(s => {
            s.classList.toggle('selected', s.dataset.icon === icon);
        });
    }

    function selectColor(color) {
        document.getElementById('cmodalColorInput').value = color;
        document.getElementById('cmodalPreview').style.backgroundColor = color;
        document.querySelectorAll('#cmodalColorGrid .color-swatch').forEach(s => {
            s.classList.toggle('selected', s.dataset.color === color);
        });
    }

    async function submitCollectionForm(event) {
        event.preventDefault();
        const form = document.getElementById('collectionForm');
        const fd = new FormData(form);
        if (modalMode === 'edit') { fd.append('_method', 'PUT'); }

        await fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
        });

        closeCreateModal();
        window.location.reload();
    }

    // --- Drag & drop reorder (desktop) ---
    (function initDragReorder() {
        const scroll = document.getElementById('collectionsScroll');
        if (!scroll) return;

        let dragEl = null;

        scroll.addEventListener('dragstart', (e) => {
            const item = e.target.closest('.collection-item:not(.collection-add)');
            if (!item) { e.preventDefault(); return; }
            dragEl = item;
            item.classList.add('dragging');
        });

        scroll.addEventListener('dragend', () => {
            if (dragEl) dragEl.classList.remove('dragging');
            dragEl = null;
            persistOrder();
        });

        scroll.addEventListener('dragover', (e) => {
            e.preventDefault();
            const afterEl = getDragAfterElement(scroll, e.clientX);
            if (!dragEl) return;
            const addTile = scroll.querySelector('.collection-add');
            if (afterEl == null) {
                scroll.insertBefore(dragEl, addTile);
            } else {
                scroll.insertBefore(dragEl, afterEl);
            }
        });

        function getDragAfterElement(container, x) {
            const items = [...container.querySelectorAll('.collection-item:not(.dragging):not(.collection-add)')];
            return items.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = x - box.left - box.width / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset, element: child };
                }
                return closest;
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        async function persistOrder() {
            const ids = [...scroll.querySelectorAll('.collection-item:not(.collection-add)')].map(el => el.dataset.id);
            if (ids.length === 0) return;
            await apiRequest(collectionsReorderUrl, 'POST', { order: ids });
        }
    })();
</script>
@endsection
