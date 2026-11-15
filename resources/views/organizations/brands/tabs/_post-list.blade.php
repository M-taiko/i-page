{{-- Posts across a brand: brand-level posts and channel posts, with the manage menu. Expects $posts. --}}
@php
    $audienceLabels = ['all' => __('All channels'), 'public' => __('Public channels'), 'private' => __('Private channels')];
@endphp
<ul class="list-group list-group-flush">
    @forelse ($posts as $post)
        <li class="list-group-item px-0">
            <div class="d-flex align-items-start">
                <span class="avatar avatar-md brround {{ $post->isBrandPost() ? 'bg-warning-transparent text-warning' : 'bg-primary-transparent text-primary' }} mx-2">
                    <i class="fe fe-{{ $post->isBrandPost() ? 'award' : 'message-circle' }}"></i>
                </span>
                <div class="flex-grow-1" style="min-width: 0;">
                    <a href="{{ route('guest.post-detail', $post->id) }}" class="font-weight-bold d-block text-truncate">{{ $post->title ?: Str::limit($post->body, 70) }}</a>
                    <div class="small text-muted">
                        @if ($post->isBrandPost())
                            <x-valex.badge variant="warning">🏷️ {{ __('Brand post') }} · {{ $audienceLabels[$post->brand_audience] ?? $post->brand_audience }}</x-valex.badge>
                        @else
                            <x-valex.badge variant="light">{{ $post->channel?->name }}</x-valex.badge>
                        @endif
                        @if ($post->isPinned())<x-valex.badge variant="primary">📌 {{ __('Pinned') }}</x-valex.badge>@endif
                        @if ($post->status !== 'published')<x-valex.badge variant="secondary">{{ str_replace('_', ' ', $post->status) }}</x-valex.badge>@endif
                        {{ $post->author?->full_name ?? __('System') }} · {{ $post->created_at->diffForHumans() }}
                    </div>
                </div>
                @include('posts.partials._manage')
            </div>
        </li>
    @empty
        <li class="list-group-item text-center text-muted">{{ __('No posts yet') }}</li>
    @endforelse
</ul>
