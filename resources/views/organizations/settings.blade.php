@extends('layouts.app-modern')

@section('content')
<style>
    .org-hero {
        background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
        color: white;
        padding: var(--space-8) var(--space-6);
        border-radius: var(--radius-xl);
        margin-bottom: var(--space-6);
        display: flex;
        align-items: center;
        gap: var(--space-4);
    }
    .org-hero-avatar {
        width: 64px; height: 64px; border-radius: var(--radius-lg);
        background: rgba(255,255,255,0.18);
        display: flex; align-items: center; justify-content: center;
        font-size: var(--text-2xl); font-weight: var(--font-weight-bold); flex-shrink: 0;
    }
    .org-hero h1 { margin: 0 0 4px; font-size: var(--text-2xl); }
    .org-hero p { margin: 0; opacity: 0.9; font-size: var(--text-sm); }

    .settings-tabs {
        display: flex; gap: var(--space-1);
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-lg);
        padding: var(--space-1);
        margin-bottom: var(--space-6);
        overflow-x: auto;
    }
    .settings-tab {
        display: flex; align-items: center; gap: var(--space-2);
        padding: var(--space-2) var(--space-4);
        border: none; background: none; cursor: pointer;
        border-radius: var(--radius-md);
        color: var(--text-secondary); font-size: var(--text-sm); font-weight: var(--font-weight-medium);
        white-space: nowrap; transition: all var(--transition-fast);
    }
    .settings-tab:hover { background-color: var(--surface-hover); color: var(--text-primary); }
    .settings-tab.active { background-color: var(--primary-600); color: white; }
    .settings-tab .count-pill {
        background-color: rgba(255,255,255,0.25); border-radius: var(--radius-full);
        padding: 0 6px; font-size: 10px; font-weight: var(--font-weight-bold);
    }
    .settings-tab:not(.active) .count-pill { background-color: var(--primary-50); color: var(--primary-700); }

    .tab-panel { display: none; }
    .tab-panel.active { display: block; }

    .list-card .card-body { padding: 0; }
    .list-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: var(--space-4); border-bottom: 1px solid var(--surface-border);
    }
    .list-card-header h5 { margin: 0; font-size: var(--text-base); font-weight: var(--font-weight-bold); color: var(--text-primary); display: flex; align-items: center; gap: var(--space-2); }

    .list-row {
        display: flex; align-items: center; gap: var(--space-3);
        padding: var(--space-3) var(--space-4);
        border-bottom: 1px solid var(--surface-border);
    }
    .list-row:last-child { border-bottom: none; }
    .list-row:hover { background-color: var(--surface-hover); }
    .row-avatar {
        width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0; overflow: hidden;
        display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); font-size: var(--text-xs);
        background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
    }
    .row-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .row-icon {
        width: 40px; height: 40px; border-radius: var(--radius-md); flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; background-color: var(--primary-50); color: var(--primary-600); font-size: var(--text-lg);
    }
    .row-title { font-weight: var(--font-weight-semibold); font-size: var(--text-sm); color: var(--text-primary); }
    .row-subtitle { font-size: var(--text-xs); color: var(--text-tertiary); margin-top: 2px; }
    .row-actions { display: flex; gap: var(--space-2); flex-shrink: 0; margin-inline-start: auto; }

    .pill { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: var(--radius-full); font-size: 11px; font-weight: var(--font-weight-semibold); }
    .pill-primary { background-color: var(--primary-50); color: var(--primary-700); }
    .pill-success { background-color: var(--success-50); color: var(--success-700); }
    .pill-muted { background-color: var(--surface-hover); color: var(--text-tertiary); }

    .empty-row { text-align: center; padding: var(--space-8) var(--space-4); color: var(--text-tertiary); font-size: var(--text-sm); }

    .form-label { font-weight: var(--font-weight-semibold); color: var(--text-primary); margin-bottom: var(--space-2); display: block; font-size: var(--text-sm); }
    .form-control, .form-select {
        border: 1px solid var(--surface-border); border-radius: var(--radius-md);
        padding: var(--space-2) var(--space-3); font-size: var(--text-sm); background-color: var(--surface-bg); color: var(--text-primary);
    }
    .form-control:focus, .form-select:focus { border-color: var(--primary-600); box-shadow: 0 0 0 3px var(--primary-50); outline: none; }
</style>

