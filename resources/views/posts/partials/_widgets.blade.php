{{-- Interactions for a post: reactions + format widget (poll / event / rating). Expects $post. Optional: $flash (show messages). ADR-013 --}}
@php
    $user = auth()->user();
    $format = $post->post_format ?: 'standard';
    $badge = $post->badge();
    $accentStyle = $badge ? "--pf-accent: {$badge['color']}" : '';
    $reactionConfig = config('posts.reactions');
    $post->loadMissing('reactions');
    $reactionCounts = $post->reactions->groupBy('type')->map->count();
    $myReaction = $user ? $post->reactions->firstWhere('user_id', $user->id)?->type : null;
@endphp
<div class="pf-widgets" style="{{ $accentStyle }}">
    @if (($flash ?? false) || session('flash_post') == $post->id)
        @if (session('error'))<div class="pf-alert">{{ session('error') }}</div>@endif
        @if (session('success'))<div class="pf-ok">{{ session('success') }}</div>@endif
    @endif

    {{-- ===================== Poll ===================== --}}
    @if ($format === 'poll')
        @php
            $post->loadMissing('pollOptions.votes');
            $options = $post->pollOptions;
            $totalVoters = $post->pollVotes()->distinct('user_id')->count('user_id');
            $totalVotes = $options->sum(fn ($o) => $o->votes->count());
            $myVotes = $user ? $post->pollVotes()->where('user_id', $user->id)->pluck('option_id')->all() : [];
            $closed = $post->pollClosed();
            $showResults = $closed || $myVotes || $post->option('poll.show_results', 'always') === 'always';
            $multiple = (bool) $post->option('poll.multiple', false);
            $endsAt = $post->option('poll.ends_at');
        @endphp
        <div class="pf-box">
            <h6><i class="bi bi-bar-chart"></i> {{ __('Poll') }}
                <span class="pf-muted">· {{ $totalVoters }} {{ __('voters') }}@if ($endsAt) · {{ $closed ? __('Ended') : __('Ends') . ' ' . \Illuminate\Support\Carbon::parse($endsAt)->diffForHumans() }}@endif</span></h6>

            @if ($user && ! $myVotes && ! $closed)
                <form action="{{ route('posts.vote', $post->id) }}" method="POST">
                    @csrf
                    @foreach ($options as $opt)
                        <label class="pf-opt">
                            <input type="{{ $multiple ? 'checkbox' : 'radio' }}" name="options[]" value="{{ $opt->id }}" {{ $multiple ? '' : 'required' }}>
                            <span>{{ $opt->label }}</span>
                        </label>
                    @endforeach
                    @if ($multiple)<div class="pf-muted mb-2">{{ __('You can choose more than one option.') }}</div>@endif
                    <button type="submit" class="pf-btn">{{ __('Vote') }}</button>
                </form>
            @else
                @foreach ($options as $opt)
                    @php $count = $opt->votes->count(); $pct = $totalVotes ? round($count / $totalVotes * 100) : 0; @endphp
                    @if ($showResults)
                        <div class="pf-res {{ in_array($opt->id, $myVotes, true) ? 'mine' : '' }}">
                            <div class="pf-bar" style="width: {{ $pct }}%"></div>
                            <span>{{ $opt->label }}@if (in_array($opt->id, $myVotes, true)) ✓@endif</span>
                            <span>{{ $pct }}% <small class="pf-muted">({{ $count }})</small></span>
                        </div>
                    @else
                        <div class="pf-res"><span>{{ $opt->label }}</span></div>
                    @endif
                @endforeach
                @guest
                    @if (! $closed)<a class="pf-btn" href="{{ route('login') }}">{{ __('Sign in to vote') }}</a>@endif
                @endguest
                @if (! $showResults)<div class="pf-muted">{{ __('Results appear after you vote.') }}</div>@endif
            @endif
        </div>
    @endif

    {{-- ===================== Event ===================== --}}
    @if ($format === 'event')
        @php
            $post->loadMissing('rsvps');
            $starts = $post->option('event.starts_at') ? \Illuminate\Support\Carbon::parse($post->option('event.starts_at')) : null;
            $ends = $post->option('event.ends_at') ? \Illuminate\Support\Carbon::parse($post->option('event.ends_at')) : null;
            $location = $post->option('event.location');
            $capacity = $post->option('event.capacity');
            $counts = $post->rsvps->groupBy('status')->map->count();
            $going = (int) ($counts['going'] ?? 0);
            $myRsvp = $user ? $post->rsvps->firstWhere('user_id', $user->id)?->status : null;
            $over = $ends ? $ends->isPast() : ($starts ? $starts->isPast() : false);
        @endphp
        <div class="pf-box">
            <h6><i class="bi bi-calendar-event"></i> {{ __('Event') }}</h6>
            <div style="font-size: 14px; line-height: 1.8;">
                @if ($starts)<div><i class="bi bi-clock"></i> {{ $starts->translatedFormat('D, M d · H:i') }}@if ($ends) → {{ $ends->isSameDay($starts) ? $ends->format('H:i') : $ends->translatedFormat('M d · H:i') }}@endif</div>@endif
                @if ($location)<div><i class="bi bi-geo-alt"></i> {{ $location }}</div>@endif
                <div><i class="bi bi-people"></i> {{ $going }} {{ __('going') }}@if ($capacity) / {{ $capacity }}@endif · {{ (int) ($counts['maybe'] ?? 0) }} {{ __('maybe') }}</div>
            </div>
            <div class="pf-row" style="margin-top: 10px;">
                @if ($over)
                    <span class="pf-muted">{{ __('This event has ended.') }}</span>
                @elseif ($user)
                    @foreach (config('posts.rsvp') as $key => $label)
                        <form action="{{ route('posts.rsvp', $post->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="{{ $key }}">
                            <button type="submit" class="pf-btn {{ $myRsvp === $key ? 'on' : 'pf-outline' }}">{{ __($label) }}</button>
                        </form>
                    @endforeach
                @else
                    <a class="pf-btn" href="{{ route('login') }}">{{ __('Sign in to RSVP') }}</a>
                @endif
            </div>
        </div>
    @endif

    {{-- ===================== Rating ===================== --}}
    @if ($format === 'rating')
        @php
            $post->loadMissing('ratings');
            $ratingCount = $post->ratings->count();
            $average = $ratingCount ? round($post->ratings->avg('rating'), 1) : 0;
            $myRating = $user ? (int) $post->ratings->firstWhere('user_id', $user->id)?->rating : 0;
        @endphp
        <div class="pf-box">
            <h6><i class="bi bi-star"></i> {{ __('Rating') }}</h6>
            <div style="display:flex; align-items:center; gap:10px; margin-bottom: 10px;">
                <strong style="font-size: 24px;">{{ $ratingCount ? number_format($average, 1) : '—' }}</strong>
                <span class="pf-starline">@for ($i = 1; $i <= 5; $i++){{ $i <= round($average) ? '★' : '☆' }}@endfor</span>
                <span class="pf-muted">({{ $ratingCount }})</span>
            </div>
            @if ($user)
                <form action="{{ route('posts.rate', $post->id) }}" method="POST">
                    @csrf
                    <div class="pf-muted">{{ $myRating ? __('Your rating — tap to change') : __('Tap a star to rate') }}</div>
                    <div class="pf-stars">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="submit" name="rating" value="{{ $i }}" class="{{ $i <= $myRating ? 'on' : '' }}" aria-label="{{ $i }}">★</button>
                        @endfor
                    </div>
                </form>
            @else
                <a class="pf-btn" href="{{ route('login') }}">{{ __('Sign in to rate') }}</a>
            @endif
        </div>
    @endif

    {{-- ===================== Reactions (all formats) ===================== --}}
    <div class="pf-react">
        @foreach ($post->enabledReactions() as $key)
            @php [$label, $emoji] = $reactionConfig[$key]; $count = (int) ($reactionCounts[$key] ?? 0); @endphp
            @if ($user)
                <form action="{{ route('posts.react', $post->id) }}" method="POST" style="display:inline">
                    @csrf
                    <input type="hidden" name="type" value="{{ $key }}">
                    <button type="submit" class="{{ $myReaction === $key ? 'on' : '' }}" title="{{ __($label) }}">{{ $emoji }} <span>{{ $count ?: '' }}</span></button>
                </form>
            @else
                <a href="{{ route('login') }}" title="{{ __($label) }}">{{ $emoji }} <span>{{ $count ?: '' }}</span></a>
            @endif
        @endforeach
    </div>
</div>
