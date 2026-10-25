{{-- News as blog cards (Valex "blog"). --}}
<h5 class="mb-3 mt-4">{{ __('Latest updates') }}</h5>
@if ($posts->isEmpty())
    <div class="card"><div class="card-body text-center text-muted py-5">{{ __('No posts yet') }}</div></div>
@else
    <div class="row row-sm">
        @foreach ($posts as $post)
            <div class="col-md-6 col-xl-4">
                <div class="card custom-card">
                    @if ($cover = $post->coverUrl())
                        <img class="card-img-top w-100" style="height: 170px; object-fit: cover;" src="{{ $cover }}" alt="">
                    @else
                        <div class="bg-primary-gradient d-flex align-items-center justify-content-center text-white" style="height: 110px;"><i class="fe fe-file-text tx-30"></i></div>
                    @endif
                    <div class="card-body">
                        <div class="small text-muted mb-1">{{ $post->channel?->name }} · {{ ($post->published_at ?? $post->created_at)->format('M d, Y') }}</div>
                        <h5 class="card-title">{{ $post->title ?: Str::limit($post->body, 50) }} @include('posts.partials._chips')</h5>
                        <p class="card-text text-muted">{{ Str::limit($post->summary ?: $post->body, 110) }}</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="small"><span class="avatar avatar-xs brround bg-primary mx-1">{{ mb_substr($post->author?->first_name ?? '?', 0, 1) }}</span>{{ $post->author?->full_name }}</span>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('guest.post-detail', $post->id) }}">{{ __('Read more') }} <i class="fe fe-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="d-flex justify-content-center">{{ $posts->links('pagination::bootstrap-4') }}</div>
@endif
