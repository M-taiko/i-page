{{-- Tenant posts with where they appear and the manage menu (ADR-016). Expects $posts. --}}
@php
    $placementLabels = ['page' => __('Tenant page only'), 'all' => __('Every channel'), 'selected' => __('Chosen channels')];
@endphp
<ul class="list-group list-group-flush">
    @forelse ($posts as $post)
        <li class="list-group-item px-0">
            <div class="d-flex align-items-start">
                <span class="avatar avatar-md brround bg-primary-transparent text-primary mx-2"><i class="fe fe-send"></i></span>
                <div class="flex-grow-1" style="min-width: 0;">
                    <a href="{{ route('guest.post-detail', $post->id) }}" class="font-weight-bold d-block text-truncate">{{ $post->title ?: Str::limit($post->body, 70) }}</a>
                    <div class="small text-muted mt-1">
                        <x-valex.badge variant="info">{{ $placementLabels[$post->placement] ?? $post->placement }}</x-valex.badge>
                        @if ($post->placement === 'selected')
                            @foreach ($post->targetChannels->take(4) as $target)
                                <x-valex.badge variant="light">{{ $target->name }}</x-valex.badge>
                            @endforeach
                            @if ($post->targetChannels->count() > 4)<x-valex.badge variant="light">+{{ $post->targetChannels->count() - 4 }}</x-valex.badge>@endif
                            @if ($post->cascade)<x-valex.badge variant="light">⤵ {{ __('with sub-channels') }}</x-valex.badge>@endif
                        @endif
                        @if ($post->show_on_page)<x-valex.badge variant="success">🏢 {{ __('On tenant page') }}</x-valex.badge>@endif
                        @if ($post->isPinned())<x-valex.badge variant="primary">📌 {{ __('Pinned') }}</x-valex.badge>@endif
                    </div>
                    <div class="small text-muted mt-1">{{ $post->author?->full_name ?? __('System') }} · {{ $post->created_at->diffForHumans() }}</div>
                </div>
                @include('posts.partials._manage')
            </div>
        </li>
    @empty
        <li class="list-group-item text-center text-muted">{{ __('No posts yet') }}</li>
    @endforelse
</ul>
