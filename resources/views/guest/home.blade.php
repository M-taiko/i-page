@extends('layouts.mobile-shell')

@section('title', ($tab === 'for-you' ? __('Your feed') : __('Discover hotels, stores, schools & more')) . ' · i-page')
@section('meta_description', __('Follow the places you love — hotels, stores, schools and more — and get their news, offers and events in one feed. Scan a QR code to join any channel.'))

@section('app-bar')
    <div class="app-bar-title">
        <a href="{{ route('guest.home') }}" aria-label="{{ __('i-page home') }}"><x-brand-logo animate="idle" :height="30" /></a>
    </div>
    <div class="app-bar-actions">
        <a href="{{ route('guest.search-organizations') }}" class="app-bar-icon-btn" aria-label="{{ __('Search') }}">
            <i class="bi bi-search"></i>
        </a>
    </div>
@endsection

@section('extra-styles')
    .home { padding-bottom: 96px; }
    @include('partials.feed._styles')

    /* Mobile discovery: readable cards, clear search and generous touch targets. */
    .home{max-width:1000px;margin:auto;padding:16px 0 110px}
    .home .hero{margin:0 16px 24px;padding:28px;border-radius:28px;background:#6755e7;color:#fff;position:relative;overflow:hidden;box-shadow:0 16px 40px #6755e720}
    .home .hero h1{font-size:clamp(27px,5vw,38px);line-height:1.2;letter-spacing:-.025em}
    .home .hero p{color:#eeebff;line-height:1.7;max-width:460px}
    .home .hero-kicker{color:#d7f58a;font-size:12px;font-weight:700;letter-spacing:.04em}
    .home .hero-cta{background:#fff;color:#4936ba;border-radius:14px;padding:12px 18px;min-height:44px}
    .discover-search{margin:0 16px 24px;display:flex;align-items:center;gap:12px;padding:17px 18px;border:1px solid var(--surface-border);border-radius:18px;background:var(--surface-bg);color:var(--text-secondary);text-decoration:none;box-shadow:0 6px 20px #14142b05}
    .discover-search i{color:#6755e7;font-size:20px}.discover-search .search-arrow{margin-inline-start:auto}
    .home .sec{margin-block:28px}.home .sec-head{padding-inline:18px;margin-bottom:16px}.home .sec-head h2{font-size:19px;font-weight:750;letter-spacing:-.02em}
    .home .hscroll{gap:14px;padding:4px 16px 18px;scroll-snap-type:x mandatory}
    .home .org{min-width:126px;padding:18px 12px;border:1px solid var(--surface-border);border-radius:22px;background:var(--surface-bg);scroll-snap-align:start}
    .home .org-ring{background:none;padding:0}.home .org-av{border-radius:20px}.home .org-name{font-size:13px;margin-top:12px}
    .home .sug{min-width:195px;border-radius:24px;padding:20px;border:1px solid var(--surface-border);background:var(--surface-bg);box-shadow:0 8px 24px #19152d07;scroll-snap-align:start}
    .home .sug-av{width:60px;height:60px;border-radius:20px}.home .sug-name{font-size:17px;margin-top:16px}.home .sug-foot{margin-top:20px}.home .sug-go{background:#eeebff;color:#5944ce;border-radius:10px;padding:8px 14px}
    .home .nudge{margin:24px 16px;border-radius:22px;background:var(--surface-bg);border:1px solid var(--surface-border);padding:20px}
    .home .seg{margin:0 16px}.home .seg-inner{border-radius:16px;padding:5px}.home .seg a{min-height:44px;border-radius:12px}
    @media(max-width:480px){.home .hero{padding:24px;display:block}.home .hero> :last-child:not(.hero-text){position:absolute;width:90px;height:90px;opacity:.16;inset-inline-end:4px;top:8px;pointer-events:none}.home .hero-text{position:relative;z-index:1}.home .hero h1{max-width:280px}.home .hero p{font-size:14px}.home .hero-cta{display:inline-flex}.home .stories{padding-block:8px 18px}}
    .hscroll:has(.discover-channel){display:flex;flex-wrap:wrap;overflow:visible}.discover-channel{flex:1 1 230px;min-width:0;border:1px solid var(--surface-border);border-radius:22px;overflow:hidden;background:var(--surface-bg)}.discover-channel.expanded{flex-basis:100%}.channel-preview{display:block;position:relative;width:100%;height:190px;border:0;padding:0;background:#526f60;cursor:pointer;text-align:start;transition:height .35s ease}.expanded .channel-preview{height:340px}.channel-cover{width:100%;height:100%;object-fit:cover}.channel-overlay{position:absolute;inset:0;background:linear-gradient(transparent 35%,#10251ed9)}.channel-identity{position:absolute;bottom:18px;inset-inline:18px;display:flex;align-items:center;gap:12px;color:white;font-size:18px}.channel-identity img{width:52px;height:52px;object-fit:cover;border:2px solid white;border-radius:16px}.expanded .channel-identity img{width:76px;height:76px}.channel-breadcrumbs{display:flex;flex-wrap:wrap;gap:6px;padding:16px 16px 8px;font-size:12px}.channel-breadcrumbs a{color:var(--text-primary)}.channel-breadcrumbs a:hover{text-decoration:underline}.channel-hint{display:block;padding:0 16px 16px;color:var(--text-secondary);font-size:11px}@media(max-width:480px){.discover-channel{flex-basis:calc(50% - 14px)}.channel-preview{height:170px}.expanded .channel-preview{height:290px}}@media(prefers-reduced-motion:reduce){.channel-preview{transition:none}}
    .home .org-av{overflow:hidden}.home .org-av img{width:100%;height:100%;object-fit:cover;display:block}
    .home .hscroll:has(.discover-channel){flex-wrap:nowrap;overflow-x:auto;overflow-y:hidden;align-items:flex-start;scroll-snap-type:x proximity;scroll-padding-inline:16px;overscroll-behavior-inline:contain;padding-bottom:20px}
    .discover-channel{flex:0 0 min(260px,78vw);scroll-snap-align:start;transition:flex-basis .6s cubic-bezier(.22,1,.36,1),box-shadow .6s ease}
    .discover-channel.expanded{flex-basis:var(--channel-expanded-width,calc(100% - 32px));box-shadow:0 12px 32px #142c2020}
    .channel-preview{transition:height .6s cubic-bezier(.22,1,.36,1)}.channel-identity img{transition:width .6s cubic-bezier(.22,1,.36,1),height .6s cubic-bezier(.22,1,.36,1)}
    @media(max-width:480px){.discover-channel{flex-basis:78vw}.discover-channel.expanded{flex-basis:var(--channel-expanded-width,calc(100% - 32px))}}
    @media(prefers-reduced-motion:reduce){.discover-channel,.channel-preview,.channel-identity img{transition:none}}
    /* Expansion changes width only; image, avatar and text regions keep their height. */
    .discover-channel .channel-preview,.discover-channel.expanded .channel-preview{height:190px;transition:none}
    .discover-channel .channel-identity img,.discover-channel.expanded .channel-identity img{width:52px;height:52px;transition:none;flex-shrink:0}
    .channel-identity strong{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .discover-channel .channel-breadcrumbs{height:64px;box-sizing:border-box;align-content:flex-start;overflow-y:auto}
    .discover-channel .channel-hint{height:42px;box-sizing:border-box;overflow:hidden}
    @media(max-width:480px){.discover-channel .channel-preview,.discover-channel.expanded .channel-preview{height:170px}}
    .organization-preview .organization-cover{filter:blur(3px);transform:scale(1.06)}
    .organization-preview .channel-overlay{background:linear-gradient(180deg,#10201d44,#10201dcc)}
    .organization-identity{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:20px;color:white;text-align:center}
    .organization-logo{width:64px;height:64px;flex-shrink:0;background:var(--surface-bg);border:3px solid #fff;border-radius:19px;overflow:hidden;display:grid;place-items:center;font-size:24px;color:var(--text-primary);box-shadow:0 5px 18px #0003}
    .organization-logo img{width:100%;height:100%;object-fit:cover}.organization-identity strong{font-size:16px;line-height:22px;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.organization-identity small{font-size:12px;opacity:.85}
    @media(max-width:700px){
        .discover-channel.organization-preview{flex-basis:150px;border-radius:18px}
        .discover-channel.organization-preview.expanded{flex-basis:var(--channel-expanded-width,calc(100% - 32px))}
        .organization-preview .channel-preview,.organization-preview.expanded .channel-preview{height:128px}
        .organization-preview .organization-identity{padding:8px;gap:4px}
        .organization-preview .organization-logo{width:40px;height:40px;border-radius:12px;border-width:2px}
        .organization-preview .organization-identity strong{font-size:13px;line-height:17px;white-space:normal;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;height:34px}
        .organization-preview .organization-identity small{font-size:11px}
    }
    /* Collections as Instagram-style story circles */
    .stories { display: flex; gap: 14px; overflow-x: auto; padding: 6px 16px 12px; scroll-snap-type: x proximity; scrollbar-width: none; }
    .stories::-webkit-scrollbar { display: none; }
    .story { position: relative; flex: 0 0 auto; width: 70px; display: flex; flex-direction: column; align-items: center; gap: 6px; text-decoration: none; scroll-snap-align: start; }
    .story-ring { width: 66px; height: 66px; padding: 3px; border-radius: 50%; background: color-mix(in srgb, var(--sc) 45%, var(--surface-border)); transition: transform .2s ease; }
    .story.is-new .story-ring { background: conic-gradient(from 210deg, #fbbf24, #f43f5e, #a855f7, var(--sc), #fbbf24); }
    .story-face { width: 100%; height: 100%; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #fff;
        background: radial-gradient(circle at 30% 25%, color-mix(in srgb, var(--sc) 65%, #fff), var(--sc)); border: 3px solid var(--surface-bg-secondary, #f8fafc); }
    .story:hover .story-ring { transform: scale(1.06); }
    .story:active .story-ring { transform: scale(.95); }
    .story:focus-visible { outline: 0; }
    .story:focus-visible .story-ring { box-shadow: 0 0 0 3px var(--primary-500); }
    .story-name { max-width: 74px; font-size: 11.5px; font-weight: 600; line-height: 1.25; color: var(--text-primary); text-align: center;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .story-dot { position: absolute; top: 2px; inset-inline-end: 4px; width: 14px; height: 14px; border-radius: 50%; background: #22c55e; border: 2.5px solid var(--surface-bg-secondary, #f8fafc); }
    .story-add .story-ring { background: none; border: 2px dashed var(--surface-border); padding: 2px; }
    .story-add .story-face { background: var(--surface-bg); color: var(--primary-600); font-size: 22px; border: 0; }
    .story.is-new .story-ring { animation: storyGlow 2.6s ease-in-out infinite; }
    @keyframes storyGlow { 0%, 100% { filter: none; } 50% { filter: drop-shadow(0 0 6px rgba(244, 63, 94, .45)); } }
    @media (prefers-reduced-motion: reduce) { .story.is-new .story-ring { animation: none; } .story-ring { transition: none; } }
@endsection

@section('content')
@php
    $palette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777', '#0891b2'];
    $colorFor = fn ($seed) => $palette[crc32((string) $seed) % count($palette)];
    $hour = (int) now()->format('G');
    $greeting = $hour >= 5 && $hour < 12 ? __('Good morning') : ($hour >= 12 && $hour < 17 ? __('Good afternoon') : __('Good evening'));
    $user = auth()->user();
@endphp
<div class="home">
    {{-- Hero --}}
    <section class="hero reveal">
        <div class="hero-text">
            @if ($tab === 'for-you')
                <div class="hero-kicker"><span class="js-greeting" data-morning="{{ __('Good morning') }}" data-afternoon="{{ __('Good afternoon') }}" data-evening="{{ __('Good evening') }}">{{ $greeting }}</span>{{ $user ? ', ' . $user->first_name : '' }} 👋</div>
                <h1>{{ __('Your feed is ready') }}</h1>
                <p>{{ trans_choice('Fresh news from the :count channel you joined.|Fresh news from the :count channels you joined.', $joinedCount, ['count' => $joinedCount]) }}</p>
                <a href="{{ route('guest.home', ['tab' => 'discover']) }}" class="hero-cta"><i class="bi bi-compass"></i> {{ __('Discover more') }}</a>
            @else
                <div class="hero-kicker">@if ($user)<span class="js-greeting" data-morning="{{ __('Good morning') }}" data-afternoon="{{ __('Good afternoon') }}" data-evening="{{ __('Good evening') }}">{{ $greeting }}</span>, {{ $user->first_name }} 👋@else{{ __('Welcome to i-Page') }}@endif</div>
                <h1>{{ __('Discover what\'s happening') }}</h1>
                <p>{{ __('Hotels, stores, schools and more share their news here. Follow the channels you like to build your own feed.') }}</p>
                @guest
                    <a href="{{ route('register') }}" class="hero-cta"><i class="bi bi-person-plus"></i> {{ __('Create a free account') }}</a>
                @else
                    <a href="{{ route('guest.search-organizations') }}" class="hero-cta"><i class="bi bi-building"></i> {{ __('Find organizations') }}</a>
                @endguest
            @endif
        </div>
        <x-illustration :name="$tab === 'for-you' ? 'feed' : 'discover'" size="132" />
    </section>

    <a class="discover-search" href="{{ route('guest.search-organizations') }}"><i class="bi bi-search" aria-hidden="true"></i><span>{{ __('Search') }} · {{ __('Organizations') }}</span><i class="bi bi-arrow-up-right search-arrow" aria-hidden="true"></i></a>
    {{-- Collections as story circles (members), docs/27 --}}
    @auth
        <nav class="stories reveal" aria-label="{{ __('My collections') }}">
            @foreach ($collections as $collection)
                <a href="{{ route('collections.show', $collection->id) }}" class="story {{ $collection->has_new ? 'is-new' : '' }}"
                   style="--sc: {{ $collection->color ?: '#6d5dfc' }};"
                   data-tip="{{ $collection->name }}"
                   data-tip-sub="{{ trans_choice(':count channel|:count channels', $collection->channels_count) }}{{ $collection->has_new ? ' · ' . __('new posts') : '' }}">
                    <span class="story-ring"><span class="story-face">{{ $collection->icon ?: '📁' }}</span></span>
                    <span class="story-name">{{ $collection->name }}</span>
                    @if ($collection->has_new)<span class="story-dot" aria-label="{{ __('New posts') }}"></span>@endif
                </a>
            @endforeach
            <a href="{{ route('collections.index') }}" class="story story-add" data-tip="{{ __('New collection') }}" data-tip-sub="{{ __('Group the channels you like') }}">
                <span class="story-ring"><span class="story-face"><i class="bi bi-plus-lg"></i></span></span>
                <span class="story-name">{{ __('New') }}</span>
            </a>
        </nav>
    @endauth

    {{-- Tabs (only when there is a personal feed to switch to) --}}
    @if ($hasFeed)
        <nav class="seg" aria-label="{{ __('Feed') }}">
            <div class="seg-inner">
                <a href="{{ route('guest.home', ['tab' => 'for-you']) }}" class="{{ $tab === 'for-you' ? 'active' : '' }}" @if ($tab === 'for-you') aria-current="page" @endif><i class="bi bi-house-heart"></i> {{ __('For you') }}</a>
                <a href="{{ route('guest.home', ['tab' => 'discover']) }}" class="{{ $tab === 'discover' ? 'active' : '' }}" @if ($tab === 'discover') aria-current="page" @endif><i class="bi bi-shuffle"></i> {{ __('Discover') }}</a>
            </div>
        </nav>
    @endif

    @if ($tab === 'discover')
        {{-- Organizations --}}
        @if ($organizations->isNotEmpty())
            <section class="sec reveal" aria-labelledby="orgsTitle">
                <div class="sec-head">
                    <h2 id="orgsTitle">{{ __('Organizations') }}</h2>
                    <a href="{{ route('guest.search-organizations') }}">{{ __('See all') }}</a>
                </div>
                <div class="hscroll">
                    @foreach ($organizations as $org)
                        @php $orgCover=\App\Support\Designs::coverPaths($org->design_options??[])[0]??$org->logo_path; @endphp
                        <article class="discover-channel organization-preview">
                            <button type="button" class="channel-preview" aria-expanded="false" aria-label="{{ $org->name }}" data-url="{{ route('guest.organization-detail',$org->id) }}">
                                @if($orgCover)<img class="channel-cover organization-cover" src="{{ Storage::url($orgCover) }}" alt="" loading="lazy">@endif
                                <span class="channel-overlay"></span>
                                <span class="organization-identity">
                                    <span class="organization-logo">@if($org->logo_path)<img src="{{ Storage::url($org->logo_path) }}" alt="" loading="lazy">@else{{ mb_substr($org->name,0,1) }}@endif</span>
                                    <strong>{{ $org->name }}</strong>
                                    <small>{{ trans_choice(':count channel|:count channels',$org->channels_count) }}</small>
                                </span>
                            </button>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Channels to start with --}}
        @if ($suggested->isNotEmpty())
            <section class="sec reveal" aria-labelledby="sugTitle">
                <div class="sec-head"><h2 id="sugTitle">{{ $hasFeed ? __('More channels for you') : __('Channels to start with') }}</h2></div>
                <div class="hscroll">
                    @foreach ($suggested as $channel)
                        @include('guest.partials.channel-discovery')
                    @endforeach
                </div>
            </section>
        @endif

        @if (! $hasFeed)
            <div class="nudge reveal">
                <x-illustration name="channels" size="70" />
                <div><strong>{{ __('Make it yours') }}</strong>{{ __('Join a channel and this page turns into your personal feed.') }}</div>
            </div>
        @endif
    @endif

    {{-- Feed --}}
    <section class="sec" aria-labelledby="feedTitle">
        <div class="sec-head"><h2 id="feedTitle">{{ $tab === 'for-you' ? __('Latest from your channels') : __('Random picks') }}</h2></div>

        @if ($posts->isEmpty())
            <div class="feed-empty reveal">
                <x-illustration :name="$tab === 'for-you' ? 'empty' : 'search'" size="170" />
                <h3>{{ $tab === 'for-you' ? __('Your channels are quiet for now') : __('Nothing to discover yet') }}</h3>
                <p>{{ $tab === 'for-you' ? __('New posts from the channels you joined will land here. Meanwhile, see what others are sharing.') : __('Organizations haven\'t posted publicly yet. Check back soon!') }}</p>
                @if ($tab === 'for-you')<a href="{{ route('guest.home', ['tab' => 'discover']) }}" class="pill-btn"><i class="bi bi-compass"></i> {{ __('Discover posts') }}</a>@endif
            </div>
        @else
            @include('partials.feed._list', [
                'posts' => $posts,
                'endTitle' => $tab === 'for-you' ? __('You\'re all caught up!') : __('That\'s all for now'),
                'endText' => $tab === 'for-you' ? __('You\'ve seen every new post from your channels.') : __('Come back later for a fresh mix — or open a channel to see more.'),
                'endUrl' => route('guest.home', ['tab' => 'discover']),
                'endLabel' => $tab === 'for-you' ? __('Discover more') : __('Shuffle again'),
            ])
        @endif
    </section>
</div>
@endsection

@section('modals')
    <div class="signin-modal-overlay" id="signinModal" onclick="if(event.target===this) closeSignInModal()">
        <div class="signin-modal">
            <x-illustration name="notifications" size="120" />
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
@include('partials.feed._script')
<script>
    function requireSignIn() { document.getElementById('signinModal').classList.add('show'); }
    document.querySelectorAll('.hscroll:has(.discover-channel)').forEach(track=>{
        const reduce=matchMedia('(prefers-reduced-motion: reduce)');
        const size=()=>track.style.setProperty('--channel-expanded-width',Math.max(1,track.clientWidth-32)+'px');
        size();new ResizeObserver(size).observe(track);
        let hoverTimer,alignTimer;
        const expand=card=>{
            if(card.classList.contains('expanded'))return;
            track.querySelectorAll('.expanded').forEach(el=>{el.classList.remove('expanded');el.querySelector('button').setAttribute('aria-expanded','false')});
            card.classList.add('expanded');card.querySelector('button').setAttribute('aria-expanded','true');
            clearTimeout(alignTimer);
            alignTimer=setTimeout(()=>card.scrollIntoView({behavior:reduce.matches?'instant':'smooth',block:'nearest',inline:'start'}),reduce.matches?0:620);
        };
        track.querySelectorAll('.discover-channel').forEach(card=>{
            const button=card.querySelector('.channel-preview');
            button.addEventListener('click',()=>{
                clearTimeout(hoverTimer);
                if(card.classList.contains('expanded')){window.location.assign(button.dataset.url);return;}
                expand(card);
            });
            card.addEventListener('pointerenter',event=>{
                if(event.pointerType!=='mouse'||!matchMedia('(hover:hover)').matches)return;
                clearTimeout(hoverTimer);hoverTimer=setTimeout(()=>expand(card),220);
            });
            card.addEventListener('pointerleave',event=>{
                clearTimeout(hoverTimer);
                if(event.pointerType!=='mouse')return;
                clearTimeout(alignTimer);
                card.classList.remove('expanded');
                button.setAttribute('aria-expanded','false');
            });
            card.addEventListener('keydown',event=>{if(event.key==='Escape'){clearTimeout(alignTimer);card.classList.remove('expanded');button.setAttribute('aria-expanded','false');button.focus()}});
        });
    });
    function closeSignInModal() { document.getElementById('signinModal').classList.remove('show'); }

</script>
@endsection
