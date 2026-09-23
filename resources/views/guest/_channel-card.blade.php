{{--
    Single channel card for the organization channels grid. If the channel
    has sub-channels, the first tap shows a popup listing them (with a
    tooltip hinting to tap again); the second tap navigates into the
    channel itself. Channels with no sub-channels navigate immediately.
--}}
@php
    $hasChildren = $channel->childChannels->count() > 0;
@endphp
<div class="channel-card-wrap">
    <a href="{{ route('guest.channel-detail', [$organization, $channel->slug]) }}"
       class="channel-card"
       @if($hasChildren)
           data-channel-id="{{ $channel->id }}"
           onclick="return handleChannelCardClick(event, this)"
       @endif
    >
        <div class="channel-icon"><i class="bi bi-chat-dots"></i></div>
        <div class="channel-card-name">{{ $channel->name }}</div>
        <div class="channel-card-meta">{{ $channel->users_count }} {{ __('members') }} · {{ $channel->posts_count }} {{ __('posts') }}</div>
        @if($hasChildren)
            <span class="channel-subbadge"><i class="bi bi-diagram-3"></i>{{ $channel->childChannels->count() }}</span>
        @endif
    </a>

    @if($hasChildren)
        <div class="channel-subpopup" id="subpopup-{{ $channel->id }}">
            <div class="subpopup-tooltip">{{ __('Tap :name again to open it', ['name' => $channel->name]) }}</div>
            <div class="subpopup-list">
                @foreach($channel->childChannels as $sub)
                    <a href="{{ route('guest.channel-detail', [$organization, $sub->slug]) }}" class="subpopup-item">
                        <i class="bi bi-chat-dots"></i> {{ $sub->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