@php
    $pendingRequests = $organization->memberships()->where('status', 'pending')->with('user')->get();
    $pendingInvites = $organization->memberships()->where('status', 'invited')->with('user', 'invitedBy')->get();
    $activeMemberships = $organization->memberships()->where('status', 'active')->with('user', 'department')->get();
@endphp

<!-- Header -->
<div class="org-hero">
    <div class="org-hero-avatar">{{ substr($organization->name, 0, 1) }}</div>
    <div>
        <h1>{{ $organization->name }}</h1>
        <p>{{ __('Manage your organization settings, team, brands, and more') }}</p>
    </div>
</div>

<!-- Stats -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-6);">
    <x-stat-card title="{{ __('Members') }}" value="{{ $activeMemberships->count() }}" icon="people" />
    <x-stat-card title="{{ __('Pending') }}" value="{{ $pendingRequests->count() + $pendingInvites->count() }}" icon="hourglass-split" />
    <x-stat-card title="{{ __('Brands') }}" value="{{ $organization->brands->count() }}" icon="box-seam" />
    <x-stat-card title="{{ __('Locations') }}" value="{{ $organization->locations->count() }}" icon="geo-alt" />
</div>

<!-- Tabs -->
<nav class="settings-tabs" id="settingsTabs">
    <button type="button" class="settings-tab" data-tab="general"><i class="bi bi-gear"></i> {{ __('General') }}</button>
    <button type="button" class="settings-tab" data-tab="members">
        <i class="bi bi-people"></i> {{ __('Members') }}
        @if($pendingRequests->count() + $pendingInvites->count() > 0)
            <span class="count-pill">{{ $pendingRequests->count() + $pendingInvites->count() }}</span>
        @endif
    </button>
    <button type="button" class="settings-tab" data-tab="brands"><i class="bi bi-box-seam"></i> {{ __('Brands') }}</button>
    <button type="button" class="settings-tab" data-tab="locations"><i class="bi bi-geo-alt"></i> {{ __('Locations') }}</button>
    <button type="button" class="settings-tab" data-tab="sla"><i class="bi bi-clock-history"></i> {{ __('SLA Rules') }}</button>
</nav>

<!-- General -->
<div class="tab-panel" id="panel-general">
    <x-card-modern style="max-width: 560px;">
        <form action="{{ route('organizations.update', $organization) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom: var(--space-4);">
                <label class="form-label">{{ __('Organization Name') }}</label>
                <input type="text" class="form-control" style="width: 100%;" name="name" value="{{ $organization->name }}" required>
            </div>
            <div style="margin-bottom: var(--space-4);">
                <label class="form-label">{{ __('Email') }}</label>
                <input type="email" class="form-control" style="width: 100%;" name="email" value="{{ $organization->email }}">
            </div>
            <div style="margin-bottom: var(--space-4);">
                <label class="form-label">{{ __('Phone') }}</label>
                <input type="tel" class="form-control" style="width: 100%;" name="phone" value="{{ $organization->phone }}">
            </div>
            <div style="margin-bottom: var(--space-4);">
                <label class="form-label">{{ __('Address') }}</label>
                <input type="text" class="form-control" style="width: 100%;" name="address" value="{{ $organization->address }}">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-bottom: var(--space-6);">
                <div>
                    <label class="form-label">{{ __('City') }}</label>
                    <input type="text" class="form-control" style="width: 100%;" name="city" value="{{ $organization->city }}">
                </div>
                <div>
                    <label class="form-label">{{ __('Country') }}</label>
                    <input type="text" class="form-control" style="width: 100%;" name="country" value="{{ $organization->country }}">
                </div>
            </div>

            <button class="btn btn-primary" type="submit" style="background-color: var(--primary-600); color: white; border: none; padding: var(--space-2) var(--space-6); border-radius: var(--radius-md); font-size: var(--text-sm); font-weight: var(--font-weight-medium); cursor: pointer;">
                {{ __('Save Changes') }}
            </button>
        </form>
    </x-card-modern>
</div>

