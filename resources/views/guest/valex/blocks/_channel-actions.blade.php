{{-- Subscribe + favorite actions for channel hero blocks. Expects $organization, $channel, $isSubscribed, $isFavorited. --}}
@auth
    <form action="{{ $isSubscribed ? route('dashboard.channels.unsubscribe', [$organization->id, $channel->id]) : route('dashboard.channels.subscribe', [$organization->id, $channel->id]) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn {{ $isSubscribed ? 'btn-light' : 'btn-primary' }}">
            <i class="fe fe-{{ $isSubscribed ? 'check' : 'plus' }}"></i> {{ $isSubscribed ? __('Subscribed') : __('Subscribe') }}
        </button>
    </form>
    <form action="{{ route('collections.favorite', $channel->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-light" title="{{ __('Favorite') }}"><i class="fe fe-star {{ $isFavorited ? 'text-warning' : '' }}"></i></button>
    </form>
@else
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#signinModal"><i class="fe fe-plus"></i> {{ __('Subscribe') }}</button>
@endauth
