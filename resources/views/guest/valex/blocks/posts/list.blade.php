{{-- Compact news list (Valex "list-group"). --}}
<h5 class="mb-3 mt-4">{{ __('Latest updates') }}</h5>
@if ($posts->isEmpty())
    <div class="card"><div class="card-body text-center text-muted py-5">{{ __('No posts yet') }}</div></div>
@else
    <div class="card">
        <div class="list-group list-group-flush">
            @foreach ($posts as $post)
                <a href="{{ route('guest.post-detail', $post->id) }}" class="list-group-item list-group-item-action d-flex align-items-center">
                    @if ($cover = $post->coverUrl())
                        <img src="{{ $cover }}" class="avatar avatar-lg radius mx-2" style="object-fit: cover;" alt="">
                    @else
                        <span class="avatar avatar-lg radius bg-primary-transparent text-primary mx-2 d-inline-flex align-items-center justify-content-center"><i class="fe fe-file-text"></i></span>
                    @endif
                    <span class="flex-grow-1">
                        <strong class="d-block">{{ $post->title ?: Str::limit($post->body, 50) }} @include('posts.partials._chips')</strong>
                        <small class="text-muted">{{ $post->channel?->name }} · {{ ($post->published_at ?? $post->created_at)->diffForHumans() }}</small>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
    <div class="d-flex justify-content-center mt-3">{{ $posts->links('pagination::bootstrap-4') }}</div>
@endif
