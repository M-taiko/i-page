@extends('layouts.app-modern')

@section('title', __('Users'))

@section('content')
<style>
    .verify-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: var(--radius-full); font-size: 10px; font-weight: var(--font-weight-bold); }
    .verify-badge.verified { background-color: var(--success-50); color: var(--success-700); }
    .verify-badge.unverified { background-color: var(--warning-50, #fffbeb); color: var(--warning-700, #b45309); }
    .invite-row { display: flex; align-items: center; gap: var(--space-3); padding: var(--space-3) var(--space-4); border-bottom: 1px solid var(--surface-border); }
    .invite-row:last-child { border-bottom: none; }
    .invite-avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); font-size: var(--text-xs); flex-shrink: 0; background: linear-gradient(135deg, var(--primary-500), var(--secondary-500)); }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-top">
        <div class="page-header-info">
            <h1>{{ __('Team Members') }}</h1>
            <p>{{ __('Manage your organization\'s people, roles, and business details') }}</p>
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

@if(session('success'))
    <x-alert-modern type="success" dismissible>{{ session('success') }}</x-alert-modern>
@endif
@if(session('error'))
    <x-alert-modern type="danger" dismissible>{{ session('error') }}</x-alert-modern>
@endif

<!-- Stats -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-6);">
    <x-stat-card title="{{ __('Active Members') }}" value="{{ $users->total() }}" icon="people" />
    <x-stat-card title="{{ __('Pending Invites') }}" value="{{ $pendingInvites->count() }}" icon="envelope-paper" />
    <x-stat-card title="{{ __('Join Requests') }}" value="{{ $joinRequests->count() }}" icon="hourglass-split" />
    <x-stat-card title="{{ __('Business Verified') }}" value="{{ $users->getCollection()->filter(fn($u) => $u->membership?->isBusinessVerified())->count() }}" icon="patch-check" />
</div>

<!-- Join Requests (self-initiated) -->
@if($joinRequests->count() > 0)
    <x-card-modern style="margin-bottom: var(--space-6); padding: 0;">
        <div style="padding: var(--space-4); border-bottom: 1px solid var(--surface-border); font-weight: var(--font-weight-bold); font-size: var(--text-sm);">
            <i class="bi bi-hourglass-split"></i> {{ __('Join Requests') }} ({{ $joinRequests->count() }})
        </div>
        @foreach($joinRequests as $request)
            <div class="invite-row">
                <div class="invite-avatar">{{ $request->user->initials }}</div>
                <div style="flex: 1;">
                    <div style="font-weight: var(--font-weight-semibold); font-size: var(--text-sm);">{{ $request->user->full_name }}</div>
                    <div style="font-size: var(--text-xs); color: var(--text-tertiary);">{{ $request->user->email }}</div>
                </div>
                <form action="{{ route('organizations.membership.approve', [$org, $request->user]) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> {{ __('Approve') }}</button>
                </form>
                <form action="{{ route('organizations.membership.reject', [$org, $request->user]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Reject this request?') }}')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i> {{ __('Reject') }}</button>
                </form>
            </div>
        @endforeach
    </x-card-modern>
@endif

<!-- Pending Invites (admin-initiated) -->
@if($pendingInvites->count() > 0)
    <x-card-modern style="margin-bottom: var(--space-6); padding: 0;">
        <div style="padding: var(--space-4); border-bottom: 1px solid var(--surface-border); font-weight: var(--font-weight-bold); font-size: var(--text-sm);">
            <i class="bi bi-envelope-paper"></i> {{ __('Pending Invites') }} ({{ $pendingInvites->count() }})
        </div>
        @foreach($pendingInvites as $invite)
            <div class="invite-row">
                <div class="invite-avatar">{{ $invite->user->initials }}</div>
                <div style="flex: 1;">
                    <div style="font-weight: var(--font-weight-semibold); font-size: var(--text-sm);">{{ $invite->user->full_name }}</div>
                    <div style="font-size: var(--text-xs); color: var(--text-tertiary);">
                        {{ __('Invited as :role by :name', ['role' => ucfirst(str_replace('_', ' ', $invite->role)), 'name' => $invite->invitedBy?->full_name ?? __('N/A')]) }}
                    </div>
                </div>
                <span class="verify-badge unverified"><i class="bi bi-clock"></i> {{ __('Awaiting response') }}</span>
                <form action="{{ route('organizations.membership.invite-user.cancel', [$org, $invite->user]) }}" method="POST" onsubmit="return confirm('{{ __('Cancel this invitation?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                </form>
            </div>
        @endforeach
    </x-card-modern>
