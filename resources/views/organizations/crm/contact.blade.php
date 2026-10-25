@extends('layouts.valex')

@section('title', $user->full_name)

@section('page-header')
    <x-valex.page-header :title="$user->full_name" :subtitle="__('Contact 360') . ' · ' . $organization->name">
        <x-slot:actions>
            <a href="{{ route('organizations.crm.contacts') }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $engagement = $stats['received'] > 0 ? round($stats['read'] / $stats['received'] * 100) : 0;
    $ticketVariant = fn ($s) => match ($s) { 'open' => 'primary', 'in_progress' => 'info', 'waiting' => 'warning', 'resolved' => 'success', default => 'secondary' };
@endphp

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                @if ($user->avatar_path)
                    <img src="{{ Storage::url($user->avatar_path) }}" class="avatar avatar-xxl brround mb-3" alt="">
                @else
                    <span class="avatar avatar-xxl brround bg-primary mb-3">{{ $user->initials }}</span>
                @endif
                <h5 class="mb-1">{{ $user->full_name }}</h5>
                @if ($membership)<x-valex.badge variant="primary">{{ ucfirst(str_replace('_', ' ', $membership->role)) }}</x-valex.badge><x-valex.badge :variant="$membership->status === 'active' ? 'success' : 'warning'">{{ $membership->status }}</x-valex.badge>@endif
                @if ($isFollower)<x-valex.badge variant="info">{{ __('Follower') }}</x-valex.badge>@endif
                @if ($user->is_vip)<x-valex.badge variant="warning">VIP</x-valex.badge>@endif
            </div>
            <ul class="list-group list-group-flush small">
                @foreach ([
                    __('Email') => $user->email, __('Mobile') => $user->mobile,
                    __('Job title') => $membership?->job_title, __('Employee ID') => $membership?->employee_id,
                    __('Department') => $membership?->department?->name, __('Manager') => $membership?->manager?->full_name,
                    __('Business verified') => $membership ? ($membership->isBusinessVerified() ? __('Yes') : __('No')) : null,
                    __('Last seen') => $user->last_seen_at?->diffForHumans(), __('Joined platform') => $user->created_at->format('M d, Y'),
                ] as $k => $v)
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">{{ $k }}</span><span class="text-right">{{ $v ?: '—' }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row row-sm">
            @foreach ([[__('Received'), $stats['received'], 'inbox', 'primary'], [__('Viewed'), $stats['viewed'], 'eye', 'info'], [__('Read'), $stats['read'], 'check', 'success'], [__('Acknowledged'), $stats['acknowledged'], 'check-circle', 'warning'], [__('Tickets'), $stats['tickets'], 'tag', 'danger'], [__('QR scans'), $stats['qrScans'], 'grid', 'purple']] as [$l, $v, $i, $c])
                <div class="col-6 col-md-4"><x-valex.stat-card :title="$l" :value="$v" :icon="$i" :variant="$c" /></div>
            @endforeach
        </div>

        <x-valex.card :title="__('Engagement')" :subtitle="__('Share of received posts that were read')">
            <div class="d-flex justify-content-between mb-1"><span class="text-muted">{{ __('Read rate') }}</span><strong>{{ $engagement }}%</strong></div>
            <div class="progress" style="height: 8px;"><div class="progress-bar bg-success" style="width: {{ $engagement }}%"></div></div>
        </x-valex.card>

        <div class="row">
            <div class="col-md-6">
                <x-valex.card :title="__('Channels') . ' (' . $channels->count() . ')'">
                    <ul class="list-unstyled mb-0">
                        @forelse ($channels as $c)
                            <li class="d-flex justify-content-between py-1"><span>{{ $c->name }}</span><small class="text-muted">{{ $c->pivot->role }} · {{ $c->pivot->status }}</small></li>
                        @empty
                            <li class="text-muted">{{ __('Not subscribed to any channel') }}</li>
                        @endforelse
                    </ul>
                </x-valex.card>
            </div>
            <div class="col-md-6">
                <x-valex.card :title="__('Tickets') . ' (' . $tickets->count() . ')'">
                    <ul class="list-unstyled mb-0">
                        @forelse ($tickets as $t)
                            <li class="d-flex justify-content-between py-1"><a href="{{ route('tickets.show', $t) }}"><code>{{ $t->ticket_number }}</code> {{ Str::limit($t->title, 24) }}</a><x-valex.badge :variant="$ticketVariant($t->status)">{{ str_replace('_', ' ', $t->status) }}</x-valex.badge></li>
                        @empty
                            <li class="text-muted">{{ __('No tickets') }}</li>
                        @endforelse
                    </ul>
                </x-valex.card>
            </div>
        </div>
    </div>
</div>

<x-valex.card :title="__('Activity timeline')">
    @include('crm._activity-table', ['logs' => $activity])
</x-valex.card>
@endsection
