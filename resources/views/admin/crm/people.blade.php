@extends('layouts.valex')

@section('title', __('People'))

@section('page-header')
    <x-valex.page-header :title="__('Platform CRM')" :subtitle="__('People')">
        <x-slot:actions>
            <a href="{{ route('admin.crm.index') }}" class="btn btn-outline-secondary mx-1"><i class="fe fe-briefcase"></i> {{ __('Organizations 360') }}</a>
            <a href="{{ route('admin.crm.activity') }}" class="btn btn-outline-primary mx-1"><i class="fe fe-activity"></i> {{ __('Activity Log') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row row-sm">
    <div class="col-sm-4"><x-valex.stat-card :title="__('All accounts')" :value="$summary['users']" icon="users" variant="primary" /></div>
    <div class="col-sm-4"><x-valex.stat-card :title="__('Visitors (no organization)')" :value="$summary['visitors']" icon="user" variant="purple" /></div>
    <div class="col-sm-4"><x-valex.stat-card :title="__('Staff (in an organization)')" :value="$summary['users'] - $summary['visitors']" icon="briefcase" variant="success" /></div>
</div>

<x-valex.card>
    <form method="GET" class="form-row mb-3">
        <div class="col-md-6 mb-2"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Search name, email, username...') }}"></div>
        <div class="col-md-4 mb-2">
            <select name="type" class="form-control">
                <option value="">{{ __('Everyone') }}</option>
                <option value="staff" @selected(request('type') === 'staff')>{{ __('Staff') }}</option>
                <option value="visitors" @selected(request('type') === 'visitors')>{{ __('Visitors') }}</option>
                <option value="super_admin" @selected(request('type') === 'super_admin')>{{ __('Super admins') }}</option>
            </select>
        </div>
        <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block"><i class="fe fe-search"></i> {{ __('Filter') }}</button></div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover text-md-nowrap mb-0">
            <thead><tr><th>{{ __('Person') }}</th><th>{{ __('Role') }}</th><th class="text-center">{{ __('Organizations') }}</th><th class="text-center">{{ __('Channels') }}</th><th class="text-center">{{ __('Following') }}</th><th>{{ __('Last seen') }}</th><th>{{ __('Joined') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($people as $p)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md brround bg-primary mx-2">{{ $p->initials }}</span>
                                <div><a href="{{ route('admin.crm.person', $p) }}" class="font-weight-bold">{{ $p->full_name }}</a>@if ($p->is_vip) <x-valex.badge variant="warning">VIP</x-valex.badge>@endif<div class="small text-muted">{{ $p->email }}</div></div>
                            </div>
                        </td>
                        <td>@forelse ($p->roles as $r)<x-valex.badge variant="primary">{{ str_replace('_', ' ', $r->name) }}</x-valex.badge> @empty<span class="text-muted">{{ __('Visitor') }}</span>@endforelse</td>
                        <td class="text-center">{{ $p->organization_memberships_count }}</td>
                        <td class="text-center">{{ $p->subscribed_channels_count }}</td>
                        <td class="text-center">{{ $p->followed_organizations_count }}</td>
                        <td class="text-muted">{{ $p->last_seen_at?->diffForHumans() ?? __('Never') }}</td>
                        <td class="text-muted">{{ $p->created_at->format('M d, Y') }}</td>
                        <td><a href="{{ route('admin.crm.person', $p) }}" class="btn btn-sm btn-outline-primary">{{ __('360') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">{{ __('No people match your filters') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center mt-3">{{ $people->links('pagination::bootstrap-4') }}</div>
</x-valex.card>
@endsection
