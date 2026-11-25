{{-- Channel profile card (Valex profile + avatar). --}}
<div class="card">
    <div class="card-body text-center">
        <div class="small text-muted mb-2">
            <a href="{{ route('guest.organization-detail', $organization) }}">{{ $organization->name }}</a>
            @if ($parentChannel) <i class="fe fe-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> <a href="{{ route('guest.channel-detail', [$organization, $parentChannel->slug]) }}">{{ $parentChannel->name }}</a>@endif
        </div>
        <span class="avatar avatar-xxl brround bg-primary d-inline-flex align-items-center justify-content-center mb-2">@if($channel->logo_path)<img src="{{ Storage::url($channel->logo_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:inherit">@else<i class="fe fe-message-circle tx-30"></i>@endif</span>
        <h4 class="mb-1">{{ $channel->name }}</h4>
        <p class="text-muted mb-3">{{ $channel->users_count }} {{ __('members') }} · {{ $channel->organization?->name }}</p>
        @include('guest.valex.blocks._channel-actions')
    </div>
</div>
