@extends('layouts.app-modern')

@section('title', __('Organization Dashboard'))

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-top">
        <div class="page-header-info">
            <h1>{{ $organization->name }}</h1>
            <p>{{ __('Manage your organization communications and content') }}</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('tenant.channels.create') }}" class="btn btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-2);">
                <i class="bi bi-plus-circle"></i>
                <span>{{ __('Create Channel') }}</span>
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-6);">
    <x-stat-card
        title="{{ __('Channels') }}"
        value="{{ $stats['total_channels'] }}/{{ $stats['max_channels'] }}"
        icon="chat-dots"
    />
    <x-stat-card
        title="{{ __('Posts') }}"
        value="{{ $stats['total_posts'] }}"
        icon="newspaper"
    />
    <x-stat-card
        title="{{ __('Team Members') }}"
        value="{{ $stats['total_users'] }}"
        icon="people"
    />
</div>

<!-- Recent Posts Section -->
@if($stats['recent_posts']->count() > 0)
    <div style="margin-bottom: var(--space-6);">
        <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-4);">
            <i class="bi bi-newspaper" style="font-size: var(--text-2xl); color: var(--primary-600);"></i>
            <h2 style="margin: 0; font-size: var(--text-2xl); font-weight: var(--font-weight-bold); color: var(--text-primary);">
                {{ __('Recent Posts') }}
            </h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--space-4);">
            @foreach($stats['recent_posts'] as $post)
                <x-card-modern>
                    <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-4);">
                        <div style="width: 40px; height: 40px; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--primary-500), var(--secondary-500)); display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); font-size: var(--text-sm);">
                            {{ $post->author->initials }}
                        </div>
                        <div style="flex: 1;">
                            <h4 style="margin: 0; font-size: var(--text-sm); font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                                {{ $post->author->full_name }}
                            </h4>
                            <p style="margin: var(--space-1) 0 0; font-size: var(--text-xs); color: var(--text-tertiary);">
                                {{ $post->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    <p style="margin: 0 0 var(--space-3); font-size: var(--text-sm); color: var(--text-primary); line-height: var(--line-height-normal);">
                        {{ Str::limit($post->body, 120) }}
                    </p>

                    @if($post->channel)
                        <div style="display: flex; align-items: center; gap: var(--space-2); padding: var(--space-2) var(--space-3); background-color: var(--primary-50); border-radius: var(--radius-md); margin-bottom: var(--space-3);">
                            <i class="bi bi-chat-dots" style="color: var(--primary-600);"></i>
                            <span style="font-size: var(--text-xs); font-weight: var(--font-weight-medium); color: var(--primary-700);">
                                {{ $post->channel->name }}
                            </span>
                        </div>
                    @endif

                    <div style="border-top: 1px solid var(--surface-border); padding-top: var(--space-3); display: flex; gap: var(--space-2);">
                        @if($post->audience)
                            <span style="display: inline-flex; align-items: center; gap: var(--space-1); padding: var(--space-1) var(--space-2); background-color: var(--secondary-50); color: var(--secondary-700); border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: var(--font-weight-medium);">
                                <i class="bi bi-eye"></i>
                                {{ ucfirst($post->audience) }}
                            </span>
                        @endif
                    </div>
                </x-card-modern>
            @endforeach
        </div>
    </div>
@else
    <x-empty-state-modern
        title="{{ __('No Posts Yet') }}"
        message="{{ __('Start by creating your first post to engage with your team.') }}"
        icon="newspaper"
    >
        <a href="{{ route('tenant.channels.create') }}" class="btn btn-primary mt-4" style="text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-2);">
            <i class="bi bi-plus-circle"></i>
            <span>{{ __('Create First Channel') }}</span>
        </a>
    </x-empty-state-modern>
@endif

@endsection