<!-- Members -->
<div class="tab-panel" id="panel-members">
    @if($pendingRequests->count() > 0)
        <x-card-modern class="list-card" style="margin-bottom: var(--space-4);">
            <div class="list-card-header">
                <h5><i class="bi bi-hourglass-split"></i> {{ __('Join Requests') }} ({{ $pendingRequests->count() }})</h5>
            </div>
            @foreach($pendingRequests as $request)
                <div class="list-row">
                    <div class="row-avatar">{{ $request->user->initials }}</div>
                    <div>
                        <div class="row-title">{{ $request->user->full_name }}</div>
                        <div class="row-subtitle">{{ $request->user->email }}</div>
                    </div>
                    <div class="row-actions">
                        <form action="{{ route('organizations.membership.approve', [$organization, $request->user]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> {{ __('Approve') }}</button>
                        </form>
                        <form action="{{ route('organizations.membership.reject', [$organization, $request->user]) }}" method="POST" onsubmit="return confirm('{{ __('Reject this request?') }}')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </div>
                </div>
            @endforeach
        </x-card-modern>
    @endif

    @if($pendingInvites->count() > 0)
        <x-card-modern class="list-card" style="margin-bottom: var(--space-4);">
            <div class="list-card-header">
                <h5><i class="bi bi-envelope-paper"></i> {{ __('Pending Invites') }} ({{ $pendingInvites->count() }})</h5>
            </div>
            @foreach($pendingInvites as $invite)
                <div class="list-row">
                    <div class="row-avatar">{{ $invite->user->initials }}</div>
                    <div>
                        <div class="row-title">{{ $invite->user->full_name }}</div>
                        <div class="row-subtitle">{{ __('Invited as :role by :name', ['role' => ucfirst(str_replace('_', ' ', $invite->role)), 'name' => $invite->invitedBy?->full_name ?? __('N/A')]) }}</div>
                    </div>
                    <div class="row-actions">
                        <span class="pill pill-muted"><i class="bi bi-clock"></i> {{ __('Awaiting response') }}</span>
                        <form action="{{ route('organizations.membership.invite-user.cancel', [$organization, $invite->user]) }}" method="POST" onsubmit="return confirm('{{ __('Cancel this invitation?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </div>
                </div>
            @endforeach
        </x-card-modern>
    @endif

    <x-card-modern class="list-card"  >
        <div class="list-card-header">
            <h5><i class="bi bi-people"></i> {{ __('Team Members') }} ({{ $activeMemberships->count() }})</h5>
            <a href="{{ route('dashboard.users.create', $organization) }}" class="btn btn-primary btn-sm" style="text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-1);">
                <i class="bi bi-plus-circle"></i> {{ __('Add Member') }}
            </a>
        </div>

        @forelse($activeMemberships as $membership)
            @php $user = $membership->user; @endphp
            <div class="list-row">
                <div class="row-avatar">
                    @if($user->avatar_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($user->avatar_path) }}" alt="">
                    @else
                        {{ $user->initials }}
                    @endif
                </div>
                <div style="min-width: 0;">
                    <div class="row-title">{{ $user->full_name }}</div>
                    <div class="row-subtitle">{{ $user->email }}</div>
                    @if($membership->job_title || $membership->employee_id)
                        <div class="row-subtitle">
                            {{ $membership->job_title }}
                            @if($membership->employee_id) &middot; {{ $membership->employee_id }} @endif
                        </div>
                    @endif
                </div>
                <div class="row-actions">
                    <span class="pill pill-primary">{{ ucfirst(str_replace('_', ' ', $membership->role)) }}</span>
                    @if($membership->isBusinessVerified())
                        <span class="pill pill-success"><i class="bi bi-patch-check-fill"></i> {{ __('Verified') }}</span>
                    @elseif($membership->job_title || $membership->employee_id)
                        <form action="{{ route('organizations.membership.verify', [$organization, $user]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success">{{ __('Verify') }}</button>
                        </form>
                    @endif
                    <a href="{{ route('dashboard.users.edit', [$organization, $user]) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                    @if($user->id !== auth()->id())
                        <form action="{{ route('dashboard.users.destroy', [$organization, $user]) }}" method="POST" onsubmit="return confirm('{{ __('Remove this member from the organization?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-row">{{ __('No members yet') }}</div>
        @endforelse
    </x-card-modern>
</div>

