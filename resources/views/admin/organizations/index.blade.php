@extends('layouts.app-modern')

@section('title', __('Organizations Management'))

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-top">
        <div class="page-header-info">
            <h1>{{ __('Organizations') }}</h1>
            <p>{{ __('Manage all organizations in the system') }}</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.organizations.create') }}" class="btn btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-2);">
                <i class="bi bi-building"></i>
                <span>{{ __('Add Organization') }}</span>
            </a>
        </div>
    </div>
</div>

<!-- Success Message -->
@if(session('success'))
    <div style="background-color: var(--success-50); border: 1px solid var(--success-200); border-radius: var(--radius-md); padding: var(--space-3) var(--space-4); margin-bottom: var(--space-6); display: flex; align-items: center; gap: var(--space-3);">
        <i class="bi bi-check-circle" style="color: var(--success-600); font-size: var(--text-lg);"></i>
        <div style="flex: 1; color: var(--success-700);">{{ session('success') }}</div>
        <button onclick="this.parentElement.style.display='none';" style="background: none; border: none; color: var(--success-600); cursor: pointer; font-size: var(--text-lg);">
            <i class="bi bi-x"></i>
        </button>
    </div>
@endif

<!-- Statistics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-6);">
    <x-stat-card
        title="{{ __('Total Organizations') }}"
        value="{{ $organizations->total() }}"
        icon="building"
    />
    <x-stat-card
        title="{{ __('Active') }}"
        value="{{ $organizations->where('is_active', true)->count() }}"
        icon="check-circle"
    />
    <x-stat-card
        title="{{ __('Inactive') }}"
        value="{{ $organizations->where('is_active', false)->count() }}"
        icon="x-circle"
    />
</div>

<!-- Organizations Table -->
@if($organizations->count() > 0)
    <x-card-modern>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--surface-border);">
                        <th style="padding: var(--space-4); text-align: left; font-weight: var(--font-weight-semibold); color: var(--text-secondary); font-size: var(--text-sm);">{{ __('Organization') }}</th>
                        <th style="padding: var(--space-4); text-align: center; font-weight: var(--font-weight-semibold); color: var(--text-secondary); font-size: var(--text-sm);">{{ __('Max Channels') }}</th>
                        <th style="padding: var(--space-4); text-align: center; font-weight: var(--font-weight-semibold); color: var(--text-secondary); font-size: var(--text-sm);">{{ __('Users') }}</th>
                        <th style="padding: var(--space-4); text-align: center; font-weight: var(--font-weight-semibold); color: var(--text-secondary); font-size: var(--text-sm);">{{ __('Channels') }}</th>
                        <th style="padding: var(--space-4); text-align: center; font-weight: var(--font-weight-semibold); color: var(--text-secondary); font-size: var(--text-sm);">{{ __('Posts') }}</th>
                        <th style="padding: var(--space-4); text-align: center; font-weight: var(--font-weight-semibold); color: var(--text-secondary); font-size: var(--text-sm);">{{ __('Status') }}</th>
                        <th style="padding: var(--space-4); text-align: center; font-weight: var(--font-weight-semibold); color: var(--text-secondary); font-size: var(--text-sm);">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($organizations as $org)
                        <tr style="border-bottom: 1px solid var(--surface-border); transition: background-color var(--transition-fast);" onmouseover="this.style.backgroundColor='var(--surface-hover)'" onmouseout="this.style.backgroundColor='transparent'">
                            <td style="padding: var(--space-4);">
                                <div style="display: flex; align-items: center; gap: var(--space-3);">
                                    <div style="width: 40px; height: 40px; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--primary-500), var(--secondary-500)); display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); font-size: var(--text-sm);">
                                        {{ substr($org->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong style="display: block; font-size: var(--text-sm); color: var(--text-primary);">{{ $org->name }}</strong>
                                        <small style="color: var(--text-tertiary); display: block;">{{ Str::limit($org->description, 30) ?? __('No description') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: var(--space-4); text-align: center; color: var(--text-primary);">
                                <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: var(--primary-50); color: var(--primary-700); border-radius: var(--radius-md); font-weight: var(--font-weight-semibold); font-size: var(--text-sm);">
                                    {{ $org->max_channels }}
                                </span>
                            </td>
                            <td style="padding: var(--space-4); text-align: center;">
                                <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: var(--secondary-50); color: var(--secondary-700); border-radius: var(--radius-md); font-weight: var(--font-weight-semibold); font-size: var(--text-sm);">
                                    {{ $org->users()->count() }}
                                </span>
                            </td>
                            <td style="padding: var(--space-4); text-align: center;">
                                <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: var(--warning-50); color: var(--warning-700); border-radius: var(--radius-md); font-weight: var(--font-weight-semibold); font-size: var(--text-sm);">
                                    {{ $org->channels()->count() }}
                                </span>
                            </td>
                            <td style="padding: var(--space-4); text-align: center;">
                                <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: var(--success-50); color: var(--success-700); border-radius: var(--radius-md); font-weight: var(--font-weight-semibold); font-size: var(--text-sm);">
                                    {{ $org->posts()->count() }}
                                </span>
                            </td>
                            <td style="padding: var(--space-4); text-align: center;">
                                @if($org->is_active)
                                    <span style="display: inline-flex; align-items: center; gap: var(--space-1); padding: var(--space-1) var(--space-3); background-color: var(--success-50); color: var(--success-700); border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: var(--font-weight-medium);">
                                        <i class="bi bi-check-circle"></i>
                                        {{ __('Active') }}
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: var(--space-1); padding: var(--space-1) var(--space-3); background-color: var(--danger-50); color: var(--danger-700); border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: var(--font-weight-medium);">
                                        <i class="bi bi-x-circle"></i>
                                        {{ __('Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding: var(--space-4); text-align: center;">
                                <div style="display: flex; justify-content: center; gap: var(--space-2);">
                                    <a href="{{ route('admin.organizations.show', $org->id) }}" class="btn btn-icon btn-sm" title="{{ __('View') }}" style="color: var(--primary-600);">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.organizations.edit', $org->id) }}" class="btn btn-icon btn-sm" title="{{ __('Edit') }}" style="color: var(--warning-600);">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.organizations.destroy', $org->id) }}" method="POST" style="display: contents;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm" title="{{ __('Delete') }}" style="color: var(--danger-600);" onclick="return confirm('{{ __('Are you sure?') }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($organizations->hasPages())
            <div style="padding: var(--space-6) var(--space-4) 0; border-top: 1px solid var(--surface-border); display: flex; justify-content: center;">
                {{ $organizations->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </x-card-modern>
@else
    <x-empty-state-modern
        title="{{ __('No Organizations Yet') }}"
        message="{{ __('Start by creating your first organization.') }}"
        icon="building"
    >
        <a href="{{ route('admin.organizations.create') }}" class="btn btn-primary mt-4" style="text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-2);">
            <i class="bi bi-building"></i>
            <span>{{ __('Create First Organization') }}</span>
        </a>
    </x-empty-state-modern>
@endif

@endsection
