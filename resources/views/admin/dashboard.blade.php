@extends('layouts.valex')

@section('title', __('Super Admin Dashboard'))

@section('page-header')
    <x-valex.page-header
        :title="__('Super Admin Dashboard')"
        :subtitle="__('Welcome back,') . ' ' . auth()->user()->full_name . ' — ' . __('platform-wide overview')">
        <x-slot:actions>
            <x-valex.range-switch :days="$days" />
            <a href="{{ route('admin.organizations.create') }}" class="btn btn-primary mx-2"><i class="fe fe-plus"></i> {{ __('New Organization') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
{{-- KPIs --}}
<div class="row row-sm">
    @foreach ($stats['kpis'] as $kpi)
        <div class="col-sm-6 col-xl-4 col-xxl-2">
            <x-valex.kpi-card :kpi="$kpi" :days="$days" />
        </div>
    @endforeach
</div>

{{-- Growth + org status --}}
<div class="row row-sm">
    <div class="col-xl-8">
        <x-valex.card :title="__('Platform Growth')" :subtitle="__('Posts, new users and new organizations per day')">
            <div style="height: 300px;"><canvas id="growthChart"></canvas></div>
        </x-valex.card>
    </div>
    <div class="col-xl-4">
        <x-valex.card :title="__('Organizations by Status')" :subtitle="$stats['totalChannelsActive'] . ' ' . __('active channels')">
            <div style="height: 300px;"><canvas id="orgStatusChart"></canvas></div>
        </x-valex.card>
    </div>
</div>

{{-- Top orgs + QR --}}
<div class="row row-sm">
    <div class="col-xl-6">
        <x-valex.card :title="__('Top Organizations')" :subtitle="__('By number of posts')">
            <div style="height: 280px;"><canvas id="topOrgsChart"></canvas></div>
        </x-valex.card>
    </div>
    <div class="col-xl-4 col-md-7">
        <x-valex.card :title="__('QR Scans')" :subtitle="__('Scans per day')">
            <div style="height: 280px;"><canvas id="scansChart"></canvas></div>
        </x-valex.card>
    </div>
    <div class="col-xl-2 col-md-5">
        <x-valex.card :title="__('Devices')">
            <div style="height: 280px;"><canvas id="devicesChart"></canvas></div>
        </x-valex.card>
    </div>
</div>

{{-- Content mix + engagement + tickets --}}
<div class="row row-sm">
    <div class="col-xl-4 col-md-6">
        <x-valex.card :title="__('Content Mix')" :subtitle="__('Posts by type')">
            <div style="height: 240px;"><canvas id="postTypesChart"></canvas></div>
        </x-valex.card>
    </div>
    <div class="col-xl-4 col-md-6"><x-valex.engagement :engagement="$stats['engagement']" /></div>
    <div class="col-xl-4">
        <x-valex.card :title="__('Tickets by Status')">
            @php $ts = $stats['ticketsByStatus']; $tsTotal = array_sum($ts); @endphp
            @if ($tsTotal === 0)
                <p class="text-muted text-center my-4">{{ __('No tickets yet') }}</p>
            @else
                @foreach ($ts as $status => $count)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1"><span class="text-muted">{{ ucfirst(str_replace('_', ' ', $status)) }}</span><strong>{{ $count }}</strong></div>
                        <div class="progress" style="height: 8px;"><div class="progress-bar bg-{{ ['open' => 'primary', 'in_progress' => 'info', 'waiting' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'][$status] ?? 'secondary' }}" style="width: {{ round($count / $tsTotal * 100) }}%"></div></div>
                    </div>
                @endforeach
            @endif
        </x-valex.card>
    </div>
</div>

{{-- Tables --}}
<div class="row row-sm">
    <div class="col-xl-7">
        <x-valex.card :title="__('Newest Organizations')">
            <x-slot:actions><a href="{{ route('admin.organizations.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a></x-slot:actions>
            @if ($stats['newestOrganizations']->isEmpty())
                <p class="text-muted text-center mb-0">{{ __('No organizations yet') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover text-md-nowrap mb-0">
                        <thead><tr><th>{{ __('Organization') }}</th><th>{{ __('Status') }}</th><th>{{ __('Users') }}</th><th>{{ __('Channels') }}</th><th>{{ __('Posts') }}</th><th>{{ __('Created') }}</th></tr></thead>
                        <tbody>
                            @foreach ($stats['newestOrganizations'] as $org)
                                <tr>
                                    <td><a href="{{ route('admin.organizations.show', $org) }}" class="font-weight-bold">{{ $org->name }}</a><div class="small text-muted">{{ $org->city ?? __('No city set') }}</div></td>
                                    <td><x-valex.badge :variant="match($org->status) { 'active' => 'success', 'suspended' => 'warning', 'cancelled' => 'danger', default => 'secondary' }">{{ ucfirst($org->status) }}</x-valex.badge></td>
                                    <td>{{ $org->users_count }}</td>
                                    <td>{{ $org->channels_count }}</td>
                                    <td>{{ $org->posts_count }}</td>
                                    <td class="text-muted">{{ $org->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-valex.card>

        <x-valex.card :title="__('Open Tickets')">
            <x-slot:actions><a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a></x-slot:actions>
            @if ($stats['openTickets']->isEmpty())
                <p class="text-muted text-center mb-0">{{ __('No open tickets') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover text-md-nowrap mb-0">
                        <thead><tr><th>{{ __('Ticket') }}</th><th>{{ __('Organization') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Opened') }}</th></tr></thead>
                        <tbody>
                            @foreach ($stats['openTickets'] as $ticket)
                                <tr>
                                    <td><code>{{ $ticket->ticket_number }}</code> <a href="{{ route('tickets.show', $ticket) }}">{{ Str::limit($ticket->title, 40) }}</a></td>
                                    <td class="text-muted">{{ $ticket->organization->name ?? __('Unknown org') }}</td>
                                    <td><x-valex.badge :variant="match($ticket->priority) { 'urgent' => 'danger', 'high' => 'warning', 'medium' => 'info', default => 'secondary' }">{{ ucfirst($ticket->priority) }}</x-valex.badge></td>
                                    <td class="text-muted">{{ $ticket->opened_at?->format('M d, Y') ?? __('Recently') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-valex.card>
    </div>

    <div class="col-xl-5">
        <x-valex.card :title="__('Recent Activity')">
            @forelse ($stats['recentActivity'] as $log)
                <div class="d-flex align-items-start {{ ! $loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
                    <span class="avatar avatar-md brround bg-primary-transparent text-primary mx-2"><i class="fe fe-activity"></i></span>
                    <div>
                        <div class="small font-weight-bold">{{ $log->description ?: ucfirst(str_replace('_', ' ', $log->event)) }}</div>
                        <div class="small text-muted">{{ $log->user?->full_name ?? __('System') }} · {{ $log->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center mb-0">{{ __('Nothing yet') }}</p>
            @endforelse
        </x-valex.card>

        <x-valex.card :title="__('Quick Actions')">
            <div class="row row-xs">
                @foreach ([
                    ['admin.organizations.create', 'plus-circle', __('New Org')],
                    ['admin.organizations.index', 'briefcase', __('Organizations')],
                ] as [$routeName, $icon, $label])
                    <div class="col-6 mb-2"><a href="{{ route($routeName) }}" class="btn btn-outline-primary btn-block"><i class="fe fe-{{ $icon }}"></i> {{ $label }}</a></div>
                @endforeach
            </div>
        </x-valex.card>
    </div>
</div>
@endsection

@php $chartData = Arr::only($stats, ['growth', 'scans', 'orgStatus', 'topOrgs', 'devices', 'postTypes']); @endphp
@push('scripts')
<script src="{{ asset('assets/valex/plugins/chart.js/Chart.bundle.min.js') }}"></script>
<script src="{{ asset('assets/valex/js/ipage-charts.js') }}"></script>
<script>
    (function () {
        var s = @json($chartData);
        var none = @json(__('No data for this period'));
        IPChart.line('growthChart', s.growth, none);
        IPChart.doughnut('orgStatusChart', s.orgStatus.labels, s.orgStatus.data, none, ['#22c55e', '#f59e0b', '#ef4444']);
        IPChart.bar('topOrgsChart', s.topOrgs.labels, [
            { label: @json(__('Posts')), data: s.topOrgs.posts },
            { label: @json(__('Users')), data: s.topOrgs.users },
            { label: @json(__('Channels')), data: s.topOrgs.channels }
        ], none);
        IPChart.line('scansChart', s.scans, none);
        IPChart.doughnut('devicesChart', s.devices.labels, s.devices.data, none);
        IPChart.doughnut('postTypesChart', s.postTypes.labels, s.postTypes.data, none);
    })();
</script>
@endpush
