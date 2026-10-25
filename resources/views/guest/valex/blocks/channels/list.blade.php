{{-- Plain directory list grouped by brand (Valex "list-group"). --}}
<h5 class="mb-3 mt-4">{{ __('Channels') }}</h5>
@forelse ($channelGroups as $group)
    <div class="card mb-3">
        <div class="card-header pb-2"><strong>{{ $group['title'] }}</strong> <span class="badge badge-light badge-pill">{{ $group['channels']->count() }}</span></div>
        <div class="list-group list-group-flush">
            @foreach ($group['channels'] as $channel)
                <a href="{{ route('guest.channel-detail', [$organization, $channel->slug]) }}" class="list-group-item list-group-item-action d-flex align-items-center">
                    <span class="avatar avatar-md brround bg-primary-transparent text-primary mx-2 d-inline-flex align-items-center justify-content-center"><i class="fe fe-message-circle"></i></span>
                    <span class="flex-grow-1"><strong class="d-block">{{ $channel->name }}</strong><small class="text-muted">{{ $channel->users_count }} {{ __('members') }} · {{ $channel->posts_count }} {{ __('posts') }}</small></span>
                    <i class="fe fe-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-muted"></i>
                </a>
            @endforeach
        </div>
    </div>
@empty
    <div class="card"><div class="card-body text-center text-muted py-5">{{ __('No public channels available') }}</div></div>
@endforelse
