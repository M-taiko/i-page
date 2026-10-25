@extends('layouts.valex')

@section('title', $organization->name)

@section('page-header')
    <x-valex.page-header :title="$organization->name" :subtitle="__('Organization')">
        <x-slot:actions>
            <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary mx-1"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
            <a href="{{ route('admin.organizations.edit', $organization->id) }}" class="btn btn-warning mx-1"><i class="fe fe-edit"></i> {{ __('Edit Organization') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $status = $organization->status ?? 'active';
    $memberCount = $organization->users()->count();
@endphp

<div class="card bg-primary-gradient text-white">
    <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h3 class="text-white mb-1">{{ $organization->name }}</h3>
            <p class="mb-2 op-8">{{ $organization->description ?? __('No description provided') }}</p>
            <x-valex.badge :variant="$organization->is_active ? 'success' : 'danger'">{{ $organization->is_active ? __('Active') : __('Inactive') }}</x-valex.badge>
            @if ($organization->city || $organization->country)
                <x-valex.badge variant="light"><i class="fe fe-map-pin"></i> {{ collect([$organization->city, $organization->country])->filter()->implode(', ') }}</x-valex.badge>
            @endif
        </div>
        <span class="avatar avatar-xxl brround bg-white-transparent tx-30">{{ mb_substr($organization->name, 0, 1) }}</span>
    </div>
</div>

<div class="row row-sm">
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Max Channels')" :value="$organization->max_channels" icon="layers" variant="primary" /></div>
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Active Channels')" :value="$organization->channels()->count()" icon="message-circle" variant="success" /></div>
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Team Members')" :value="$memberCount" icon="users" variant="info" /></div>
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Total Posts')" :value="$organization->posts()->count()" icon="file-text" variant="warning" /></div>
</div>

<div class="row">
    <div class="col-lg-8">
        <x-valex.card :title="__('Organization Information')">
            <div class="row">
                <div class="col-md-6 mb-3"><div class="small text-muted text-uppercase">{{ __('Organization Name') }}</div><strong>{{ $organization->name }}</strong></div>
                <div class="col-md-6 mb-3">
                    <div class="small text-muted text-uppercase">{{ __('Status') }}</div>
                    <x-valex.badge :variant="$organization->is_active ? 'success' : 'danger'">{{ $organization->is_active ? __('Active') : __('Inactive') }}</x-valex.badge>
                </div>
                <div class="col-md-6 mb-3"><div class="small text-muted text-uppercase">{{ __('City') }}</div>{{ $organization->city ?? __('Not specified') }}</div>
                <div class="col-md-6 mb-3"><div class="small text-muted text-uppercase">{{ __('Country') }}</div>{{ $organization->country ?? __('Not specified') }}</div>
            </div>
            <hr>
            <div class="small text-muted text-uppercase">{{ __('Description') }}</div>
            <p class="mb-0">{{ $organization->description ?? __('No description provided') }}</p>
        </x-valex.card>

        <x-valex.card :title="__('Settings')">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="small text-muted text-uppercase">{{ __('Max Channels Allowed') }}</div>
                <x-valex.badge variant="primary">{{ $organization->max_channels }}</x-valex.badge>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ __('Organization Status') }}</strong>
                    <p class="small text-muted mb-0">
                        {{ $organization->is_active
                            ? __('Organization is active. Team members can access and use all features.')
                            : __('Organization is inactive. Team members cannot access the platform.') }}
                    </p>
                </div>
                <form id="status-form" action="{{ route('admin.organizations.update', $organization->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="is_active" value="{{ $organization->is_active ? 0 : 1 }}">
                    <input type="hidden" name="name" value="{{ $organization->name }}">
                    <input type="hidden" name="description" value="{{ $organization->description }}">
                    <input type="hidden" name="city" value="{{ $organization->city }}">
                    <input type="hidden" name="country" value="{{ $organization->country }}">
                    <input type="hidden" name="max_channels" value="{{ $organization->max_channels }}">
                    <button type="submit" class="btn btn-sm {{ $organization->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                        {{ $organization->is_active ? __('Deactivate') : __('Activate') }}
                    </button>
                </form>
            </div>
            <hr>
            <div class="small text-muted text-uppercase">{{ __('Created') }}</div>
            {{ $organization->created_at->format('d M Y, H:i') }}
        </x-valex.card>

        <x-valex.card :title="__('Brands & Channels')">
            <x-slot:actions>
                <a href="{{ route('admin.organizations.brands.create', $organization) }}" class="btn btn-sm btn-outline-primary mx-1"><i class="fe fe-plus"></i> {{ __('Brand') }}</a>
                <a href="{{ route('admin.organizations.channels.create', $organization) }}" class="btn btn-sm btn-primary"><i class="fe fe-plus"></i> {{ __('Channel') }}</a>
            </x-slot:actions>

            @forelse ($organization->brands as $brand)
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-1">
                        <i class="fe fe-bookmark text-purple mx-2"></i>
                        <strong>{{ $brand->name }}</strong>
                        <a href="{{ route('admin.organizations.brands.edit', [$organization, $brand]) }}" class="small mx-2">{{ __('edit') }}</a>
                    </div>
                    <ul class="list-unstyled mb-0 mx-4">
                        @forelse ($brand->channels as $channel)
                            <li class="d-flex align-items-center small py-1">
                                <i class="fe fe-{{ $channel->type === 'public' ? 'globe' : 'lock' }} text-muted mx-2"></i>
                                {{ $channel->name }} <span class="text-muted mx-1">({{ $channel->type }})</span>
                                <a href="{{ route('admin.organizations.channels.edit', [$organization, $channel]) }}" class="ml-auto">{{ __('edit') }}</a>
                            </li>
                        @empty
                            <li class="small text-muted">{{ __('No channels yet') }}</li>
                        @endforelse
                    </ul>
                </div>
            @empty
                <p class="text-muted mb-0">{{ __('No brands yet. Create one to start adding channels.') }}</p>
            @endforelse
        </x-valex.card>
    </div>

    <div class="col-lg-4">
        <x-valex.card :title="__('Team Members')" :subtitle="$memberCount">
            @if ($memberCount > 0)
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Role') }}</th></tr></thead>
                        <tbody>
                            @foreach ($organization->users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm brround bg-primary mx-2">{{ $user->initials }}</span>
                                            <div><div>{{ $user->full_name }}</div><small class="text-muted">{{ $user->email }}</small></div>
                                        </div>
                                    </td>
                                    <td><x-valex.badge variant="primary">{{ $user->pivot->role }}</x-valex.badge></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center mb-0">{{ __('No team members') }}</p>
            @endif
        </x-valex.card>

        <x-valex.card :title="__('Actions')">
            <div class="small text-muted text-uppercase mb-2">{{ __('Subscription') }}: <strong>{{ ucfirst($status) }}</strong></div>

            @if ($status === 'active')
                <form action="{{ route('admin.organizations.suspend', $organization) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning btn-block"><i class="fe fe-pause-circle"></i> {{ __('Suspend') }}</button>
                </form>
            @else
                <form action="{{ route('admin.organizations.activate', $organization) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-success btn-block"><i class="fe fe-play-circle"></i> {{ __('Reactivate') }}</button>
                </form>
            @endif

            @if ($status !== 'cancelled')
                <form action="{{ route('admin.organizations.cancel', $organization) }}" method="POST" class="mb-2"
                      onsubmit="return confirm('{{ __('Cancel this organization\'s subscription?') }}')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-block"><i class="fe fe-x-octagon"></i> {{ __('Cancel Subscription') }}</button>
                </form>
            @endif

            <hr>
            <form action="{{ route('admin.organizations.destroy', $organization->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-block"
                        onclick="return confirm('{{ __('Are you sure you want to delete this organization? This action cannot be undone.') }}')">
                    <i class="fe fe-trash-2"></i> {{ __('Delete Organization') }}
                </button>
            </form>
        </x-valex.card>
    </div>
</div>
@endsection
