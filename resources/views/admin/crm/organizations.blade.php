@extends('layouts.valex')

@section('title', __('Organizations 360'))

@section('page-header')
    <x-valex.page-header :title="__('Platform CRM')" :subtitle="__('Organizations 360')">
        <x-slot:actions>
            <a href="{{ route('admin.crm.people') }}" class="btn btn-outline-primary mx-1"><i class="fe fe-users"></i> {{ __('People') }}</a>
            <a href="{{ route('admin.crm.activity') }}" class="btn btn-outline-primary mx-1"><i class="fe fe-activity"></i> {{ __('Activity Log') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row row-sm">
    <div class="col-sm-6 col-xl-2"><x-valex.stat-card :title="__('Organizations')" :value="$summary['organizations']" icon="briefcase" variant="primary" /></div>
    <div class="col-sm-6 col-xl-2"><x-valex.stat-card :title="__('Active')" :value="$summary['active']" icon="check-circle" variant="success" /></div>
    <div class="col-sm-6 col-xl-2"><x-valex.stat-card :title="__('Accounts')" :value="$summary['users']" icon="users" variant="info" /></div>
    <div class="col-sm-6 col-xl-2"><x-valex.stat-card :title="__('Visitors')" :value="$summary['visitors']" icon="user" variant="purple" /></div>
    <div class="col-sm-6 col-xl-2"><x-valex.stat-card :title="__('Open Tickets')" :value="$summary['openTickets']" icon="tag" variant="warning" /></div>
    <div class="col-sm-6 col-xl-2"><x-valex.stat-card :title="__('Events (24h)')" :value="$summary['activity24h']" icon="activity" variant="danger" /></div>
</div>

<x-valex.card>
    <form method="GET" class="form-row align-items-center mb-3">
        <div class="col-md-4 mb-2"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Search name, email, city...') }}"></div>
        <div class="col-md-2 mb-2">
            <select name="status" class="form-control">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (['active' => __('Active'), 'suspended' => __('Suspended'), 'cancelled' => __('Cancelled')] as $v => $l)
                    <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <select name="health" class="form-control">
                <option value="">{{ __('Any health') }}</option>
                @foreach (['healthy' => __('Healthy'), 'attention' => __('Attention'), 'risk' => __('At risk')] as $v => $l)
                    <option value="{{ $v }}" @selected(request('health') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <select name="sort" class="form-control">
                <option value="">{{ __('Newest first') }}</option>
                <option value="name" @selected(request('sort') === 'name')>{{ __('Name A-Z') }}</option>
            </select>
        </div>
        <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block"><i class="fe fe-search"></i> {{ __('Filter') }}</button></div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover text-md-nowrap mb-0">
            <thead>
                <tr>
                    <th>{{ __('Organization') }}</th><th>{{ __('Status') }}</th><th>{{ __('Health') }}</th>
                    <th class="text-center">{{ __('Members') }}</th><th class="text-center">{{ __('Channels') }}</th>
                    <th class="text-center">{{ __('Posts') }} <small class="text-muted">(30d)</small></th>
                    <th class="text-center">{{ __('Open Tickets') }}</th><th class="text-center">{{ __('QR Scans') }}</th>
                    <th>{{ __('Last activity') }}</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($organizations as $org)
                    <tr>
                        <td>
                            <a href="{{ route('admin.crm.organization', $org) }}" class="font-weight-bold">{{ $org->name }}</a>
                            <div class="small text-muted">{{ collect([$org->city, $org->country])->filter()->implode(', ') ?: $org->email }}</div>
                        </td>
                        <td><x-valex.badge :variant="match($org->status) { 'active' => 'success', 'suspended' => 'warning', 'cancelled' => 'danger', default => 'secondary' }">{{ ucfirst($org->status) }}</x-valex.badge></td>
                        <td><x-valex.health-badge :health="$org->health" /></td>
                        <td class="text-center">{{ $org->active_members_count }}</td>
                        <td class="text-center">{{ $org->channels_count }}</td>
                        <td class="text-center">{{ $org->recent_posts_count }} <small class="text-muted">/ {{ $org->posts_count }}</small></td>
                        <td class="text-center">{{ $org->open_tickets_count }}</td>
                        <td class="text-center">{{ number_format($org->qr_scans_total) }}</td>
                        <td class="text-muted">{{ $org->last_activity_at ? \Illuminate\Support\Carbon::parse($org->last_activity_at)->diffForHumans() : __('Never') }}</td>
                        <td><a href="{{ route('admin.crm.organization', $org) }}" class="btn btn-sm btn-outline-primary">{{ __('360 view') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">{{ __('No organizations match your filters') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center mt-3">{{ $organizations->links('pagination::bootstrap-4') }}</div>
</x-valex.card>
@endsection
