@extends('layouts.app-modern')

@section('title', __('News Feed'))

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-top">
        <div class="page-header-info">
            <h1>{{ __('News Feed') }}</h1>
            <p>{{ __('View and manage all posts across your channels') }}</p>
        </div>
        <div class="page-header-actions">
            <x-btn href="{{ route('dashboard.feeds.create', session('current_organization_id')) }}" variant="primary" icon="pencil-square">
                {{ __('Create Post') }}
            </x-btn>
        </div>
    </div>
</div>

<!-- Feed -->
@if ($posts->count() > 0)
    <div style="display: grid; gap: var(--space-8); max-width: 800px; margin: 0 auto;">
        @foreach ($posts as $post)
            <x-card-modern elevated>
                <!-- Post Header -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                    <div style="display: flex; gap: var(--space-3);">
                        <div style="width: 48px; height: 48px; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--primary-100), var(--secondary-100)); display: flex; align-items: center; justify-content: center; font-weight: var(--font-weight-bold); color: var(--primary-700); font-size: var(--text-base); flex-shrink: 0;">
                            {{ $post->author->initials }}
                        </div>
                        <div>
                            <h4 style="margin: 0 0 var(--space-1); font-size: var(--text-base);">{{ $post->author->full_name }}</h4>
                            <small style="color: var(--text-tertiary);">{{ $post->published_at?->diffForHumans() }}</small>
                        </div>
                    </div>
                    @can('delete', $post)
                        <form action="{{ route('dashboard.feeds.destroy', [session('current_organization_id'), $post], $post) }}" method="POST" style="display: contents;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-icon btn-sm" style="color: var(--danger-600);" onclick="return confirm('{{ __('Are you sure?') }}')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    @endcan
                </div>

                <!-- Post Content -->
                <div style="margin-bottom: var(--space-4);">
                    <p style="margin: 0 0 var(--space-3); color: var(--text-primary); line-height: var(--line-height-relaxed);">
                        {{ $post->body }}
                    </p>

                    @if ($post->image_path)
                        <img src="{{ Storage::url($post->image_path) }}" alt="Post image"
                            style="width: 100%; height: auto; border-radius: var(--radius-lg); max-height: 400px; object-fit: cover;">
                    @endif
                </div>

                <!-- Channel Badge -->
                @if ($post->channel)
                    <div style="margin-bottom: var(--space-4);">
                        <span style="display: inline-flex; align-items: center; gap: var(--space-1); padding: var(--space-1) var(--space-3); background-color: var(--info-50); color: var(--info-700); border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: var(--font-weight-medium);">
                            <i class="bi bi-chat-dots"></i>
                            {{ $post->channel->name }}
                        </span>
                    </div>
                @endif

                <!-- Post Actions -->
                <div style="border-top: 1px solid var(--surface-border); padding-top: var(--space-4); display: flex; gap: var(--space-4);">
                    <button style="background: none; border: none; color: var(--text-secondary); font-size: var(--text-sm); cursor: pointer; display: flex; align-items: center; gap: var(--space-2); transition: color var(--transition-fast);" onmouseover="this.style.color='var(--success-600)'" onmouseout="this.style.color='var(--text-secondary)'">
                        <i class="bi bi-hand-thumbs-up"></i>
                        <span>{{ __('Like') }}</span>
                    </button>
                    <button style="background: none; border: none; color: var(--text-secondary); font-size: var(--text-sm); cursor: pointer; display: flex; align-items: center; gap: var(--space-2); transition: color var(--transition-fast);" onmouseover="this.style.color='var(--primary-600)'" onmouseout="this.style.color='var(--text-secondary)'">
                        <i class="bi bi-chat"></i>
                        <span>{{ __('Comment') }}</span>
                    </button>
                    <button style="background: none; border: none; color: var(--text-secondary); font-size: var(--text-sm); cursor: pointer; display: flex; align-items: center; gap: var(--space-2); transition: color var(--transition-fast);" onmouseover="this.style.color='var(--info-600)'" onmouseout="this.style.color='var(--text-secondary)'">
                        <i class="bi bi-share"></i>
                        <span>{{ __('Share') }}</span>
                    </button>
                </div>
            </x-card-modern>
        @endforeach
    </div>

    <!-- Pagination -->
    <div style="display: flex; justify-content: center; margin-top: var(--space-12);">
        {{ $posts->links('pagination::bootstrap-5') }}
    </div>
@else
    <x-empty-state-modern
        title="{{ __('No Posts') }}"
        message="{{ __('Start sharing updates with your team to keep everyone informed.') }}"
        icon="newspaper"
    >
        <x-btn href="{{ route('dashboard.feeds.create', session('current_organization_id')) }}" variant="primary" icon="pencil-square" class="mt-4">
            {{ __('Create Post') }}
        </x-btn>
    </x-empty-state-modern>
@endif
@endsection