<!-- Brands -->
<div class="tab-panel" id="panel-brands">
    <x-card-modern class="list-card"  >
        <div class="list-card-header">
            <h5><i class="bi bi-box-seam"></i> {{ __('Brands') }}</h5>
            <a href="{{ route('organizations.brands.create', $organization) }}" class="btn btn-primary btn-sm" style="text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-1);">
                <i class="bi bi-plus-circle"></i> {{ __('Add Brand') }}
            </a>
        </div>
        @forelse($organization->brands as $brand)
            <div class="list-row">
                <div class="row-icon"><i class="bi bi-box-seam"></i></div>
                <div style="flex: 1;">
                    <div class="row-title">{{ $brand->name }}</div>
                </div>
                <div class="row-actions">
                    <span class="pill {{ $brand->is_active ? 'pill-success' : 'pill-muted' }}">{{ $brand->is_active ? __('Active') : __('Inactive') }}</span>
                    <a href="{{ route('organizations.brands.edit', [$organization, $brand]) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('organizations.brands.destroy', [$organization, $brand]) }}" method="POST" onsubmit="return confirm('{{ __('Delete this brand? Its channels will be affected.') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-row">{{ __('No brands yet') }}</div>
        @endforelse
    </x-card-modern>
</div>

<!-- Locations -->
<div class="tab-panel" id="panel-locations">
    <x-card-modern class="list-card"  >
        <div class="list-card-header">
            <h5><i class="bi bi-geo-alt"></i> {{ __('Locations') }}</h5>
            <a href="{{ route('organizations.locations.create', $organization) }}" class="btn btn-primary btn-sm" style="text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-1);">
                <i class="bi bi-plus-circle"></i> {{ __('Add Location') }}
            </a>
        </div>
        @forelse($organization->locations as $location)
            <div class="list-row">
                <div class="row-icon"><i class="bi bi-geo-alt"></i></div>
                <div style="flex: 1;">
                    <div class="row-title">{{ $location->name }}</div>
                    <div class="row-subtitle">{{ collect([$location->city, $location->country])->filter()->join(', ') ?: __('No address set') }}</div>
                </div>
                <div class="row-actions">
                    <a href="{{ route('organizations.locations.edit', [$organization, $location]) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('organizations.locations.destroy', [$organization, $location]) }}" method="POST" onsubmit="return confirm('{{ __('Delete this location?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-row">{{ __('No locations yet') }}</div>
        @endforelse
    </x-card-modern>
</div>

<!-- SLA Rules -->
<div class="tab-panel" id="panel-sla">
    <x-card-modern class="list-card"  >
        <div class="list-card-header">
            <h5><i class="bi bi-clock-history"></i> {{ __('SLA Rules') }}</h5>
            <a href="{{ route('organizations.sla-rules.create', $organization) }}" class="btn btn-primary btn-sm" style="text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-1);">
                <i class="bi bi-plus-circle"></i> {{ __('Add SLA Rule') }}
            </a>
        </div>
        @forelse($organization->slaRules as $rule)
            <div class="list-row">
                <div class="row-icon"><i class="bi bi-clock-history"></i></div>
                <div style="flex: 1;">
                    <div class="row-title">{{ $rule->name }}</div>
                    <div class="row-subtitle">
                        {{ __('First response') }}: {{ $rule->first_response_time ? $rule->first_response_time . ' ' . __('min') : '—' }}
                        &middot; {{ __('Resolution') }}: {{ $rule->resolution_time ? $rule->resolution_time . ' ' . __('min') : '—' }}
                    </div>
                </div>
                <div class="row-actions">
                    <span class="pill {{ $rule->is_active ? 'pill-success' : 'pill-muted' }}">{{ $rule->is_active ? __('Active') : __('Inactive') }}</span>
                    <a href="{{ route('organizations.sla-rules.edit', [$organization, $rule]) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('organizations.sla-rules.destroy', [$organization, $rule]) }}" method="POST" onsubmit="return confirm('{{ __('Delete this SLA rule?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-row">{{ __('No SLA rules yet') }}</div>
        @endforelse
    </x-card-modern>
</div>

<script>
    (function () {
        const tabs = document.querySelectorAll('.settings-tab');
        const panels = document.querySelectorAll('.tab-panel');

        function activate(tabName) {
            tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === tabName));
            panels.forEach(p => p.classList.toggle('active', p.id === 'panel-' + tabName));
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                activate(this.dataset.tab);
                history.replaceState(null, '', '#' + this.dataset.tab);
            });
        });

        const initial = window.location.hash ? window.location.hash.substring(1) : 'general';
        activate(document.getElementById('panel-' + initial) ? initial : 'general');
    })();
</script>
@endsection
