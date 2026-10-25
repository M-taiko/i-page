@extends('layouts.valex')

@section('title', __('Users'))

@section('page-header')
    <x-valex.page-header :title="__('Team Members')" :subtitle="__('Manage your organization\'s people, roles, and business details')">
        <x-slot:actions>
            <a href="{{ route('dashboard.users.create', $organization) }}" class="btn btn-primary"><i class="fe fe-user-plus"></i> {{ __('Add Member') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row row-sm">
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Active Members')" :value="$users->total()" icon="users" variant="primary" /></div>
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Pending Invites')" :value="$pendingInvites->count()" icon="mail" variant="warning" /></div>
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Join Requests')" :value="$joinRequests->count()" icon="clock" variant="info" /></div>
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Business Verified')" :value="$users->getCollection()->filter(fn ($u) => $u->membership?->isBusinessVerified())->count()" icon="check-circle" variant="success" /></div>
</div>

{{-- Join requests (self-initiated) --}}
@if ($joinRequests->count() > 0)
    <x-valex.card :title="__('Join Requests') . ' (' . $joinRequests->count() . ')'">
        <ul class="list-group list-group-flush">
            @foreach ($joinRequests as $request)
                <li class="list-group-item d-flex align-items-center px-0">
                    <span class="avatar avatar-md brround bg-primary mx-2">{{ $request->user->initials }}</span>
                    <div class="flex-grow-1">
                        <div class="font-weight-bold">{{ $request->user->full_name }}</div>
                        <small class="text-muted">{{ $request->user->email }}</small>
                    </div>
                    <form action="{{ route('organizations.membership.approve', [$org, $request->user]) }}" method="POST" class="d-inline mx-1">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success"><i class="fe fe-check"></i> {{ __('Approve') }}</button>
                    </form>
                    <form action="{{ route('organizations.membership.reject', [$org, $request->user]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Reject this request?') }}')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fe fe-x"></i> {{ __('Reject') }}</button>
                    </form>
                </li>
            @endforeach
        </ul>
    </x-valex.card>
@endif

{{-- Pending invites (admin-initiated) --}}
@if ($pendingInvites->count() > 0)
    <x-valex.card :title="__('Pending Invites') . ' (' . $pendingInvites->count() . ')'">
        <ul class="list-group list-group-flush">
            @foreach ($pendingInvites as $invite)
                <li class="list-group-item d-flex align-items-center px-0">
                    <span class="avatar avatar-md brround bg-primary mx-2">{{ $invite->user->initials }}</span>
                    <div class="flex-grow-1">
                        <div class="font-weight-bold">{{ $invite->user->full_name }}</div>
                        <small class="text-muted">{{ __('Invited as :role by :name', ['role' => ucfirst(str_replace('_', ' ', $invite->role)), 'name' => $invite->invitedBy?->full_name ?? __('N/A')]) }}</small>
                    </div>
                    <x-valex.badge variant="warning" class="mx-2"><i class="fe fe-clock"></i> {{ __('Awaiting response') }}</x-valex.badge>
                    <form action="{{ route('organizations.membership.invite-user.cancel', [$org, $invite->user]) }}" method="POST" onsubmit="return confirm('{{ __('Cancel this invitation?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fe fe-x"></i></button>
                    </form>
                </li>
            @endforeach
        </ul>
    </x-valex.card>
@endif

{{-- Active members --}}
@if ($users->count() > 0)
    <div class="row row-sm">
        @foreach ($users as $user)
            @php $membership = $user->membership; @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center" style="min-width: 0;">
                                @if ($user->avatar_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($user->avatar_path) }}" alt="" class="avatar avatar-lg brround mx-2">
                                @else
                                    <span class="avatar avatar-lg brround bg-primary mx-2">{{ $user->initials }}</span>
                                @endif
                                <div style="min-width: 0;">
                                    <h6 class="mb-0 text-truncate">{{ $user->full_name }}</h6>
                                    <small class="text-muted">{{ $user->username ? '@' . $user->username : $user->ipage_id }}</small>
                                </div>
                            </div>
                            <x-valex.badge variant="primary">{{ ucfirst(str_replace('_', ' ', $membership?->role ?? 'member')) }}</x-valex.badge>
                        </div>

                        <ul class="list-unstyled small border-top pt-3 mb-3">
                            <li class="d-flex justify-content-between mb-1"><span class="text-muted">{{ __('Email') }}</span><span class="text-truncate" style="max-width: 60%;">{{ $user->email }}</span></li>
                            @if ($membership?->job_title)
                                <li class="d-flex justify-content-between mb-1"><span class="text-muted">{{ __('Job Title') }}</span><span>{{ $membership->job_title }}</span></li>
                            @endif
                            @if ($membership?->employee_id)
                                <li class="d-flex justify-content-between mb-1"><span class="text-muted">{{ __('Employee ID') }}</span><span>{{ $membership->employee_id }}</span></li>
                            @endif
                            <li class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">{{ __('Business Info') }}</span>
                                @if ($membership?->isBusinessVerified())
                                    <x-valex.badge variant="success"><i class="fe fe-check-circle"></i> {{ __('Verified') }}</x-valex.badge>
                                @elseif ($membership?->job_title || $membership?->employee_id)
                                    <form action="{{ route('organizations.membership.verify', [$org, $user]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning py-0"><i class="fe fe-clock"></i> {{ __('Verify Now') }}</button>
                                    </form>
                                @else
                                    <x-valex.badge variant="secondary">{{ __('Not set') }}</x-valex.badge>
                                @endif
                            </li>
                        </ul>

                        <div class="d-flex border-top pt-3">
                            <button type="button" class="btn btn-sm btn-light flex-fill mx-1"
                                    onclick="openBusinessModal({{ $user->id }}, '{{ addslashes($user->full_name) }}', '{{ addslashes($membership?->job_title ?? '') }}', '{{ addslashes($membership?->employee_id ?? '') }}')">
                                <i class="fe fe-briefcase"></i> {{ __('Business') }}
                            </button>
                            <a href="{{ route('dashboard.users.edit', [$organization, $user]) }}" class="btn btn-sm btn-outline-warning flex-fill mx-1"><i class="fe fe-edit"></i> {{ __('Edit') }}</a>
                            <form action="{{ route('dashboard.users.destroy', [$organization, $user]) }}" method="POST" class="flex-fill mx-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger btn-block" onclick="return confirm('{{ __('Remove this member from the organization?') }}')"><i class="fe fe-trash-2"></i> {{ __('Remove') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center">{{ $users->links('pagination::bootstrap-4') }}</div>
@else
    <x-valex.empty-state :title="__('No Team Members Yet')" :message="__('Start by adding your first team member to your organization.')" icon="users">
        <a href="{{ route('dashboard.users.create', $organization) }}" class="btn btn-primary mt-3"><i class="fe fe-user-plus"></i> {{ __('Add First Member') }}</a>
    </x-valex.empty-state>
@endif

{{-- Business details modal --}}
<div class="modal fade" id="businessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="businessModalForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="businessModalTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>{{ __('Job Title') }}</label><input type="text" name="job_title" id="businessModalJobTitle" class="form-control"></div>
                    <div class="form-group mb-0"><label>{{ __('Employee ID') }}</label><input type="text" name="employee_id" id="businessModalEmployeeId" class="form-control"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openBusinessModal(userId, name, jobTitle, employeeId) {
        document.getElementById('businessModalTitle').textContent = '{{ __('Business Details') }} — ' + name;
        document.getElementById('businessModalJobTitle').value = jobTitle;
        document.getElementById('businessModalEmployeeId').value = employeeId;
        document.getElementById('businessModalForm').action = '{{ route('organizations.membership.business-details.update-for', [$org, '__ID__']) }}'.replace('__ID__', userId);
        jQuery('#businessModal').modal('show');
    }
</script>
@endpush
