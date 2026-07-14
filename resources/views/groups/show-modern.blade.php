@extends('layouts.app-modern')

@section('title', $group->name)

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-top">
        <div class="page-header-info">
            <h1>{{ $group->name }}</h1>
            @if ($group->branch)
                <p>{{ $group->branch->name }}</p>
            @endif
        </div>
        <div class="page-header-actions">
            <x-btn href="{{ route('dashboard.groups.edit', [session('current_organization_id'), $group], $group) }}" variant="primary" icon="pencil">
                {{ __('Edit') }}
            </x-btn>
            <x-btn href="{{ route('dashboard.groups.index', session('current_organization_id')) }}" variant="outline" icon="arrow-left">
                {{ __('Back') }}
            </x-btn>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-8);">
    <!-- Main Content -->
    <div>
        <x-card-modern title="{{ __('Group Details') }}" icon="diagram-3" elevated>
            <div style="display: grid; gap: var(--space-6);">
                <div>
                    <h4 style="margin: 0 0 var(--space-2); color: var(--text-secondary); font-size: var(--text-sm); font-weight: var(--font-weight-medium);">{{ __('Name') }}</h4>
                    <p style="margin: 0; font-size: var(--text-lg); font-weight: var(--font-weight-semibold);">{{ $group->name }}</p>
                </div>

                @if ($group->description)
                    <div>
                        <h4 style="margin: 0 0 var(--space-2); color: var(--text-secondary); font-size: var(--text-sm); font-weight: var(--font-weight-medium);">{{ __('Description') }}</h4>
                        <p style="margin: 0; color: var(--text-primary); line-height: var(--line-height-relaxed);">{{ $group->description }}</p>
                    </div>
                @endif

                <div>
                    <h4 style="margin: 0 0 var(--space-2); color: var(--text-secondary); font-size: var(--text-sm); font-weight: var(--font-weight-medium);">{{ __('Branch') }}</h4>
                    <p style="margin: 0;">
                        @if ($group->branch)
                            <span style="display: inline-flex; align-items: center; padding: var(--space-1) var(--space-3); background-color: var(--info-50); color: var(--info-700); border-radius: var(--radius-full); font-size: var(--text-sm); font-weight: var(--font-weight-medium);">
                                {{ $group->branch->name }}
                            </span>
                        @else
                            <span style="color: var(--text-tertiary);">{{ __('—') }}</span>
                        @endif
                    </p>
                </div>

                <div>
                    <h4 style="margin: 0 0 var(--space-2); color: var(--text-secondary); font-size: var(--text-sm); font-weight: var(--font-weight-medium);">{{ __('Created') }}</h4>
                    <p style="margin: 0; color: var(--text-primary);">{{ $group->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </x-card-modern>

        <!-- Members Section -->
        <x-card-modern title="{{ __('Members') }} ({{ $group->users->count() }})" icon="people" elevated style="margin-top: var(--space-8);">
            @if ($group->users->count() > 0)
                <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                    @foreach ($group->users as $user)
                        <div style="padding-bottom: var(--space-4); border-bottom: 1px solid var(--surface-border); display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: var(--space-3);">
                                <div style="width: 40px; height: 40px; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--primary-100), var(--secondary-100)); display: flex; align-items: center; justify-content: center; font-weight: var(--font-weight-bold); color: var(--primary-700); font-size: var(--text-sm);">
                                    {{ $user->initials }}
                                </div>
                                <div>
                                    <strong style="display: block; font-size: var(--text-sm);">{{ $user->full_name }}</strong>
                                    <small style="color: var(--text-tertiary);">{{ $user->email }}</small>
                                </div>
                            </div>
                            <span style="display: inline-flex; align-items: center; padding: var(--space-1) var(--space-3); background-color: var(--neutral-200); color: var(--neutral-700); border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: var(--font-weight-medium);">
                                {{ $user->roles()->first()?->name ?? __('No Role') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state-modern
                    title="{{ __('No Members') }}"
                    message="{{ __('This group has no members yet.') }}"
                    icon="people"
                />
            @endif
        </x-card-modern>
    </div>

    <!-- Sidebar -->
    <div>
        <x-card-modern title="{{ __('Actions') }}" elevated>
            <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                <x-btn href="{{ route('dashboard.groups.edit', [session('current_organization_id'), $group], $group) }}" variant="primary" class="w-100">
                    {{ __('Edit Group') }}
                </x-btn>

                <form action="{{ route('dashboard.groups.destroy', [session('current_organization_id'), $group], $group) }}" method="POST" style="display: contents;">
                    @csrf
                    @method('DELETE')
                    <x-btn type="submit" variant="danger" class="w-100" onclick="return confirm('{{ __('Are you sure?') }}')">
                        {{ __('Delete Group') }}
                    </x-btn>
                </form>
            </div>
        </x-card-modern>

        <x-card-modern title="{{ __('Info') }}" style="margin-top: var(--space-6);">
            <div style="display: flex; flex-direction: column; gap: var(--space-4); font-size: var(--text-sm);">
                <div>
                    <small style="display: block; color: var(--text-tertiary); font-weight: var(--font-weight-medium); margin-bottom: var(--space-1);">{{ __('Members') }}</small>
                    <strong style="font-size: var(--text-2xl);">{{ $group->users->count() }}</strong>
                </div>

                <div style="padding-top: var(--space-4); border-top: 1px solid var(--surface-border);">
                    <small style="display: block; color: var(--text-tertiary); font-weight: var(--font-weight-medium); margin-bottom: var(--space-1);">{{ __('Created') }}</small>
                    <small>{{ $group->created_at->diffForHumans() }}</small>
                </div>
            </div>
        </x-card-modern>
    </div>
</div>

<style>
    @media (max-width: 1024px) {
        [style*="grid-template-columns: 2fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection
