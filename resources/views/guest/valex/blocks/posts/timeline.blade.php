{{-- Latest news as a vertical timeline (Valex "timeline"). --}}
<h5 class="mb-3 mt-4">{{ __('Latest updates') }}</h5>
@if ($posts->isEmpty())
    <div class="card"><div class="card-body text-center text-muted py-5">{{ __('No posts yet') }}</div></div>
@else
    <div class="card"><div class="card-body">
        <div class="vtimeline">
            @foreach ($posts as $post)
                <div class="timeline-wrapper timeline-wrapper-primary {{ $loop->odd ? '' : 'timeline-inverted' }}">
                    <div class="timeline-badge"><i class="fe fe-file-text text-white"></i></div>
                    <div class="timeline-panel">
                        <div class="timeline-heading"><h6 class="timeline-title"><a href="{{ route('guest.post-detail', $post->id) }}">{{ $post->title ?: Str::limit($post->body, 50) }} @include('posts.partials._chips')</a></h6></div>
                        <div class="timeline-body"><p>{{ Str::limit($post->summary ?: $post->body, 140) }}</p></div>
                        <div class="timeline-footer d-flex align-items-center flex-wrap">
                            <i class="fe fe-message-circle"></i> <span class="mx-1">{{ $post->channel?->name }}</span>
                            <span class="ml-auto"><i class="fe fe-calendar"></i> {{ ($post->published_at ?? $post->created_at)->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div></div>
    <div class="d-flex justify-content-center">{{ $posts->links('pagination::bootstrap-4') }}</div>
@endif
