{{-- Subscribe, notification bell and favorite actions for channel hero blocks. Expects $organization, $channel, $isSubscribed, $isFavorited, $bellOn. --}}
<style>.ch-bell-valex.is-on { color: #f59e0b; }</style>
@auth
    <form action="{{ $isSubscribed ? route('subscriptions.destroy', $channel->id) : route('subscriptions.store', $channel->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn {{ $isSubscribed ? 'btn-light' : 'btn-primary' }}">
            <i class="fe fe-{{ $isSubscribed ? 'check' : 'plus' }}"></i> {{ $isSubscribed ? __('Subscribed') : __('Subscribe') }}
        </button>
    </form>
    @if ($isSubscribed)
        @include('channels._bell', ['class' => 'btn btn-light ch-bell-valex'])
    @endif
    <form action="{{ route('collections.favorite', $channel->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-light" title="{{ __('Favorite') }}"><i class="fe fe-star {{ $isFavorited ? 'text-warning' : '' }}"></i></button>
    </form>
@else
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#signinModal"><i class="fe fe-plus"></i> {{ __('Subscribe') }}</button>
@endauth
