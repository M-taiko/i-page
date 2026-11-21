{{-- Post cards for the /browse feed; rendered on first load and for each infinite-scroll batch.
     Expects $posts (with like_count, comment_count, views_count and, for members, user_liked). --}}
@php
    $palette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777', '#0891b2'];
    $colorFor = fn ($seed) => $palette[crc32((string) $seed) % count($palette)];
@endphp
@foreach ($posts as $post)
    @php
        $isTenant = $post->isTenantPost();
        $source = $post->sourceName() ?? __('i-Page');
        $org = $post->organization ?? $post->channel?->organization;
        $sourceUrl = $isTenant || ! $post->channel
            ? ($org ? route('guest.organization-detail', $org->id) : '#')
            : route('guest.channel-detail', [$post->channel->organization_id, $post->channel->slug]);
        $postUrl = route('guest.post-detail', $post->id);
        $private = $post->channel?->type === 'private';
    @endphp
    <article class="fc reveal" data-post="{{ $post->id }}">
        <header class="fc-head">
            <a href="{{ $sourceUrl }}" class="fc-avatar" style="--av: {{ $colorFor($source) }};" aria-hidden="true" tabindex="-1">
                {{ mb_substr($source, 0, 1) }}
                @if ($isTenant)<span class="fc-verified" title="{{ __('Official post') }}"><i class="bi bi-patch-check-fill"></i></span>@endif
            </a>
            <div class="fc-who">
                <a href="{{ $sourceUrl }}" class="fc-source">{{ $source }}</a>
                <div class="fc-meta">
                    @if (! $isTenant && $org)<a href="{{ route('guest.organization-detail', $org->id) }}" class="fc-org">{{ $org->name }}</a><span aria-hidden="true">·</span>@endif
                    <time datetime="{{ $post->published_at?->toIso8601String() }}">{{ $post->published_at?->diffForHumans() }}</time>
                    @if ($post->isPinned())<span class="fc-pill">📌 {{ __('Pinned') }}</span>@endif
                    @if ($private)<span class="fc-pill fc-pill-lock"><i class="bi bi-lock-fill"></i> {{ __('Members') }}</span>@endif
                </div>
            </div>
        </header>

        <div class="fc-body">
            @include('posts.partials._content', ['post' => $post, 'limit' => 320, 'url' => $postUrl, 'showWidgets' => $post->hasWidgets()])
        </div>

        <footer class="fc-actions">
            @auth
                @unless ($post->hasWidgets())
                    <form action="{{ route('posts.like', $post->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="fc-btn {{ $post->user_liked ? 'is-on' : '' }}" aria-pressed="{{ $post->user_liked ? 'true' : 'false' }}">
                            <i class="bi {{ $post->user_liked ? 'bi-heart-fill' : 'bi-heart' }}"></i><span>{{ $post->like_count }}</span>
                        </button>
                    </form>
                @endunless
                <button type="button" class="fc-btn" onclick="toggleFeedComments(this)" aria-expanded="false"><i class="bi bi-chat"></i><span>{{ $post->comment_count }}</span></button>
            @else
                @unless ($post->hasWidgets())<button type="button" class="fc-btn" onclick="requireSignIn()"><i class="bi bi-heart"></i><span>{{ $post->like_count }}</span></button>@endunless
                <button type="button" class="fc-btn" onclick="requireSignIn()"><i class="bi bi-chat"></i><span>{{ $post->comment_count }}</span></button>
            @endauth
            <button type="button" class="fc-btn" onclick="sharePost('{{ $postUrl }}', this)" aria-label="{{ __('Share') }}"><i class="bi bi-send"></i></button>
            <span class="fc-views" title="{{ __('Views') }}"><i class="bi bi-eye"></i> {{ $post->views_count }}</span>
        </footer>

        @auth
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
        @endauth
    </article>
@endforeach
