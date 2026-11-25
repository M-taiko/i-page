{{--
    Single channel card for the organization channels grid. Tapping a card
    always opens the channel's posts; its sub-channels (if any) are shown in
    a strip at the top of the channel page.
--}}
@php
    $hasChildren = $channel->childChannels->count() > 0;
@endphp
<div class="channel-card-wrap">
    <a href="{{ route('guest.channel-detail', [$organization, $channel->slug]) }}" class="channel-card">
        <div class="channel-icon">@if($channel->logo_path)<img src="{{ Storage::url($channel->logo_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:inherit">@else<i class="bi bi-chat-dots"></i>@endif</div>
        <div class="channel-card-name">{{ $channel->name }}</div>
        <div class="channel-card-meta">{{ $channel->users_count }} {{ __('members') }} · {{ $channel->posts_count }} {{ __('posts') }}</div>
        @if($hasChildren)
            <span class="channel-subbadge"><i class="bi bi-diagram-3"></i>{{ $channel->childChannels->count() }}</span>
        @endif
    </a>
</div>
