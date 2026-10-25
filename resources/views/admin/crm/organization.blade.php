@extends('layouts.valex')

@section('title', $organization->name . ' · 360')

@section('page-header')
    <x-valex.page-header :title="$organization->name" :subtitle="__('Organization 360')">
        <x-slot:actions>
            <a href="{{ route('admin.crm.index') }}" class="btn btn-outline-secondary mx-1"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
            <a href="{{ route('admin.organizations.show', $organization) }}" class="btn btn-primary mx-1"><i class="fe fe-settings"></i> {{ __('Manage') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $statusVariant = match ($organization->status) { 'active' => 'success', 'suspended' => 'warning', 'cancelled' => 'danger', default => 'secondary' };
    $ticketVariant = fn ($s) => match ($s) { 'open' => 'primary', 'in_progress' => 'info', 'waiting' => 'warning', 'resolved' => 'success', default => 'secondary' };
    $postVariant = fn ($s) => match ($s) { 'published' => 'success', 'pending_approval' => 'warning', 'rejected' => 'danger', 'scheduled' => 'info', default => 'secondary' };
@endphp

<x-valex.hero :title="$organization->name" :subtitle="collect([$organization->city, $organization->country])->filter()->implode(', ') ?: ($organization->email ?? '')">
    <div class="mt-2">
        <x-valex.badge :variant="$statusVariant">{{ ucfirst($organization->status) }}</x-valex.badge>
        <x-valex.health-badge :health="$health" />
        <span class="small mx-2">{{ __('Last activity') }}: {{ $lastActivity ? \Illuminate\Support\Carbon::parse($lastActivity)->diffForHumans() : __('Never') }}</span>
    </div>
</x-valex.hero>

<div class="row row-sm">
    @foreach ([
        [__('Members'), $stats['activeMembers'] . '/' . $stats['members'], 'users', 'primary'],
        [__('Followers'), $stats['followers'], 'heart', 'info'],
        [__('Channels'), $stats['channels'], 'message-circle', 'purple'],
        [__('Posts (30d)'), $stats['recentPosts'] . ' / ' . $stats['posts'], 'file-text', 'success'],
        [__('Open Tickets'), $stats['openTickets'] . ' / ' . $stats['allTickets'], 'tag', 'danger'],
        [__('QR Scans'), number_format($stats['qrScans']), 'grid', 'warning'],
    ] as [$label, $value, $icon, $variant])
        <div class="col-sm-6 col-xl-2"><x-valex.stat-card :title="$label" :value="$value" :icon="$icon" :variant="$variant" /></div>
    @endforeach
</div>

<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs" id="crmTabs">
            @foreach (['overview' => __('Overview'), 'members' => __('Members'), 'channels' => __('Channels'), 'posts' => __('Posts'), 'tickets' => __('Tickets'), 'qr' => __('QR Codes'), 'activity' => __('Activity')] as $key => $label)
                <li class="nav-item"><a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab" href="#tab-{{ $key }}">{{ $label }}</a></li>
            @endforeach
        </ul>

        <div class="tab-content pt-4">
            {{-- Overview --}}
            <div class="tab-pane active" id="tab-overview">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted text-uppercase mb-3">{{ __('Profile') }}</h6>
                        <dl class="row mb-0">
                            @foreach ([
                                __('Email') => $organization->email, __('Phone') => $organization->phone, __('Address') => $organization->address,
                                __('City') => $organization->city, __('Country') => $organization->country,
                                __('Channel limit') => $stats['channels'] . ' / ' . $organization->max_channels,
                                __('Brands') => $stats['brands'], __('Locations') => $stats['locations'],
                                __('Created') => $organization->created_at->format('M d, Y'),
                            ] as $k => $v)
                                <dt class="col-sm-4 text-muted font-weight-normal">{{ $k }}</dt><dd class="col-sm-8">{{ $v ?: '—' }}</dd>
                            @endforeach
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted text-uppercase mb-3">{{ __('Health breakdown') }}</h6>
                        @foreach ([
                            [__('Posted in last 30 days'), $stats['recentPosts'] > 0, 30],
                            [__('At least 2 active members'), $stats['activeMembers'] >= 2, 20],
                            [__('Has a channel'), $stats['channels'] >= 1, 20],
                            [__('Activity in last 14 days'), $lastActivity && \Illuminate\Support\Carbon::parse($lastActivity)->gte(now()->subDays(14)), 30],
                        ] as [$label, $ok, $pts])
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span><i class="fe fe-{{ $ok ? 'check-circle text-success' : 'x-circle text-danger' }}"></i> {{ $label }}</span>
                                <strong>+{{ $ok ? $pts : 0 }}<small class="text-muted"> / {{ $pts }}</small></strong>
                            </div>
                        @endforeach
                        <div class="d-flex justify-content-between pt-2"><strong>{{ __('Score') }}</strong><strong>{{ $health['score'] }} / 100</strong></div>
                    </div>
                </div>
            </div>

            {{-- Members --}}
            <div class="tab-pane" id="tab-members">
                <div class="table-responsive">
                    <table class="table table-hover text-md-nowrap mb-0">
                        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Role') }}</th><th>{{ __('Status') }}</th><th>{{ __('Job title') }}</th><th>{{ __('Last seen') }}</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($memberships as $m)
                                <tr>
                                    <td><a href="{{ route('admin.crm.person', $m->user) }}" class="font-weight-bold">{{ $m->user->full_name }}</a><div class="small text-muted">{{ $m->user->email }}</div></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $m->role)) }}</td>
                                    <td><x-valex.badge :variant="$m->status === 'active' ? 'success' : ($m->status === 'inactive' ? 'secondary' : 'warning')">{{ ucfirst($m->status) }}</x-valex.badge></td>
                                    <td>{{ $m->job_title ?: '—' }}</td>
                                    <td class="text-muted">{{ $m->user->last_seen_at?->diffForHumans() ?? __('Never') }}</td>
                                    <td><a href="{{ route('admin.crm.person', $m->user) }}" class="btn btn-sm btn-outline-primary">{{ __('360') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No members yet') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Channels --}}
            <div class="tab-pane" id="tab-channels">
                <div class="table-responsive">
                    <table class="table table-hover text-md-nowrap mb-0">
                        <thead><tr><th>{{ __('Channel') }}</th><th>{{ __('Type') }}</th><th>{{ __('Status') }}</th><th class="text-center">{{ __('Subscribers') }}</th><th class="text-center">{{ __('Posts') }}</th><th>{{ __('Created') }}</th></tr></thead>
                        <tbody>
                            @forelse ($channels as $c)
                                <tr>
                                    <td class="font-weight-bold">{{ $c->name }}</td>
                                    <td><x-valex.badge :variant="$c->type === 'public' ? 'success' : 'warning'">{{ $c->type }}</x-valex.badge></td>
                                    <td>{{ ucfirst($c->status) }}</td>
                                    <td class="text-center">{{ $c->users_count }}</td>
                                    <td class="text-center">{{ $c->posts_count }}</td>
                                    <td class="text-muted">{{ $c->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No channels yet') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Posts --}}
            <div class="tab-pane" id="tab-posts">
                <div class="table-responsive">
                    <table class="table table-hover text-md-nowrap mb-0">
                        <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Channel') }}</th><th>{{ __('Author') }}</th><th>{{ __('Status') }}</th><th>{{ __('Created') }}</th></tr></thead>
                        <tbody>
                            @forelse ($posts as $p)
                                <tr>
                                    <td>{{ Str::limit($p->title ?? $p->body, 50) }}</td>
                                    <td>{{ $p->channel?->name ?? '—' }}</td>
                                    <td>{{ $p->author?->full_name ?? '—' }}</td>
                                    <td><x-valex.badge :variant="$postVariant($p->status)">{{ str_replace('_', ' ', $p->status) }}</x-valex.badge></td>
                                    <td class="text-muted">{{ $p->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No posts yet') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tickets --}}
            <div class="tab-pane" id="tab-tickets">
                <div class="table-responsive">
                    <table class="table table-hover text-md-nowrap mb-0">
                        <thead><tr><th>{{ __('Ticket') }}</th><th>{{ __('Status') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Assigned to') }}</th><th>{{ __('Opened') }}</th></tr></thead>
                        <tbody>
                            @forelse ($tickets as $t)
                                <tr>
                                    <td><code>{{ $t->ticket_number }}</code> {{ Str::limit($t->title, 40) }}</td>
                                    <td><x-valex.badge :variant="$ticketVariant($t->status)">{{ str_replace('_', ' ', $t->status) }}</x-valex.badge></td>
                                    <td>{{ ucfirst($t->priority) }}</td>
                                    <td>{{ $t->assignee?->full_name ?? __('Unassigned') }}</td>
                                    <td class="text-muted">{{ $t->opened_at?->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No tickets') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- QR --}}
            <div class="tab-pane" id="tab-qr">
                <div class="table-responsive">
                    <table class="table table-hover text-md-nowrap mb-0">
                        <thead><tr><th>{{ __('Label') }}</th><th>{{ __('Code') }}</th><th class="text-center">{{ __('Scans') }}</th><th>{{ __('Status') }}</th><th>{{ __('Created') }}</th></tr></thead>
                        <tbody>
                            @forelse ($qrCodes as $q)
                                <tr>
                                    <td>{{ $q->label ?: '—' }}</td>
                                    <td><code>{{ $q->code }}</code></td>
                                    <td class="text-center">{{ $q->scan_count }}</td>
                                    <td><x-valex.badge :variant="$q->is_active ? 'success' : 'secondary'">{{ $q->is_active ? __('Active') : __('Inactive') }}</x-valex.badge></td>
                                    <td class="text-muted">{{ $q->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No QR codes') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Activity --}}
            <div class="tab-pane" id="tab-activity">
                @include('crm._activity-table', ['logs' => $activity])
                <div class="text-center mt-3"><a href="{{ route('admin.crm.activity', ['organization' => $organization->id]) }}">{{ __('Open full activity log') }} →</a></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    jQuery(function ($) {
        var hash = location.hash;
        if (hash && $('#crmTabs a[href="' + hash + '"]').length) $('#crmTabs a[href="' + hash + '"]').tab('show');
        $('#crmTabs a').on('shown.bs.tab', function (e) { history.replaceState(null, '', $(e.target).attr('href')); });
    });
</script>
@endpush
