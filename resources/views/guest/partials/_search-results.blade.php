{{-- Search results: matching channels (when searching) and organizations. Expects $organizations, $channels, $search, $country. --}}
@php
    $palette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777', '#0891b2'];
    $colorFor = fn ($seed) => $palette[crc32((string) $seed) % count($palette)];
    $mark = function (string $text) use ($search) {
        $safe = e($text);
        return $search === '' ? $safe : preg_replace('/(' . preg_quote(e($search), '/') . ')/iu', '<mark>$1</mark>', $safe);
    };
@endphp

@if ($channels->isNotEmpty())
    <section class="sr-sec">
        <h2 class="sr-title"><i class="bi bi-broadcast"></i> {{ __('Channels') }} <span>{{ $channels->count() }}</span></h2>
        <div class="sr-channels">
            @foreach ($channels as $channel)
                <a href="{{ route('guest.channel-detail', [$channel->organization_id, $channel->slug]) }}" class="sr-ch reveal" data-tip="{{ $channel->name }}" data-tip-sub="{{ $channel->organization?->name }}">
                    <span class="sr-ch-av" style="--av: {{ $colorFor($channel->name) }};">{{ mb_substr($channel->name, 0, 1) }}</span>
                    <span class="sr-ch-text">
                        <span class="sr-ch-name">{!! $mark($channel->name) !!}</span>
                        <span class="sr-ch-sub">{{ $channel->organization?->name }} · {{ trans_choice(':count member|:count members', $channel->users_count) }}</span>
                    </span>
                    <i class="bi bi-chevron-right rtl-flip"></i>
                </a>
            @endforeach
        </div>
    </section>
@endif

<section class="sr-sec">
    <h2 class="sr-title"><i class="bi bi-building"></i> {{ __('Organizations') }} <span>{{ $organizations->total() }}</span></h2>

    @if ($organizations->isEmpty())
        <div class="sr-empty reveal">
            <x-illustration name="search" size="170" />
            <h3>{{ $search !== '' ? __('No results for ":q"', ['q' => $search]) : __('No organizations yet') }}</h3>
            <p>{{ $search !== '' ? __('Try a shorter word, a city or a country — or scan the QR code you see at the place.') : __('Organizations will appear here as soon as they join.') }}</p>
            @if ($search !== '' || $country !== '')
                <a href="{{ route('guest.search-organizations') }}" class="sr-reset"><i class="bi bi-x-circle"></i> {{ __('Clear search') }}</a>
            @endif
        </div>
    @else
        <div class="sr-grid">
            @foreach ($organizations as $org)
                <a href="{{ route('guest.organization-detail', $org->id) }}" class="sr-org reveal">
                    <span class="sr-org-cover" style="--av: {{ $colorFor($org->name) }};">
                        <span class="sr-org-av">{{ mb_substr($org->name, 0, 1) }}</span>
                    </span>
                    <span class="sr-org-body">
                        <span class="sr-org-name">{!! $mark($org->name) !!}</span>
                        @if ($org->city || $org->country)
                            <span class="sr-org-loc"><i class="bi bi-geo-alt"></i> {{ collect([$org->city, $org->country])->filter()->implode(', ') }}</span>
                        @endif
                        @if ($org->description)
                            <span class="sr-org-desc">{!! $mark(\Illuminate\Support\Str::limit($org->description, 110)) !!}</span>
                        @endif
                        <span class="sr-org-stats">
                            <span><i class="bi bi-broadcast"></i> {{ trans_choice(':count channel|:count channels', $org->channels_count) }}</span>
                            <span><i class="bi bi-heart"></i> {{ trans_choice(':count follower|:count followers', $org->followers_count) }}</span>
                        </span>
                    </span>
                </a>
            @endforeach
        </div>

        @if ($organizations->hasPages())
            <nav class="sr-pages" aria-label="{{ __('Pages') }}">
                @if ($organizations->onFirstPage())
                    <span class="sr-page is-off"><i class="bi bi-chevron-left rtl-flip"></i></span>
                @else
                    <a class="sr-page" href="{{ $organizations->previousPageUrl() }}" aria-label="{{ __('Previous') }}"><i class="bi bi-chevron-left rtl-flip"></i></a>
                @endif
                <span class="sr-page-info">{{ __('Page :p of :n', ['p' => $organizations->currentPage(), 'n' => $organizations->lastPage()]) }}</span>
                @if ($organizations->hasMorePages())
                    <a class="sr-page" href="{{ $organizations->nextPageUrl() }}" aria-label="{{ __('Next') }}"><i class="bi bi-chevron-right rtl-flip"></i></a>
                @else
                    <span class="sr-page is-off"><i class="bi bi-chevron-right rtl-flip"></i></span>
                @endif
            </nav>
        @endif
    @endif
</section>
