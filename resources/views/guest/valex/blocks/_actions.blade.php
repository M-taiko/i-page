{{-- Follow / sign-in action shared by hero blocks. Expects $organization, $isFollowing. --}}
@auth
    <form action="{{ $isFollowing ? route('organizations.unfollow', $organization->id) : route('organizations.follow', $organization->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn {{ $isFollowing ? 'btn-light' : 'btn-primary' }}">
            <i class="fe fe-{{ $isFollowing ? 'check' : 'plus' }}"></i> {{ $isFollowing ? __('Following') : __('Follow') }}
        </button>
    </form>
@else
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#signinModal"><i class="fe fe-plus"></i> {{ __('Follow') }}</button>
@endauth
