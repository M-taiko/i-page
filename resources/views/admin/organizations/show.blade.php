@extends('layouts.app-modern')

@section('title', $organization->name)

@section('content')
<!-- Page Header with Back Button -->
<div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-6);">
    <a href="{{ route('admin.organizations.index') }}" class="btn" style="background-color: var(--surface-hover); color: var(--text-primary); border: 1px solid var(--surface-border); padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-2);">
        <i class="bi bi-arrow-left"></i>
        <span>{{ __('Back') }}</span>
    </a>
</div>

<!-- Header Section -->
<x-card-modern style="background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%); color: white; margin-bottom: var(--space-6); position: relative; overflow: hidden;">
    <div style="position: absolute; top: -40px; right: -40px; width: 200px; height: 200px; background: rgba(255, 255, 255, 0.1); border-radius: var(--radius-full);"></div>
    <div style="position: relative; z-index: 1;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: var(--space-4);">
            <div style="flex: 1;">
                <h1 style="margin: 0 0 var(--space-2); font-size: var(--text-4xl); font-weight: var(--font-weight-bold); color: white;">
                    {{ $organization->name }}
                </h1>
                <p style="margin: 0 0 var(--space-3); opacity: 0.9; color: white;">
                    {{ $organization->description ?? __('No description provided') }}
                </p>
                <div style="display: flex; align-items: center; gap: var(--space-2); flex-wrap: wrap;">
                    @if($organization->is_active)
                        <span style="display: inline-flex; align-items: center; gap: var(--space-1); padding: var(--space-1) var(--space-3); background-color: rgba(16, 185, 129, 0.2); color: white; border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: var(--font-weight-medium); border: 1px solid rgba(16, 185, 129, 0.3);">
                            <i class="bi bi-check-circle"></i>
                            {{ __('Active') }}
                        </span>
                    @else
                        <span style="display: inline-flex; align-items: center; gap: var(--space-1); padding: var(--space-1) var(--space-3); background-color: rgba(239, 68, 68, 0.2); color: white; border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: var(--font-weight-medium); border: 1px solid rgba(239, 68, 68, 0.3);">
                            <i class="bi bi-x-circle"></i>
                            {{ __('Inactive') }}
                        </span>
                    @endif
                    @if($organization->city || $organization->country)
                        <span style="display: inline-flex; align-items: center; gap: var(--space-1); padding: var(--space-1) var(--space-3); background-color: rgba(255, 255, 255, 0.1); color: white; border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: var(--font-weight-medium);">
                            <i class="bi bi-geo-alt"></i>
                            {{ $organization->city ?? '' }}{{ $organization->city && $organization->country ? ', ' : '' }}{{ $organization->country ?? '' }}
                        </span>
                    @endif
                </div>
            </div>
            <div style="flex-shrink: 0; width: 80px; height: 80px; background: rgba(255, 255, 255, 0.15); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center; font-size: var(--text-4xl); font-weight: var(--font-weight-bold); color: rgba(255, 255, 255, 0.8);">
                {{ substr($organization->name, 0, 1) }}
            </div>
        </div>
    </div>
</x-card-modern>

<!-- Statistics Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-6);">
    <x-stat-card
        title="{{ __('Max Channels') }}"
        value="{{ $organization->max_channels }}"
        icon="diagram-3"
    />
    <x-stat-card
        title="{{ __('Active Channels') }}"
        value="{{ $organization->channels()->count() }}"
        icon="chat-dots"
    />
    <x-stat-card
        title="{{ __('Team Members') }}"
        value="{{ $organization->users()->count() }}"
        icon="people"
    />
    <x-stat-card
        title="{{ __('Total Posts') }}"
        value="{{ $organization->posts()->count() }}"
        icon="newspaper"
    />
</div>

