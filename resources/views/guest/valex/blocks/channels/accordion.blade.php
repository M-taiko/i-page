{{-- One collapsible panel per brand (Valex "accordion", Bootstrap 4 collapse). --}}
<h5 class="mb-3 mt-4">{{ __('Channels') }}</h5>
@if ($channelGroups->isEmpty())
    <div class="card"><div class="card-body text-center text-muted py-5">{{ __('No public channels available') }}</div></div>
@else
    <div class="accordion" id="channelsAccordion" role="tablist" aria-multiselectable="true">
        @foreach ($channelGroups as $i => $group)
            <div class="card mb-2">
                <div class="card-header" id="chHead{{ $i }}" role="tab">
                    <a data-toggle="collapse" href="#chBody{{ $i }}" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" class="d-flex justify-content-between align-items-center" style="color: inherit;">
                        <span><span class="avatar avatar-sm brround bg-primary mx-2">{{ mb_substr($group['title'], 0, 1) }}</span><strong>{{ $group['title'] }}</strong></span>
                        <span class="badge badge-primary badge-pill">{{ $group['channels']->count() }}</span>
                    </a>
                </div>
                <div class="collapse {{ $i === 0 ? 'show' : '' }}" id="chBody{{ $i }}" data-parent="#channelsAccordion" role="tabpanel" aria-labelledby="chHead{{ $i }}">
                    <ul class="list-group list-group-flush">
                        @foreach ($group['channels'] as $channel)
                            <a href="{{ route('guest.channel-detail', [$organization, $channel->slug]) }}" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="fe fe-message-circle mx-2 text-primary"></i>
                                <span class="flex-grow-1">{{ $channel->name }}</span>
                                <small class="text-muted">{{ $channel->users_count }} {{ __('members') }}</small>
                            </a>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
@endif
