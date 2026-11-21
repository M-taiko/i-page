{{-- Infinite-scroll post list. Expects $posts (simple paginator), $endTitle, $endText; optional $endUrl, $endLabel, $endIcon. --}}
    <div class="feed" id="feed" data-next="{{ $posts->nextPageUrl() }}">
        @include('guest.partials._feed-cards', ['posts' => $posts])
    </div>

    <div id="feedLoading" class="feed" hidden aria-hidden="true">
        @for ($i = 0; $i < 2; $i++)
            <div class="sk">
                <div class="sk-row"><div class="sk-b" style="width:44px;height:44px;border-radius:14px"></div><div style="flex:1"><div class="sk-b" style="height:12px;width:45%;margin-bottom:8px"></div><div class="sk-b" style="height:10px;width:28%"></div></div></div>
                <div class="sk-b" style="height:12px;margin-bottom:8px"></div><div class="sk-b" style="height:12px;width:80%;margin-bottom:8px"></div><div class="sk-b" style="height:12px;width:60%"></div>
            </div>
        @endfor
    </div>

    <div id="feedError" class="feed-end" hidden>
        <p>{{ __('Couldn\'t load more posts.') }}</p>
        <button type="button" class="pill-btn ghost" onclick="feedLoad()"><i class="bi bi-arrow-clockwise"></i> {{ __('Try again') }}</button>
    </div>

    <div id="feedEnd" class="feed-end" @if ($posts->hasMorePages()) hidden @endif>
        <x-illustration name="caught-up" size="140" />
        <h3>{{ $endTitle }}</h3>
        <p>{{ $endText }}</p>
        @if (! empty($endUrl))<a href="{{ $endUrl }}" class="pill-btn ghost"><i class="bi bi-{{ $endIcon ?? 'shuffle' }}"></i> {{ $endLabel }}</a>@endif
    </div>

    <div id="feedSentinel" aria-hidden="true" style="height: 1px;"></div>
    @if ($posts->hasMorePages())
        <div class="feed-more feed-more-link"><a href="{{ $posts->nextPageUrl() }}" class="pill-btn ghost">{{ __('Load more') }}</a></div>
    @endif
