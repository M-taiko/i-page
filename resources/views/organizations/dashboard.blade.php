@extends('layouts.valex')

@section('title', $organization->name)

@section('page-header')
    <x-valex.page-header :title="$organization->name" :subtitle="__('Dashboard')">
        <x-slot:actions>
            <x-valex.range-switch :days="$days" />
            <a href="{{ route('organizations.settings', $organization) }}" class="btn btn-primary mx-2"><i class="fe fe-settings"></i> {{ __('Settings') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $postVariant = fn ($s) => match ($s) { 'draft' => 'secondary', 'published' => 'success', 'pending_approval' => 'warning', 'rejected' => 'danger', default => 'info' };
    $priorityVariant = fn ($p) => match ($p) { 'urgent' => 'danger', 'high' => 'warning', 'medium' => 'info', default => 'secondary' };
@endphp

<div class="row row-sm">
    @foreach ($stats['kpis'] as $kpi)
        <div class="col-sm-6 col-xl-4 col-xxl-2">
            <x-valex.kpi-card :kpi="$kpi" :days="($kpi['current'] || $kpi['previous']) ? $days : null" />
        </div>
    @endforeach
</div>

<div class="row row-sm">
    <div class="col-xl-8">
        <x-valex.card :title="__('Activity Trend')" :subtitle="__('Posts and tickets per day')">
            <div style="height: 300px;"><canvas id="growthChart"></canvas></div>
        </x-valex.card>
    </div>
    <div class="col-xl-4">
        <x-valex.card :title="__('Content Mix')" :subtitle="__('Posts by type')">
            <div style="height: 300px;"><canvas id="postTypesChart"></canvas></div>
        </x-valex.card>
    </div>
</div>

<div class="row row-sm">
    <div class="col-xl-4 col-md-6"><x-valex.engagement :engagement="$stats['engagement']" /></div>
    <div class="col-xl-5 col-md-6">
        <x-valex.card :title="__('QR Scans')" :subtitle="__('Scans per day')">
            <div style="height: 240px;"><canvas id="scansChart"></canvas></div>
        </x-valex.card>
    </div>
    <div class="col-xl-3">
        <x-valex.card :title="__('Devices')">
            <div style="height: 240px;"><canvas id="devicesChart"></canvas></div>
        </x-valex.card>
    </div>
</div>

<div class="row row-sm">
    <div class="col-xl-4 col-md-6">
        <x-valex.card :title="__('Top Channels')">
            <x-slot:actions><a href="{{ route('tenant.channels.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a></x-slot:actions>
            <ul class="list-group list-group-flush">
                @forelse ($stats['topChannels'] as $channel)
                    <li class="list-group-item d-flex align-items-center px-0">
                        <span class="avatar avatar-md brround bg-primary-transparent text-primary mx-2"><i class="fe fe-message-circle"></i></span>
                        <div class="flex-grow-1">
                            <a href="{{ route('tenant.channels.show', $channel) }}" class="font-weight-bold">{{ $channel->name }}</a>
                            <div class="small text-muted">{{ $channel->users_count }} {{ __('members') }}</div>
                        </div>
                        <x-valex.badge variant="primary">{{ $channel->posts_count }} {{ __('posts') }}</x-valex.badge>
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted">{{ __('No channels yet') }}</li>
                @endforelse
            </ul>
        </x-valex.card>
    </div>

    <div class="col-xl-4 col-md-6">
        <x-valex.card :title="__('Recent Posts')">
            <x-slot:actions><a href="{{ route('posts.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a></x-slot:actions>
            <ul class="list-group list-group-flush">
                @forelse ($stats['recentPosts'] as $post)
                    <li class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('posts.show', $post) }}">{{ Str::limit($post->title ?? $post->body, 36) }}</a>
                            <x-valex.badge :variant="$postVariant($post->status)">{{ str_replace('_', ' ', ucfirst($post->status)) }}</x-valex.badge>
                        </div>
                        <small class="text-muted">{{ $post->author?->full_name }} · {{ $post->created_at->diffForHumans() }}</small>
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted">{{ __('No posts yet') }}</li>
                @endforelse
            </ul>
        </x-valex.card>
    </div>

    <div class="col-xl-4">
        <x-valex.card :title="__('Open Tickets')">
            <x-slot:actions><a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a></x-slot:actions>
            <ul class="list-group list-group-flush">
                @forelse ($stats['openTickets'] as $ticket)
                    <li class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('tickets.show', $ticket) }}"><code>{{ $ticket->ticket_number }}</code> {{ Str::limit($ticket->title, 26) }}</a>
                            <x-valex.badge :variant="$priorityVariant($ticket->priority)">{{ ucfirst($ticket->priority) }}</x-valex.badge>
                        </div>
                        <small class="text-muted">{{ $ticket->opened_at?->format('M d, Y') ?? __('Recently') }}</small>
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted">{{ __('No open tickets') }}</li>
                @endforelse
            </ul>
        </x-valex.card>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-7">
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
    </div>
    <div class="col-lg-5">
        <x-valex.card :title="__('Quick Actions')">
            <a href="{{ route('posts.create') }}" class="btn btn-outline-primary btn-block text-left mb-2"><i class="fe fe-edit-3"></i> {{ __('Create Post') }}</a>
            <a href="{{ route('tickets.create') }}" class="btn btn-outline-primary btn-block text-left mb-2"><i class="fe fe-plus-circle"></i> {{ __('Create Ticket') }}</a>
            <a href="{{ route('audience-segments.create') }}" class="btn btn-outline-primary btn-block text-left mb-2"><i class="fe fe-share-2"></i> {{ __('Create Audience') }}</a>
            <a href="{{ route('organizations.settings', $organization) }}" class="btn btn-outline-primary btn-block text-left"><i class="fe fe-users"></i> {{ __('Manage Team') }}</a>
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
        IPChart.doughnut('postTypesChart', s.postTypes.labels, s.postTypes.data, none);
        IPChart.line('scansChart', s.scans, none);
        IPChart.doughnut('devicesChart', s.devices.labels, s.devices.data, none);
    })();
</script>
@endpush