@endif

<!-- Active Members Grid -->
@if ($users->count() > 0)
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-6);">
        @foreach ($users as $user)
            @php $membership = $user->membership; @endphp
            <x-card-modern>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                    <div style="display: flex; align-items: center; gap: var(--space-3); flex: 1; min-width: 0;">
                        <div style="width: 48px; height: 48px; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--primary-500), var(--secondary-500)); display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); font-size: var(--text-base); flex-shrink: 0; overflow: hidden;">
                            @if($user->avatar_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($user->avatar_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                {{ $user->initials }}
                            @endif
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="margin: 0; font-size: var(--text-base); font-weight: var(--font-weight-semibold); color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $user->full_name }}
                            </h3>
                            <p style="margin: 2px 0 0; font-size: var(--text-xs); color: var(--text-tertiary);">
                                {{ $user->username ? '@' . $user->username : $user->ipage_id }}
                            </p>
                        </div>
                    </div>
                    <span style="display: inline-flex; align-items: center; padding: var(--space-1) var(--space-3); background-color: var(--primary-50); color: var(--primary-700); border-radius: var(--radius-full); font-size: 11px; font-weight: var(--font-weight-medium); flex-shrink: 0;">
                        {{ ucfirst(str_replace('_', ' ', $membership?->role ?? 'member')) }}
                    </span>
                </div>

                <div style="border-top: 1px solid var(--surface-border); padding: var(--space-3) 0; display: grid; gap: var(--space-2);">
                    <div style="display: flex; justify-content: space-between; font-size: var(--text-xs);">
                        <span style="color: var(--text-tertiary);">{{ __('Email') }}</span>
                        <span style="color: var(--text-primary); text-align: end; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 60%;">{{ $user->email }}</span>
                    </div>
                    @if($membership?->job_title)
                        <div style="display: flex; justify-content: space-between; font-size: var(--text-xs);">
                            <span style="color: var(--text-tertiary);">{{ __('Job Title') }}</span>
                            <span style="color: var(--text-primary);">{{ $membership->job_title }}</span>
                        </div>
                    @endif
                    @if($membership?->employee_id)
                        <div style="display: flex; justify-content: space-between; font-size: var(--text-xs);">
                            <span style="color: var(--text-tertiary);">{{ __('Employee ID') }}</span>
                            <span style="color: var(--text-primary);">{{ $membership->employee_id }}</span>
                        </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: var(--text-xs);">
                        <span style="color: var(--text-tertiary);">{{ __('Business Info') }}</span>
                        @if($membership?->isBusinessVerified())
                            <span class="verify-badge verified"><i class="bi bi-patch-check-fill"></i> {{ __('Verified') }}</span>
                        @elseif($membership?->job_title || $membership?->employee_id)
                            <form action="{{ route('organizations.membership.verify', [$org, $user]) }}" method="POST">
                                @csrf
                                <button type="submit" class="verify-badge unverified" style="border: none; cursor: pointer;">
                                    <i class="bi bi-clock"></i> {{ __('Verify Now') }}
                                </button>
                            </form>
                        @else
                            <span class="verify-badge unverified"><i class="bi bi-dash"></i> {{ __('Not set') }}</span>
                        @endif
                    </div>
                </div>

                <div style="border-top: 1px solid var(--surface-border); padding-top: var(--space-3); margin-top: var(--space-3); display: flex; gap: var(--space-2);">
                    <button type="button" class="btn btn-sm" onclick="openBusinessModal({{ $user->id }}, '{{ addslashes($user->full_name) }}', '{{ addslashes($membership?->job_title ?? '') }}', '{{ addslashes($membership?->employee_id ?? '') }}')"
                       style="flex: 1; background-color: var(--surface-hover); color: var(--text-primary); border: 1px solid var(--surface-border); padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); font-size: var(--text-xs); font-weight: var(--font-weight-medium); display: flex; align-items: center; justify-content: center; gap: var(--space-1); cursor: pointer;">
                        <i class="bi bi-briefcase"></i> {{ __('Business') }}
                    </button>
                    <a href="{{ route('dashboard.users.edit', [$organization, $user]) }}"
                       class="btn btn-sm"
                       style="flex: 1; background-color: var(--warning-50); color: var(--warning-700); border: 1px solid var(--warning-200); text-decoration: none; padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); font-size: var(--text-xs); font-weight: var(--font-weight-medium); display: flex; align-items: center; justify-content: center; gap: var(--space-1);">
                        <i class="bi bi-pencil"></i> {{ __('Edit') }}
                    </a>
                    <form action="{{ route('dashboard.users.destroy', [$organization, $user]) }}" method="POST" style="flex: 1;">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-sm"
                                style="width: 100%; background-color: var(--danger-50); color: var(--danger-700); border: 1px solid var(--danger-200); padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); font-size: var(--text-xs); font-weight: var(--font-weight-medium); display: flex; align-items: center; justify-content: center; gap: var(--space-1); cursor: pointer;"
                                onclick="return confirm('{{ __('Remove this member from the organization?') }}')">
                            <i class="bi bi-trash"></i> {{ __('Remove') }}
                        </button>
                    </form>
                </div>
            </x-card-modern>
        @endforeach
    </div>

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

<!-- Business Details Modal -->
<div class="confirm-modal-overlay" id="businessModal" onclick="if(event.target===this) closeBusinessModal()" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1060; align-items:center; justify-content:center; padding: var(--space-4);">
    <div style="background-color: var(--surface-bg); border-radius: var(--radius-xl); padding: var(--space-6); max-width: 400px; width: 100%;">
        <h3 style="margin: 0 0 var(--space-4); font-size: var(--text-lg); color: var(--text-primary);" id="businessModalTitle"></h3>
        <form method="POST" id="businessModalForm">
            @csrf
            @method('PUT')
            <div style="margin-bottom: var(--space-4);">
                <label style="display: block; margin-bottom: var(--space-2); font-size: var(--text-sm); font-weight: var(--font-weight-semibold); color: var(--text-primary);">{{ __('Job Title') }}</label>
                <input type="text" name="job_title" id="businessModalJobTitle" style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg);">
            </div>
            <div style="margin-bottom: var(--space-6);">
                <label style="display: block; margin-bottom: var(--space-2); font-size: var(--text-sm); font-weight: var(--font-weight-semibold); color: var(--text-primary);">{{ __('Employee ID') }}</label>
                <input type="text" name="employee_id" id="businessModalEmployeeId" style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg);">
            </div>
            <div style="display: flex; gap: var(--space-3);">
                <button type="button" onclick="closeBusinessModal()" style="flex: 1; padding: var(--space-3); border-radius: var(--radius-md); background-color: var(--surface-hover); color: var(--text-primary); border: none; cursor: pointer;">{{ __('Cancel') }}</button>
                <button type="submit" style="flex: 1; padding: var(--space-3); border-radius: var(--radius-md); background-color: var(--primary-600); color: white; border: none; cursor: pointer;">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openBusinessModal(userId, name, jobTitle, employeeId) {
        document.getElementById('businessModalTitle').textContent = '{{ __('Business Details') }} — ' + name;
        document.getElementById('businessModalJobTitle').value = jobTitle;
        document.getElementById('businessModalEmployeeId').value = employeeId;
        document.getElementById('businessModalForm').action = '{{ route('organizations.membership.business-details.update-for', [$org, '__ID__']) }}'.replace('__ID__', userId);
        document.getElementById('businessModal').style.display = 'flex';
    }

    function closeBusinessModal() {
        document.getElementById('businessModal').style.display = 'none';
    }
</script>
@endsection
