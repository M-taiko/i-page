{{-- Follow / sign-in action shared by hero blocks. Expects $organization, $isFollowing. --}}
@auth
    <form action="{{ $isFollowing ? route('organizations.unfollow', $organization->id) : route('organizations.follow', $organization->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn {{ $isFollowing ? 'btn-light' : 'btn-primary' }}" @if ($isFollowing) data-open-unfollow="{{ $organization->id }}" @endif>
            <i class="fe fe-{{ $isFollowing ? 'check' : 'plus' }}"></i> {{ $isFollowing ? __('Following') : __('Follow') }}
        </button>
    </form>
    @if ($isFollowing)
        @include('organizations._unfollow-dialog')
    @endif
@else
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#signinModal"><i class="fe fe-plus"></i> {{ __('Follow') }}</button>
@endauth
