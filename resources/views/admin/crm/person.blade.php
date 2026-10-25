@extends('layouts.valex')

@section('title', $user->full_name)

@section('page-header')
    <x-valex.page-header :title="$user->full_name" :subtitle="__('Person 360')">
        <x-slot:actions>
            <a href="{{ route('admin.crm.people') }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                @if ($user->avatar_path)
                    <img src="{{ Storage::url($user->avatar_path) }}" class="avatar avatar-xxl brround mb-3" alt="">
                @else
                    <span class="avatar avatar-xxl brround bg-primary mb-3">{{ $user->initials }}</span>
                @endif
                <h5 class="mb-0">{{ $user->full_name }}</h5>
                <div class="text-muted small mb-2">{{ $user->ipage_id }} @if ($user->username)· &#64;{{ $user->username }}@endif</div>
                @forelse ($user->roles as $r)<x-valex.badge variant="primary">{{ str_replace('_', ' ', $r->name) }}</x-valex.badge>@empty<x-valex.badge variant="secondary">{{ __('Visitor') }}</x-valex.badge>@endforelse
                @if ($user->is_vip)<x-valex.badge variant="warning">VIP</x-valex.badge>@endif
            </div>
            <ul class="list-group list-group-flush small">
                @foreach ([
                    __('Email') => $user->email . ($user->email_verified_at ? ' ✓' : ''), __('Mobile') => $user->mobile, __('Gender') => $user->gender ? ucfirst($user->gender) : null,
                    __('Nationality') => $user->nationality, __('City') => $user->city, __('Language') => strtoupper($user->language ?? ''),
                    __('Profile level') => $user->profile_level, __('Last seen') => $user->last_seen_at?->diffForHumans(), __('Joined') => $user->created_at->format('M d, Y'),
                ] as $k => $v)
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">{{ $k }}</span><span class="text-right">{{ $v ?: '—' }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row row-sm">
            @foreach ([[__('Posts'), $stats['posts'], 'file-text', 'primary'], [__('Received'), $stats['received'], 'inbox', 'info'], [__('Read'), $stats['read'], 'eye', 'success'], [__('QR scans'), $stats['qrScans'], 'grid', 'warning'], [__('Reactions'), $stats['reactions'], 'heart', 'danger'], [__('Comments'), $stats['comments'], 'message-square', 'purple']] as [$l, $v, $i, $c])
                <div class="col-6 col-md-4"><x-valex.stat-card :title="$l" :value="$v" :icon="$i" :variant="$c" /></div>
            @endforeach
        </div>

        <x-valex.card :title="__('Organizations') . ' (' . $memberships->count() . ')'">
            <ul class="list-group list-group-flush">
                @forelse ($memberships as $m)
                    <li class="list-group-item d-flex align-items-center px-0">
                        <div class="flex-grow-1">
                            @if ($m->organization)<a href="{{ route('admin.crm.organization', $m->organization) }}" class="font-weight-bold">{{ $m->organization->name }}</a>@else <span class="text-muted">{{ __('Deleted organization') }}</span>@endif
                            <div class="small text-muted">{{ $m->job_title ?: '—' }} · {{ __('since') }} {{ $m->created_at->format('M Y') }}</div>
                        </div>
                        <x-valex.badge variant="primary" class="mx-1">{{ ucfirst(str_replace('_', ' ', $m->role)) }}</x-valex.badge>
                        <x-valex.badge :variant="$m->status === 'active' ? 'success' : 'warning'">{{ ucfirst($m->status) }}</x-valex.badge>
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted">{{ __('Not a member of any organization') }}</li>
                @endforelse
            </ul>
        </x-valex.card>

        <div class="row">
            <div class="col-md-6">
                <x-valex.card :title="__('Subscribed channels') . ' (' . $channels->count() . ')'">
                    <ul class="list-unstyled mb-0">
                        @forelse ($channels as $c)
                            <li class="d-flex justify-content-between py-1"><span>{{ $c->name }} <small class="text-muted">· {{ $c->organization?->name }}</small></span><small class="text-muted">{{ $c->pivot->role }}</small></li>
                        @empty
                            <li class="text-muted">{{ __('None') }}</li>
                        @endforelse
                    </ul>
                </x-valex.card>
            </div>
            <div class="col-md-6">
                <x-valex.card :title="__('Following') . ' (' . $followed->count() . ')'">
                    <ul class="list-unstyled mb-0">
                        @forelse ($followed as $o)
                            <li class="py-1"><a href="{{ route('admin.crm.organization', $o) }}">{{ $o->name }}</a></li>
                        @empty
                            <li class="text-muted">{{ __('None') }}</li>
                        @endforelse
                    </ul>
                </x-valex.card>
            </div>
        </div>

        <x-valex.card :title="__('Tickets') . ' (' . $tickets->count() . ')'">
            <ul class="list-unstyled mb-0">
                @forelse ($tickets as $t)
                    <li class="d-flex justify-content-between py-1"><span><code>{{ $t->ticket_number }}</code> {{ Str::limit($t->title, 40) }} <small class="text-muted">· {{ $t->organization?->name }}</small></span><x-valex.badge variant="secondary">{{ str_replace('_', ' ', $t->status) }}</x-valex.badge></li>
                @empty
                    <li class="text-muted">{{ __('None') }}</li>
                @endforelse
            </ul>
        </x-valex.card>
    </div>
</div>

<x-valex.card :title="__('Recent activity by this person')">
    @include('crm._activity-table', ['logs' => $activity, 'showOrganization' => true])
</x-valex.card>
@endsection
