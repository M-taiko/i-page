{{-- Channel cards grouped by brand (Valex "cards"). --}}
@forelse ($channelGroups as $group)
    <h5 class="mb-3 mt-4"><span class="avatar avatar-sm brround bg-primary mx-2">{{ mb_substr($group['title'], 0, 1) }}</span>{{ $group['title'] }}</h5>
    <div class="row row-sm">
        @foreach ($group['channels'] as $channel)
            <div class="col-6 col-md-4 col-xl-3">
                <a href="{{ route('guest.channel-detail', [$organization, $channel->slug]) }}" class="org-card-link">
                    <div class="card text-center mb-3">
                        <div class="card-body">
                            <span class="avatar avatar-lg brround bg-primary-transparent text-primary mb-2 d-inline-flex align-items-center justify-content-center"><i class="fe fe-message-circle tx-20"></i></span>
                            <h6 class="mb-1 text-truncate">{{ $channel->name }}</h6>
                            <small class="text-muted">{{ $channel->users_count }} {{ __('members') }} · {{ $channel->posts_count }} {{ __('posts') }}</small>
                            @if ($channel->childChannels->count())
                                <div class="mt-2"><span class="badge badge-light"><i class="fe fe-share-2"></i> {{ $channel->childChannels->count() }}</span></div>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@empty
    <div class="card"><div class="card-body text-center text-muted py-5">{{ __('No public channels available') }}</div></div>
@endforelse
