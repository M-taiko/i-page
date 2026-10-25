@extends('layouts.valex')

@section('title', __('Organization Settings'))

@section('page-header')
    <x-valex.page-header :title="$organization->name" :subtitle="__('Manage your organization settings, team, brands, and more')" />
@endsection

@section('content')
@php
    $pendingRequests = $organization->memberships()->where('status', 'pending')->with('user')->get();
    $pendingInvites = $organization->memberships()->where('status', 'invited')->with('user', 'invitedBy')->get();
    $activeMemberships = $organization->memberships()->where('status', 'active')->with('user', 'department')->get();
    $pendingCount = $pendingRequests->count() + $pendingInvites->count();
@endphp

<div class="row row-sm">
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Members')" :value="$activeMemberships->count()" icon="users" variant="primary" /></div>
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Pending')" :value="$pendingCount" icon="clock" variant="warning" /></div>
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Brands')" :value="$organization->brands->count()" icon="package" variant="success" /></div>
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Locations')" :value="$organization->locations->count()" icon="map-pin" variant="info" /></div>
</div>

<div class="card">
    <div class="card-body">
        <div class="tabs-menu">
            <ul class="nav nav-tabs profile navtab-custom panel-tabs" id="settingsTabs">
                <li><a href="#tab-general" data-toggle="tab" class="active"><i class="fe fe-settings"></i> {{ __('General') }}</a></li>
                <li>
                    <a href="#tab-members" data-toggle="tab"><i class="fe fe-users"></i> {{ __('Members') }}
                        @if ($pendingCount > 0)<x-valex.badge variant="danger">{{ $pendingCount }}</x-valex.badge>@endif
                    </a>
                </li>
                <li><a href="#tab-brands" data-toggle="tab"><i class="fe fe-package"></i> {{ __('Brands') }}</a></li>
                <li><a href="#tab-locations" data-toggle="tab"><i class="fe fe-map-pin"></i> {{ __('Locations') }}</a></li>
                <li><a href="#tab-sla" data-toggle="tab"><i class="fe fe-clock"></i> {{ __('SLA Rules') }}</a></li>
                <li><a href="#tab-design" data-toggle="tab"><i class="fe fe-layout"></i> {{ __('Design') }}</a></li>
            </ul>
        </div>

        <div class="tab-content border-left border-bottom border-right border-top-0 p-4">
            {{-- General --}}
            <div class="tab-pane active" id="tab-general">
                <form action="{{ route('organizations.update', $organization) }}" method="POST" style="max-width: 560px;">
                    @csrf
                    @method('PUT')
                    <x-valex.input name="name" :label="__('Organization Name')" :value="$organization->name" required />
                    <x-valex.input name="email" type="email" :label="__('Email')" :value="$organization->email" />
                    <x-valex.input name="phone" type="tel" :label="__('Phone')" :value="$organization->phone" />
                    <x-valex.input name="address" :label="__('Address')" :value="$organization->address" />
                    <div class="row">
                        <div class="col-md-6"><x-valex.input name="city" :label="__('City')" :value="$organization->city" /></div>
                        <div class="col-md-6"><x-valex.input name="country" :label="__('Country')" :value="$organization->country" /></div>
                    </div>
                    <button class="btn btn-primary" type="submit">{{ __('Save Changes') }}</button>
                </form>
            </div>

            {{-- Members --}}
            <div class="tab-pane" id="tab-members">
                @if ($pendingRequests->isNotEmpty())
                    <h6 class="mb-3"><i class="fe fe-clock"></i> {{ __('Join Requests') }} ({{ $pendingRequests->count() }})</h6>
                    <ul class="list-group mb-4">
                        @foreach ($pendingRequests as $request)
                            <li class="list-group-item d-flex align-items-center">
                                <span class="avatar avatar-md brround bg-primary mx-2">{{ $request->user->initials }}</span>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">{{ $request->user->full_name }}</div>
                                    <small class="text-muted">{{ $request->user->email }}</small>
                                </div>
                                <form action="{{ route('organizations.membership.approve', [$organization, $request->user]) }}" method="POST" class="mx-1">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"><i class="fe fe-check"></i> {{ __('Approve') }}</button>
                                </form>
                                <form action="{{ route('organizations.membership.reject', [$organization, $request->user]) }}" method="POST" onsubmit="return confirm('{{ __('Reject this request?') }}')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fe fe-x"></i></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($pendingInvites->isNotEmpty())
                    <h6 class="mb-3"><i class="fe fe-mail"></i> {{ __('Pending Invites') }} ({{ $pendingInvites->count() }})</h6>
                    <ul class="list-group mb-4">
                        @foreach ($pendingInvites as $invite)
                            <li class="list-group-item d-flex align-items-center">
                                <span class="avatar avatar-md brround bg-primary mx-2">{{ $invite->user->initials }}</span>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">{{ $invite->user->full_name }}</div>
                                    <small class="text-muted">{{ __('Invited as :role by :name', ['role' => ucfirst(str_replace('_', ' ', $invite->role)), 'name' => $invite->invitedBy?->full_name ?? __('N/A')]) }}</small>
                                </div>
                                <x-valex.badge variant="secondary" class="mx-2"><i class="fe fe-clock"></i> {{ __('Awaiting response') }}</x-valex.badge>
                                <form action="{{ route('organizations.membership.invite-user.cancel', [$organization, $invite->user]) }}" method="POST" onsubmit="return confirm('{{ __('Cancel this invitation?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fe fe-x"></i></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="fe fe-users"></i> {{ __('Team Members') }} ({{ $activeMemberships->count() }})</h6>
                    <a href="{{ route('dashboard.users.create', $organization) }}" class="btn btn-primary btn-sm"><i class="fe fe-plus"></i> {{ __('Add Member') }}</a>
                </div>
                <ul class="list-group">
                    @forelse ($activeMemberships as $membership)
                        @php $user = $membership->user; @endphp
                        <li class="list-group-item d-flex align-items-center flex-wrap">
                            @if ($user->avatar_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($user->avatar_path) }}" class="avatar avatar-md brround mx-2" alt="">
                            @else
                                <span class="avatar avatar-md brround bg-primary mx-2">{{ $user->initials }}</span>
                            @endif
                            <div class="flex-grow-1">
                                <div class="font-weight-bold">{{ $user->full_name }}</div>
                                <small class="text-muted d-block">{{ $user->email }}</small>
                                @if ($membership->job_title || $membership->employee_id)
                                    <small class="text-muted">{{ $membership->job_title }}@if ($membership->employee_id) &middot; {{ $membership->employee_id }}@endif</small>
                                @endif
                            </div>
                            <x-valex.badge variant="primary" class="mx-1">{{ ucfirst(str_replace('_', ' ', $membership->role)) }}</x-valex.badge>
                            @if ($membership->isBusinessVerified())
                                <x-valex.badge variant="success" class="mx-1"><i class="fe fe-check-circle"></i> {{ __('Verified') }}</x-valex.badge>
                            @elseif ($membership->job_title || $membership->employee_id)
                                <form action="{{ route('organizations.membership.verify', [$organization, $user]) }}" method="POST" class="mx-1">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">{{ __('Verify') }}</button>
                                </form>
                            @endif
                            <a href="{{ route('dashboard.users.edit', [$organization, $user]) }}" class="btn btn-sm btn-outline-warning mx-1"><i class="fe fe-edit"></i></a>
                            @if ($user->id !== auth()->id())
                                <form action="{{ route('dashboard.users.destroy', [$organization, $user]) }}" method="POST" onsubmit="return confirm('{{ __('Remove this member from the organization?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fe fe-trash-2"></i></button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">{{ __('No members yet') }}</li>
                    @endforelse
                </ul>
            </div>

            {{-- Brands --}}
            <div class="tab-pane" id="tab-brands">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="fe fe-package"></i> {{ __('Brands') }}</h6>
                    <a href="{{ route('organizations.brands.create', $organization) }}" class="btn btn-primary btn-sm"><i class="fe fe-plus"></i> {{ __('Add Brand') }}</a>
                </div>
                <ul class="list-group">
                    @forelse ($organization->brands as $brand)
                        <li class="list-group-item d-flex align-items-center">
                            <span class="avatar avatar-md brround bg-primary-transparent text-primary mx-2"><i class="fe fe-package"></i></span>
                            <div class="flex-grow-1 font-weight-bold">{{ $brand->name }}</div>
                            <x-valex.badge :variant="$brand->is_active ? 'success' : 'secondary'" class="mx-1">{{ $brand->is_active ? __('Active') : __('Inactive') }}</x-valex.badge>
                            <a href="{{ route('organizations.brands.edit', [$organization, $brand]) }}" class="btn btn-sm btn-outline-warning mx-1"><i class="fe fe-edit"></i></a>
                            <form action="{{ route('organizations.brands.destroy', [$organization, $brand]) }}" method="POST" onsubmit="return confirm('{{ __('Delete this brand? Its channels will be affected.') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fe fe-trash-2"></i></button>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">{{ __('No brands yet') }}</li>
                    @endforelse
                </ul>
            </div>

            {{-- Locations --}}
            <div class="tab-pane" id="tab-locations">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="fe fe-map-pin"></i> {{ __('Locations') }}</h6>
                    <a href="{{ route('organizations.locations.create', $organization) }}" class="btn btn-primary btn-sm"><i class="fe fe-plus"></i> {{ __('Add Location') }}</a>
                </div>
                <ul class="list-group">
                    @forelse ($organization->locations as $location)
                        <li class="list-group-item d-flex align-items-center">
                            <span class="avatar avatar-md brround bg-primary-transparent text-primary mx-2"><i class="fe fe-map-pin"></i></span>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold">{{ $location->name }}</div>
                                <small class="text-muted">{{ collect([$location->city, $location->country])->filter()->join(', ') ?: __('No address set') }}</small>
                            </div>
                            <a href="{{ route('organizations.locations.edit', [$organization, $location]) }}" class="btn btn-sm btn-outline-warning mx-1"><i class="fe fe-edit"></i></a>
                            <form action="{{ route('organizations.locations.destroy', [$organization, $location]) }}" method="POST" onsubmit="return confirm('{{ __('Delete this location?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fe fe-trash-2"></i></button>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">{{ __('No locations yet') }}</li>
                    @endforelse
                </ul>
            </div>

            {{-- SLA rules --}}
            <div class="tab-pane" id="tab-sla">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="fe fe-clock"></i> {{ __('SLA Rules') }}</h6>
                    <a href="{{ route('organizations.sla-rules.create', $organization) }}" class="btn btn-primary btn-sm"><i class="fe fe-plus"></i> {{ __('Add SLA Rule') }}</a>
                </div>
                <ul class="list-group">
                    @forelse ($organization->slaRules as $rule)
                        <li class="list-group-item d-flex align-items-center">
                            <span class="avatar avatar-md brround bg-primary-transparent text-primary mx-2"><i class="fe fe-clock"></i></span>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold">{{ $rule->name }}</div>
                                <small class="text-muted">
                                    {{ __('First response') }}: {{ $rule->first_response_time ? $rule->first_response_time . ' ' . __('min') : '—' }}
                                    &middot; {{ __('Resolution') }}: {{ $rule->resolution_time ? $rule->resolution_time . ' ' . __('min') : '—' }}
                                </small>
                            </div>
                            <x-valex.badge :variant="$rule->is_active ? 'success' : 'secondary'" class="mx-1">{{ $rule->is_active ? __('Active') : __('Inactive') }}</x-valex.badge>
                            <a href="{{ route('organizations.sla-rules.edit', [$organization, $rule]) }}" class="btn btn-sm btn-outline-warning mx-1"><i class="fe fe-edit"></i></a>
                            <form action="{{ route('organizations.sla-rules.destroy', [$organization, $rule]) }}" method="POST" onsubmit="return confirm('{{ __('Delete this SLA rule?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fe fe-trash-2"></i></button>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">{{ __('No SLA rules yet') }}</li>
                    @endforelse
                </ul>
            </div>

            {{-- Design --}}
            <div class="tab-pane" id="tab-design">
                @include('organizations._design-form')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Keep the active tab in the URL hash so links like #members still work.
    (function ($) {
        var hash = window.location.hash;
        if (hash && $('#settingsTabs a[href="#tab-' + hash.substring(1) + '"]').length) {
            $('#settingsTabs a[href="#tab-' + hash.substring(1) + '"]').tab('show');
        }
        $('#settingsTabs a').on('shown.bs.tab', function (e) {
            history.replaceState(null, '', '#' + $(e.target).attr('href').replace('#tab-', ''));
        });
    })(jQuery);
</script>
@endpush
