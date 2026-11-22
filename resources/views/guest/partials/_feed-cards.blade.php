{{-- Post cards (docs/27 "Compact Post Card"): the one card used by /browse, /feed, /saved, organization and channel pages.
     Header: round avatar, source name, "Today at 08:30 AM", pin marker and the manage menu.
     Body: the post in its template (compact by default). Footer: like, comments, share, save.
     Expects $posts (decorated by FeedService::decorate: like_count, comment_count, user_liked, user_saved).
     Optional $display = [show_author, show_date, show_reactions, show_comments, show_views, excerpt, card_style]
     and $context (the Channel being viewed, to label posts that come from elsewhere). --}}
@php
    $d = array_merge(['show_author' => true, 'show_date' => true, 'show_reactions' => true, 'show_comments' => true,
        'show_views' => false, 'excerpt' => 320, 'card_style' => null], $display ?? []);
    $context = $context ?? null;
    $palette = ['#1e3a8a', '#4338ca', '#047857', '#b45309', '#b91c1c', '#1d4ed8', '#be185d', '#0e7490'];
    $colorFor = fn ($seed) => $palette[crc32((string) $seed) % count($palette)];
@endphp
@foreach ($posts as $post)
    @php
        $isTenant = $post->isTenantPost();
        $org = $post->organization ?? $post->channel?->organization;
        $source = $post->sourceName() ?? __('i-Page');
        $sourceUrl = $isTenant || ! $post->channel
            ? ($org ? route('guest.organization-detail', $org->id) : '#')
            : route('guest.channel-detail', [$post->channel->organization_id, $post->channel->slug]);
        $postUrl = route('guest.post-detail', $post->id);
        $private = $post->channel?->type === 'private';
        $fromElsewhere = $context && $post->channel_id !== $context->id;
        $logo = $org?->logo_path ? \Illuminate\Support\Facades\Storage::url($org->logo_path) : null;
    @endphp
    <article class="fc reveal" data-post="{{ $post->id }}">
        <header class="fc-head">
            @if ($d['show_author'])
                <a href="{{ $sourceUrl }}" class="fc-avatar" style="--av: {{ $colorFor($org?->name ?? $source) }};" aria-hidden="true" tabindex="-1">
                    @if ($logo)<img src="{{ $logo }}" alt="">@else{{ mb_substr($org?->name ?? $source, 0, 1) }}@endif
                    @if ($isTenant)<span class="fc-verified" title="{{ __('Official post') }}"><i class="bi bi-patch-check-fill"></i></span>@endif
                </a>
            @endif
            <div class="fc-who">
                @if ($d['show_author'])<a href="{{ $sourceUrl }}" class="fc-source">{{ $source }}</a>@endif
                <div class="fc-meta">
                    @if ($d['show_date'])<time datetime="{{ $post->published_at?->toIso8601String() }}" title="{{ $post->published_at?->toDayDateTimeString() }}">{{ $post->whenLabel() }}</time>@endif
                    @if ($fromElsewhere)
                        <span class="fc-pill">{{ $isTenant ? '🏢 ' . __('From :tenant', ['tenant' => $org?->name]) : '↳ ' . __('From :channel', ['channel' => $post->channel?->name]) }}</span>
                    @elseif (! $context && ! $isTenant && $org && $org->name !== $source)
                        <span class="fc-org">{{ $org->name }}</span>
                    @endif
                    @if ($private)<span class="fc-pill fc-pill-lock"><i class="bi bi-lock-fill"></i> {{ __('Members') }}</span>@endif
                </div>
            </div>
            <div class="fc-tools">
                @if ($post->isPinned())
                    <span class="fc-pin" data-tip="{{ __('Pinned') }}" aria-label="{{ __('Pinned') }}"><i class="bi bi-pin-angle-fill"></i></span>
                @endif
                @include('posts.partials._manage')
            </div>
        </header>

        <div class="fc-body">
            @include('posts.partials._content', [
                'post' => $post, 'limit' => $d['excerpt'], 'url' => $postUrl,
                'showWidgets' => $post->hasWidgets() && ($post->post_format !== 'standard' || $d['show_reactions']),
                'cardStyle' => $d['card_style'],
                'hideReactions' => ! $d['show_reactions'],
            ])
        </div>

        <footer class="fc-actions">
            @if ($d['show_reactions'] && ! $post->hasWidgets())
                @auth
                    <form action="{{ route('posts.like', $post->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="fc-btn {{ $post->user_liked ? 'is-on' : '' }}" aria-pressed="{{ $post->user_liked ? 'true' : 'false' }}" aria-label="{{ __('Like') }}">
                            <i class="bi {{ $post->user_liked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }}"></i><span>{{ $post->like_count }}</span>
                        </button>
                    </form>
                @else
                    <button type="button" class="fc-btn" onclick="requireSignIn()" aria-label="{{ __('Like') }}"><i class="bi bi-hand-thumbs-up"></i><span>{{ $post->like_count }}</span></button>
                @endauth
            @endif
            @if ($d['show_comments'])
                <button type="button" class="fc-btn" @auth onclick="toggleFeedComments(this)" aria-expanded="false" @else onclick="requireSignIn()" @endauth aria-label="{{ __('Comments') }}">
                    <i class="bi bi-chat"></i><span>{{ $post->comment_count }}</span>
                </button>
            @endif
            @if ($d['show_views'])
                <span class="fc-views" title="{{ __('Views') }}"><i class="bi bi-eye"></i> {{ $post->views_count }}</span>
            @endif
            <span class="fc-spacer"></span>
            <button type="button" class="fc-icon" onclick="sharePost('{{ $postUrl }}', this)" aria-label="{{ __('Share') }}" data-tip="{{ __('Share') }}"><i class="bi bi-send"></i></button>
            @auth
                <button type="button" class="fc-icon fc-save {{ $post->user_saved ? 'is-on' : '' }}" data-save-url="{{ route('saved.toggle', $post->id) }}"
                        aria-pressed="{{ $post->user_saved ? 'true' : 'false' }}" aria-label="{{ __('Save') }}" data-tip="{{ $post->user_saved ? __('Saved') : __('Save') }}">
                    <i class="bi {{ $post->user_saved ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>
                </button>
            @else
                <button type="button" class="fc-icon" onclick="requireSignIn()" aria-label="{{ __('Save') }}"><i class="bi bi-bookmark"></i></button>
            @endauth
        </footer>

        @auth
            @if ($d['show_comments'])
                <div class="fc-comments" hidden>
                    @foreach ($post->comments()->approved()->with('user')->latest()->take(3)->get()->reverse() as $comment)
                        <div class="fc-comment">
                            <span class="fc-c-avatar" style="--av: {{ $colorFor($comment->user?->full_name) }};">{{ mb_substr($comment->user?->first_name ?? '?', 0, 1) }}</span>
                            <div class="fc-c-bubble"><strong>{{ $comment->user?->full_name }}</strong><p>{{ $comment->body }}</p></div>
                        </div>
                    @endforeach
                    <form action="{{ route('posts.comments.store', $post->id) }}" method="POST" class="fc-c-form">
                        @csrf
                        <input type="text" name="body" placeholder="{{ __('Write a comment…') }}" required maxlength="1000" aria-label="{{ __('Write a comment…') }}">
                        <button type="submit" aria-label="{{ __('Send') }}"><i class="bi bi-send-fill"></i></button>
                    </form>
                </div>
            @endif
        @endauth
    </article>
@endforeach
