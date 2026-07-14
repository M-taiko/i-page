@extends('layouts.app-modern')

@section('title', __('Users'))

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-top">
        <div class="page-header-info">
            <h1>{{ __('Team Members') }}</h1>
            <p>{{ __('Manage and organize your organization members') }}</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('dashboard.users.create', $organization) }}"
               class="btn btn-primary"
               style="text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-2);">
                <i class="bi bi-person-plus"></i>
                <span>{{ __('Add Member') }}</span>
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-6);">
    <x-stat-card
        title="{{ __('Total Members') }}"
        value="{{ $users->total() }}"
        icon="people"
    />
    <x-stat-card
        title="{{ __('Active') }}"
        value="{{ $users->where('deleted_at', null)->count() }}"
        icon="person-check"
    />
    <x-stat-card
        title="{{ __('Inactive') }}"
        value="{{ $users->where('deleted_at', '!=', null)->count() }}"
        icon="person-x"
    />
</div>

<!-- Users Grid -->
@if ($users->count() > 0)
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-6);">
        @foreach ($users as $user)
            <x-card-modern>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                    <div style="display: flex; align-items: center; gap: var(--space-3); flex: 1;">
                        <div style="width: 48px; height: 48px; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--primary-500), var(--secondary-500)); display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); font-size: var(--text-base);">
                            {{ $user->initials }}
                        </div>
                        <div style="flex: 1;">
                            <h3 style="margin: 0; font-size: var(--text-base); font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                                {{ $user->full_name }}
                            </h3>
                            <p style="margin: var(--space-1) 0 0; font-size: var(--text-xs); color: var(--text-tertiary);">
                                {{ $user->ipage_id }}
                            </p>
                        </div>
                    </div>
                    <div style="flex-shrink: 0;">
                        @if ($user->deleted_at)
                            <span style="display: inline-flex; align-items: center; gap: var(--space-1); padding: var(--space-1) var(--space-3); background-color: var(--danger-50); color: var(--danger-700); border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: var(--font-weight-medium);">
                                <i class="bi bi-dash-circle"></i>
                                {{ __('Inactive') }}
                            </span>
                        @else
                            <span style="display: inline-flex; align-items: center; gap: var(--space-1); padding: var(--space-1) var(--space-3); background-color: var(--success-50); color: var(--success-700); border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: var(--font-weight-medium);">
                                <i class="bi bi-check-circle"></i>
                                {{ __('Active') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div style="border-top: 1px solid var(--surface-border); padding: var(--space-3) 0; margin: var(--space-3) 0;">
                    <div style="font-size: var(--text-xs); color: var(--text-secondary); font-weight: var(--font-weight-medium); margin-bottom: var(--space-1);">
                        {{ __('Email') }}
                    </div>
                    <p style="margin: 0; font-size: var(--text-sm); color: var(--text-primary); word-break: break-all;">
                        {{ $user->email }}
                    </p>
                </div>

                @if ($user->mobile)
                    <div style="padding: var(--space-3) 0;">
                        <div style="font-size: var(--text-xs); color: var(--text-secondary); font-weight: var(--font-weight-medium); margin-bottom: var(--space-1);">
                            {{ __('Mobile') }}
                        </div>
                        <p style="margin: 0; font-size: var(--text-sm); color: var(--text-primary);">
                            {{ $user->mobile }}
                        </p>
                    </div>
                @endif

                <div style="padding: var(--space-3) 0;">
                    <div style="font-size: var(--text-xs); color: var(--text-secondary); font-weight: var(--font-weight-medium); margin-bottom: var(--space-1);">
                        {{ __('Role') }}
                    </div>
                    <span style="display: inline-flex; align-items: center; padding: var(--space-2) var(--space-3); background-color: var(--primary-50); color: var(--primary-700); border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: var(--font-weight-medium);">
                        <i class="bi bi-shield"></i>
                        {{ $user->roles()->first()?->name ?? __('No Role') }}
                    </span>
                </div>

                <div style="border-top: 1px solid var(--surface-border); padding-top: var(--space-3); margin-top: var(--space-3); display: flex; gap: var(--space-2);">
                    <a href="{{ route('dashboard.users.show', [$organization, $user]) }}"
                       class="btn btn-sm"
                       style="flex: 1; background-color: var(--surface-hover); color: var(--text-primary); border: 1px solid var(--surface-border); text-decoration: none; padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); font-size: var(--text-xs); font-weight: var(--font-weight-medium); display: flex; align-items: center; justify-content: center; gap: var(--space-1);">
                        <i class="bi bi-eye"></i>
                        {{ __('View') }}
                    </a>
                    <a href="{{ route('dashboard.users.edit', [$organization, $user]) }}"
                       class="btn btn-sm"
                       style="flex: 1; background-color: var(--warning-50); color: var(--warning-700); border: 1px solid var(--warning-200); text-decoration: none; padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); font-size: var(--text-xs); font-weight: var(--font-weight-medium); display: flex; align-items: center; justify-content: center; gap: var(--space-1);">
                        <i class="bi bi-pencil"></i>
                        {{ __('Edit') }}
                    </a>
                    <form action="{{ route('dashboard.users.destroy', [$organization, $user]) }}" method="POST" style="flex: 1;">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-sm"
                                style="width: 100%; background-color: var(--danger-50); color: var(--danger-700); border: 1px solid var(--danger-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); font-size: var(--text-xs); font-weight: var(--font-weight-medium); display: flex; align-items: center; justify-content: center; gap: var(--space-1); cursor: pointer;"
                                onclick="return confirm('{{ __('Are you sure?') }}')">
                            <i class="bi bi-trash"></i>
                            {{ __('Delete') }}
                        </button>
                    </form>
                </div>
            </x-card-modern>
        @endforeach
    </div>

    <!-- Pagination -->
    <div style="display: flex; justify-content: center; padding: var(--space-6) var(--space-4);">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
@else
    <x-empty-state-modern
        title="{{ __('No Team Members Yet') }}"
        message="{{ __('Start by adding your first team member to your organization.') }}"
        icon="people"
    >
        <a href="{{ route('dashboard.users.create', $organization) }}"
           class="btn btn-primary mt-4"
           style="text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-2);">
            <i class="bi bi-person-plus"></i>
            <span>{{ __('Add First Member') }}</span>
        </a>
    </x-empty-state-modern>
@endif
@endsection
