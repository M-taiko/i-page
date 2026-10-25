{{-- Channel cover header with caption overlay (Valex images figure/figcaption). --}}
<figure class="org-hero-fig position-relative mb-3 text-white"
        style="min-height: {{ min($options['cover']['height_px'], 240) }}px; background: linear-gradient(135deg, var(--org-primary), var(--org-secondary));">
    @include('guest.valex.blocks._cover')
    <figcaption class="position-absolute w-100 p-4 d-flex align-items-end flex-wrap justify-content-between"
                style="bottom: 0; top: 0; z-index: 2; background: linear-gradient(180deg, rgba(0,0,0,.05), rgba(0,0,0,.65));">
        <div>
            <div class="small tx-white-8 mb-1">
                <a href="{{ route('guest.organization-detail', $organization) }}" style="color: #fff !important;">{{ $organization->name }}</a>
                @if ($parentChannel) · <a href="{{ route('guest.channel-detail', [$organization, $parentChannel->slug]) }}" style="color: #fff !important;">{{ $parentChannel->name }}</a>@endif
            </div>
            <h3 class="text-white mb-1"><i class="fe fe-message-circle"></i> {{ $channel->name }}</h3>
            <small>{{ $channel->users_count }} {{ __('members') }}@if ($channel->brand) · {{ $channel->brand->name }}@endif</small>
        </div>
        <div class="mt-3 mt-md-0">@include('guest.valex.blocks._channel-actions')</div>
    </figcaption>
</figure>
