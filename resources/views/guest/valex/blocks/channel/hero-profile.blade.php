{{-- Channel profile card (Valex profile + avatar). --}}
<div class="card">
    <div class="card-body text-center">
        <div class="small text-muted mb-2">
            <a href="{{ route('guest.organization-detail', $organization) }}">{{ $organization->name }}</a>
            @if ($parentChannel) <i class="fe fe-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> <a href="{{ route('guest.channel-detail', [$organization, $parentChannel->slug]) }}">{{ $parentChannel->name }}</a>@endif
        </div>
        <span class="avatar avatar-xxl brround bg-primary d-inline-flex align-items-center justify-content-center mb-2"><i class="fe fe-message-circle tx-30"></i></span>
        <h4 class="mb-1">{{ $channel->name }}</h4>
        <p class="text-muted mb-3">{{ $channel->users_count }} {{ __('members') }}@if ($channel->brand) · {{ $channel->brand->name }}@endif</p>
        @include('guest.valex.blocks._channel-actions')
    </div>
</div>
