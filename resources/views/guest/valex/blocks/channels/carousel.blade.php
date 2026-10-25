{{-- Sliding channel cards (Valex "carousel": Bootstrap carousel, slides of up to 3 cards). --}}
@php $allChannels = $channelGroups->flatMap(fn ($g) => $g['channels']); $slides = $allChannels->chunk(3); @endphp
<h5 class="mb-3 mt-4">{{ __('Channels') }}</h5>
@if ($allChannels->isEmpty())
    <div class="card"><div class="card-body text-center text-muted py-5">{{ __('No public channels available') }}</div></div>
@else
    <div id="channelsCarousel" class="carousel slide" data-ride="carousel" data-interval="6000">
        @if ($slides->count() > 1)
            <ol class="carousel-indicators" style="bottom: -34px;">
                @foreach ($slides as $i => $s)<li data-target="#channelsCarousel" data-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}" style="background-color: var(--org-primary);"></li>@endforeach
            </ol>
        @endif
        <div class="carousel-inner pb-2">
            @foreach ($slides as $i => $slide)
                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                    <div class="row row-sm px-1">
                        @foreach ($slide as $channel)
                            <div class="col-12 col-md-4">
                                <a href="{{ route('guest.channel-detail', [$organization, $channel->slug]) }}" class="org-card-link">
                                    <div class="card bg-primary-gradient text-white mb-3">
                                        <div class="card-body text-center py-4">
                                            <i class="fe fe-message-circle tx-30 mb-2 d-block"></i>
                                            <h5 class="text-white mb-1 text-truncate">{{ $channel->name }}</h5>
                                            <small class="tx-white-8">{{ $channel->users_count }} {{ __('members') }} · {{ $channel->posts_count }} {{ __('posts') }}</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        @if ($slides->count() > 1)
            <a class="carousel-control-prev" href="#channelsCarousel" data-slide="prev" style="width: 8%;"><i class="fe fe-chevron-left text-primary tx-24"></i></a>
            <a class="carousel-control-next" href="#channelsCarousel" data-slide="next" style="width: 8%;"><i class="fe fe-chevron-right text-primary tx-24"></i></a>
        @endif
    </div>
    <div class="mb-4"></div>
@endif
