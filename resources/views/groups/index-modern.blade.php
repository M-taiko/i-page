@extends('layouts.app-modern')

@section('title', __('Groups'))

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-top">
        <div class="page-header-info">
            <h1>{{ __('Groups') }}</h1>
            <p>{{ __('Organize your team into groups for better collaboration') }}</p>
        </div>
        <div class="page-header-actions">
            <x-btn href="{{ route('dashboard.groups.create', session('current_organization_id')) }}" variant="primary" icon="plus-circle">
                {{ __('Create Group') }}
            </x-btn>
        </div>
    </div>
</div>

<!-- Groups Grid -->
@if ($groups->count() > 0)
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-12);">
        @foreach ($groups as $group)
            <x-card-modern elevated>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                    <div>
                        <h3 style="margin: 0 0 var(--space-1); font-size: var(--text-lg);">{{ $group->name }}</h3>
                        @if ($group->branch)
                            <small style="color: var(--text-tertiary);">{{ $group->branch->name }}</small>
                        @endif
                    </div>
                </div>

                @if ($group->description)
                    <p style="margin: 0 0 var(--space-4); font-size: var(--text-sm); color: var(--text-secondary); line-height: var(--line-height-normal);">
                        {{ Str::limit($group->description, 100) }}
                    </p>
                @endif

                <div style="margin-bottom: var(--space-6); padding-bottom: var(--space-6); border-bottom: 1px solid var(--surface-border);">
                    <div style="display: flex; align-items: center; gap: var(--space-2);">
                        <i class="bi bi-people" style="color: var(--primary-600);"></i>
                        <span style="font-size: var(--text-sm); color: var(--text-secondary);">
                            {{ $group->users->count() }} {{ __('members') }}
                        </span>
                    </div>
                </div>

                <div style="display: flex; gap: var(--space-2);">
                    <x-btn href="{{ route('dashboard.groups.show', [session('current_organization_id'), $group], $group) }}" variant="outline" size="sm" class="flex-1">
                        {{ __('View') }}
                    </x-btn>
                    <x-btn href="{{ route('dashboard.groups.edit', [session('current_organization_id'), $group], $group) }}" variant="secondary" size="sm" class="flex-1">
                        {{ __('Edit') }}
                    </x-btn>
                </div>
            </x-card-modern>
        @endforeach
    </div>

    <!-- Pagination -->
    <div style="display: flex; justify-content: center; margin-top: var(--space-12);">
        {{ $groups->links('pagination::bootstrap-5') }}
    </div>
@else
    <x-empty-state-modern
        title="{{ __('No Groups') }}"
        message="{{ __('Create your first group to start organizing your team.') }}"
        icon="diagram-3"
    >
        <x-btn href="{{ route('dashboard.groups.create', session('current_organization_id')) }}" variant="primary" icon="plus-circle" class="mt-4">
            {{ __('Create Group') }}
        </x-btn>
    </x-empty-state-modern>
@endif
@endsection