<!-- Main Content Grid -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-6); margin-bottom: var(--space-6);">
    <!-- Left Column -->
    <div>
        <!-- Organization Information Card -->
        <x-card-modern style="margin-bottom: var(--space-6);">
            <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-6); padding-bottom: var(--space-6); border-bottom: 1px solid var(--surface-border);">
                <i class="bi bi-info-circle" style="font-size: var(--text-2xl); color: var(--primary-600);"></i>
                <h2 style="margin: 0; font-size: var(--text-xl); font-weight: var(--font-weight-bold); color: var(--text-primary);">
                    {{ __('Organization Information') }}
                </h2>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6);">
                <div>
                    <p style="margin: 0 0 var(--space-1); font-size: var(--text-xs); font-weight: var(--font-weight-semibold); color: var(--text-secondary); text-transform: uppercase;">
                        {{ __('Organization Name') }}
                    </p>
                    <p style="margin: 0; font-size: var(--text-base); color: var(--text-primary); font-weight: var(--font-weight-semibold);">
                        {{ $organization->name }}
                    </p>
                </div>

                <div>
                    <p style="margin: 0 0 var(--space-1); font-size: var(--text-xs); font-weight: var(--font-weight-semibold); color: var(--text-secondary); text-transform: uppercase;">
                        {{ __('Status') }}
                    </p>
                    @if($organization->is_active)
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
                </div>

                <div>
                    <p style="margin: 0 0 var(--space-1); font-size: var(--text-xs); font-weight: var(--font-weight-semibold); color: var(--text-secondary); text-transform: uppercase;">
                        {{ __('City') }}
                    </p>
                    <p style="margin: 0; font-size: var(--text-base); color: var(--text-primary);">
                        {{ $organization->city ?? __('Not specified') }}
                    </p>
                </div>

                <div>
                    <p style="margin: 0 0 var(--space-1); font-size: var(--text-xs); font-weight: var(--font-weight-semibold); color: var(--text-secondary); text-transform: uppercase;">
                        {{ __('Country') }}
                    </p>
                    <p style="margin: 0; font-size: var(--text-base); color: var(--text-primary);">
                        {{ $organization->country ?? __('Not specified') }}
                    </p>
                </div>
            </div>

            <div style="margin-top: var(--space-6); padding-top: var(--space-6); border-top: 1px solid var(--surface-border);">
                <p style="margin: 0 0 var(--space-1); font-size: var(--text-xs); font-weight: var(--font-weight-semibold); color: var(--text-secondary); text-transform: uppercase;">
                    {{ __('Description') }}
                </p>
                <p style="margin: 0; font-size: var(--text-base); color: var(--text-primary); line-height: var(--line-height-normal);">
                    {{ $organization->description ?? __('No description provided') }}
                </p>
            </div>
        </x-card-modern>

        <!-- Settings Card -->
        <x-card-modern>
            <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-6); padding-bottom: var(--space-6); border-bottom: 1px solid var(--surface-border);">
                <i class="bi bi-gear" style="font-size: var(--text-2xl); color: var(--primary-600);"></i>
                <h2 style="margin: 0; font-size: var(--text-xl); font-weight: var(--font-weight-bold); color: var(--text-primary);">
                    {{ __('Settings') }}
                </h2>
            </div>

            <!-- Max Channels Setting -->
            <div style="margin-bottom: var(--space-6); padding-bottom: var(--space-6); border-bottom: 1px solid var(--surface-border);">
                <p style="margin: 0 0 var(--space-2); font-size: var(--text-xs); font-weight: var(--font-weight-semibold); color: var(--text-secondary); text-transform: uppercase;">
                    {{ __('Max Channels Allowed') }}
                </p>
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background-color: var(--primary-50); color: var(--primary-700); border-radius: var(--radius-md); font-weight: var(--font-weight-bold); font-size: var(--text-base);">
                    {{ $organization->max_channels }}
                </span>
            </div>

            <!-- Status Toggle -->
            <div style="margin-bottom: var(--space-6); padding-bottom: var(--space-6); border-bottom: 1px solid var(--surface-border);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="margin: 0 0 var(--space-1); font-size: var(--text-sm); font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                            {{ __('Organization Status') }}
                        </p>
                        <p style="margin: 0; font-size: var(--text-xs); color: var(--text-secondary);">
                            @if($organization->is_active)
                                {{ __('Organization is active. Team members can access and use all features.') }}
                            @else
                                {{ __('Organization is inactive. Team members cannot access the platform.') }}
                            @endif
                        </p>
                    </div>

                    <button type="button" id="toggle-btn" onclick="toggleOrganizationStatus()" style="position: relative; width: 50px; height: 28px; background-color: {{ $organization->is_active ? 'var(--success-500)' : 'var(--neutral-300)' }}; border-radius: 14px; border: none; cursor: pointer; transition: all var(--transition-fast); padding: 0;">
                        <div style="position: absolute; top: 2px; {{ $organization->is_active ? 'right: 2px' : 'left: 2px' }}; width: 24px; height: 24px; background-color: white; border-radius: 50%; transition: all var(--transition-fast); box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
                    </button>
                </div>
            </div>

            <form id="status-form" action="{{ route('admin.organizations.update', $organization->id) }}" method="POST" style="display: none;">
                @csrf
                @method('PUT')
                <input type="hidden" name="is_active" id="is_active_input" value="">
                <input type="hidden" name="name" value="{{ $organization->name }}">
                <input type="hidden" name="description" value="{{ $organization->description }}">
                <input type="hidden" name="city" value="{{ $organization->city }}">
                <input type="hidden" name="country" value="{{ $organization->country }}">
                <input type="hidden" name="max_channels" value="{{ $organization->max_channels }}">
            </form>

            <!-- Created Date -->
            <div>
                <p style="margin: 0 0 var(--space-1); font-size: var(--text-xs); font-weight: var(--font-weight-semibold); color: var(--text-secondary); text-transform: uppercase;">
                    {{ __('Created') }}
                </p>
                <p style="margin: 0; font-size: var(--text-base); color: var(--text-primary);">
                    {{ $organization->created_at->format('d M Y, H:i') }}
                </p>
            </div>
        </x-card-modern>

        <!-- Brands & Channels Card -->
        <x-card-modern style="margin-top: var(--space-6);">
            <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-4); padding-bottom: var(--space-4); border-bottom: 1px solid var(--surface-border);">
                <i class="bi bi-diagram-3" style="font-size: var(--text-2xl); color: var(--primary-600);"></i>
                <h2 style="margin: 0; font-size: var(--text-xl); font-weight: var(--font-weight-bold); color: var(--text-primary);">
                    {{ __('Brands & Channels') }}
                </h2>
                <div style="margin-left: auto; display: flex; gap: var(--space-2);">
                    <a href="{{ route('admin.organizations.brands.create', $organization) }}" class="btn" style="background-color: var(--primary-50); color: var(--primary-700); border: 1px solid var(--primary-200); padding: var(--space-1) var(--space-3); border-radius: var(--radius-md); text-decoration: none; font-size: var(--text-xs); font-weight: var(--font-weight-medium);">
                        <i class="bi bi-plus-lg"></i> {{ __('Brand') }}
                    </a>
                    <a href="{{ route('admin.organizations.channels.create', $organization) }}" class="btn" style="background-color: var(--primary-600); color: white; padding: var(--space-1) var(--space-3); border-radius: var(--radius-md); text-decoration: none; font-size: var(--text-xs); font-weight: var(--font-weight-medium);">
                        <i class="bi bi-plus-lg"></i> {{ __('Channel') }}
                    </a>
                </div>
            </div>

            @forelse($organization->brands as $brand)
                <div style="margin-bottom: var(--space-4);">
                    <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-2);">
                        <i class="bi bi-bookmark-star" style="color: var(--secondary-600);"></i>
                        <strong style="color: var(--text-primary);">{{ $brand->name }}</strong>
                        <a href="{{ route('admin.organizations.brands.edit', [$organization, $brand]) }}" style="font-size: var(--text-xs); color: var(--primary-600); text-decoration: none;">{{ __('edit') }}</a>
                    </div>
                    <div style="padding-left: var(--space-6); display: flex; flex-direction: column; gap: var(--space-1);">
                        @forelse($brand->channels as $channel)
                            <div style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--text-sm);">
                                <i class="bi bi-{{ $channel->type === 'public' ? 'globe' : 'lock' }}" style="color: var(--text-tertiary);"></i>
                                <span style="color: var(--text-primary);">{{ $channel->name }}</span>
                                <span style="font-size: var(--text-xs); color: var(--text-tertiary);">({{ $channel->type }})</span>
                                <a href="{{ route('admin.organizations.channels.edit', [$organization, $channel]) }}" style="font-size: var(--text-xs); color: var(--primary-600); text-decoration: none; margin-left: auto;">{{ __('edit') }}</a>
                            </div>
                        @empty
                            <span style="font-size: var(--text-xs); color: var(--text-tertiary);">{{ __('No channels yet') }}</span>
                        @endforelse
                    </div>
                </div>
            @empty
                <p style="color: var(--text-secondary); font-size: var(--text-sm);">{{ __('No brands yet. Create one to start adding channels.') }}</p>
            @endforelse
        </x-card-modern>
    </div>

    <!-- Right Column -->
    <div>
        <!-- Team Members Card -->
        <x-card-modern style="margin-bottom: var(--space-6);">
            <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-4); padding-bottom: var(--space-4); border-bottom: 1px solid var(--surface-border);">
                <i class="bi bi-people" style="font-size: var(--text-2xl); color: var(--primary-600);"></i>
                <h2 style="margin: 0; font-size: var(--text-lg); font-weight: var(--font-weight-bold); color: var(--text-primary);">
                    {{ __('Team Members') }}
                </h2>
                <span style="margin-left: auto; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; background-color: var(--primary-50); color: var(--primary-700); border-radius: var(--radius-md); font-weight: var(--font-weight-bold); font-size: var(--text-xs);">
                    {{ $organization->users()->count() }}
                </span>
            </div>

            @if($organization->users()->count() > 0)
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--surface-border);">
                                <th style="padding: var(--space-3); text-align: left; font-weight: var(--font-weight-semibold); color: var(--text-secondary); font-size: var(--text-xs); text-transform: uppercase;">{{ __('Name') }}</th>
                                <th style="padding: var(--space-3); text-align: left; font-weight: var(--font-weight-semibold); color: var(--text-secondary); font-size: var(--text-xs); text-transform: uppercase;">{{ __('Email') }}</th>
                                <th style="padding: var(--space-3); text-align: center; font-weight: var(--font-weight-semibold); color: var(--text-secondary); font-size: var(--text-xs); text-transform: uppercase;">{{ __('Role') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($organization->users as $user)
                                <tr style="border-bottom: 1px solid var(--surface-border); transition: background-color var(--transition-fast);" onmouseover="this.style.backgroundColor='var(--surface-hover)'" onmouseout="this.style.backgroundColor='transparent'">
                                    <td style="padding: var(--space-3);">
                                        <div style="display: flex; align-items: center; gap: var(--space-2);">
                                            <div style="width: 32px; height: 32px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--primary-500), var(--secondary-500)); display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); font-size: var(--text-xs);">
                                                {{ $user->initials }}
                                            </div>
                                            <span style="font-size: var(--text-sm); font-weight: var(--font-weight-medium); color: var(--text-primary);">
                                                {{ $user->full_name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td style="padding: var(--space-3); font-size: var(--text-sm); color: var(--text-secondary);">
                                        {{ $user->email }}
                                    </td>
                                    <td style="padding: var(--space-3); text-align: center;">
                                        <span style="display: inline-flex; align-items: center; padding: var(--space-1) var(--space-2); background-color: var(--primary-50); color: var(--primary-700); border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: var(--font-weight-medium);">
                                            {{ $user->pivot->role }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: var(--space-6);">
                    <i class="bi bi-person-slash" style="font-size: var(--text-3xl); color: var(--text-tertiary); display: block; margin-bottom: var(--space-2);"></i>
                    <p style="margin: 0; color: var(--text-secondary);">{{ __('No team members') }}</p>
                </div>
            @endif
        </x-card-modern>

        <!-- Actions Card -->
        <x-card-modern>
            <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-4); padding-bottom: var(--space-4); border-bottom: 1px solid var(--surface-border);">
                <i class="bi bi-lightning" style="font-size: var(--text-2xl); color: var(--primary-600);"></i>
                <h2 style="margin: 0; font-size: var(--text-lg); font-weight: var(--font-weight-bold); color: var(--text-primary);">
                    {{ __('Actions') }}
                </h2>
            </div>

            <!-- Subscription status -->
            <div style="margin-bottom: var(--space-4); padding-bottom: var(--space-4); border-bottom: 1px solid var(--surface-border);">
                <p style="margin: 0 0 var(--space-2); font-size: var(--text-xs); font-weight: var(--font-weight-semibold); color: var(--text-secondary); text-transform: uppercase;">
                    {{ __('Subscription') }}: <strong style="color: var(--text-primary);">{{ ucfirst($organization->status ?? 'active') }}</strong>
                </p>
                <div style="display: flex; flex-direction: column; gap: var(--space-2);">
                    @if(($organization->status ?? 'active') === 'active')
                        <form action="{{ route('admin.organizations.suspend', $organization) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn" style="width: 100%; background-color: var(--warning-50); color: var(--warning-700); border: 1px solid var(--warning-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); font-size: var(--text-sm); cursor: pointer;">
                                <i class="bi bi-pause-circle"></i> {{ __('Suspend') }}
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.organizations.activate', $organization) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn" style="width: 100%; background-color: var(--success-50); color: var(--success-700); border: 1px solid var(--success-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); font-size: var(--text-sm); cursor: pointer;">
                                <i class="bi bi-play-circle"></i> {{ __('Reactivate') }}
                            </button>
                        </form>
                    @endif
                    @if(($organization->status ?? 'active') !== 'cancelled')
                        <form action="{{ route('admin.organizations.cancel', $organization) }}" method="POST" onsubmit="return confirm('{{ __('Cancel this organization\'s subscription?') }}')">
                            @csrf
                            <button type="submit" class="btn" style="width: 100%; background-color: var(--danger-50); color: var(--danger-700); border: 1px solid var(--danger-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); font-size: var(--text-sm); cursor: pointer;">
                                <i class="bi bi-x-octagon"></i> {{ __('Cancel Subscription') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                <a href="{{ route('admin.organizations.edit', $organization->id) }}" class="btn" style="background-color: var(--warning-50); color: var(--warning-700); border: 1px solid var(--warning-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); text-decoration: none; display: flex; align-items: center; justify-content: center; gap: var(--space-2); font-size: var(--text-sm); font-weight: var(--font-weight-medium);">
                    <i class="bi bi-pencil"></i>
                    <span>{{ __('Edit Organization') }}</span>
                </a>

                <form action="{{ route('admin.organizations.destroy', $organization->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn" style="width: 100%; background-color: var(--danger-50); color: var(--danger-700); border: 1px solid var(--danger-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; gap: var(--space-2); font-size: var(--text-sm); font-weight: var(--font-weight-medium); cursor: pointer;" onclick="return confirm('{{ __('Are you sure you want to delete this organization? This action cannot be undone.') }}')">
                        <i class="bi bi-trash"></i>
                        <span>{{ __('Delete Organization') }}</span>
                    </button>
                </form>
            </div>
        </x-card-modern>
    </div>
</div>

<script>
    function toggleOrganizationStatus() {
        const isActive = {{ $organization->is_active ? 'true' : 'false' }};
        const newStatus = isActive ? 0 : 1;
        const btn = document.getElementById('toggle-btn');
        const input = document.getElementById('is_active_input');

        input.value = newStatus;

        // Update button appearance
        if (newStatus === 1) {
            btn.style.backgroundColor = 'var(--success-500)';
            btn.querySelector('div').style.right = '2px';
            btn.querySelector('div').style.left = 'auto';
        } else {
            btn.style.backgroundColor = 'var(--neutral-300)';
            btn.querySelector('div').style.left = '2px';
            btn.querySelector('div').style.right = 'auto';
        }

        // Submit form
        document.getElementById('status-form').submit();
    }
</script>
@endsection
