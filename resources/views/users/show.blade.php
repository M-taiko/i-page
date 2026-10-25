@extends('layouts.valex')

@section('title', $user->full_name)

@section('page-header')
    <x-valex.page-header :title="$user->full_name" :subtitle="$user->ipage_id">
        <x-slot:actions>
            <a href="{{ route('dashboard.users.index', $organization) }}" class="btn btn-outline-secondary mx-1"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
            <a href="{{ route('dashboard.users.edit', [$organization, $user]) }}" class="btn btn-warning mx-1"><i class="fe fe-edit"></i> {{ __('Edit') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php $role = $user->roles()->first(); @endphp

<div class="row">
    <div class="col-lg-8">
        <x-valex.card :title="__('Profile Information')">
            <div class="row">
                <div class="col-md-6 mb-3"><div class="small text-muted text-uppercase">{{ __('First Name') }}</div>{{ $user->first_name }}</div>
                <div class="col-md-6 mb-3"><div class="small text-muted text-uppercase">{{ __('Last Name') }}</div>{{ $user->last_name }}</div>
                <div class="col-md-6 mb-3"><div class="small text-muted text-uppercase">{{ __('Email') }}</div>{{ $user->email }}</div>
                <div class="col-md-6 mb-3"><div class="small text-muted text-uppercase">{{ __('Mobile') }}</div>{{ $user->mobile ?? '—' }}</div>
                <div class="col-md-6 mb-3"><div class="small text-muted text-uppercase">{{ __('Gender') }}</div>{{ $user->gender ? ucfirst($user->gender) : '—' }}</div>
                <div class="col-md-6 mb-3"><div class="small text-muted text-uppercase">{{ __('Nationality') }}</div>{{ $user->nationality ?? '—' }}</div>
                <div class="col-md-6">
                    <div class="small text-muted text-uppercase">{{ __('Status') }}</div>
                    <x-valex.badge :variant="$user->email_verified_at ? 'success' : 'danger'">{{ $user->email_verified_at ? __('Verified') : __('Unverified') }}</x-valex.badge>
                </div>
                <div class="col-md-6"><div class="small text-muted text-uppercase">{{ __('Member Since') }}</div>{{ $user->created_at->format('M d, Y') }}</div>
            </div>
        </x-valex.card>

        <x-valex.card :title="__('Role & Permissions')">
            <div class="small text-muted text-uppercase">{{ __('Role') }}</div>
            @if ($role)
                <x-valex.badge variant="primary">{{ $role->name }}</x-valex.badge>
                <div class="small text-muted text-uppercase mt-3 mb-1">{{ __('Permissions') }}</div>
                @forelse ($role->permissions as $permission)
                    <span class="badge badge-light border mb-1">{{ $permission->name }}</span>
                @empty
                    <span class="text-muted">{{ __('No permissions') }}</span>
                @endforelse
            @else
                <span class="text-muted">{{ __('No role assigned') }}</span>
            @endif
        </x-valex.card>

        @if ($user->groups->count() > 0)
            <x-valex.card :title="__('Groups') . ' (' . $user->groups->count() . ')'">
                <div class="list-group">
                    @foreach ($user->groups as $group)
                        <a href="{{ route('dashboard.groups.show', [$organization, $group]) }}" class="list-group-item list-group-item-action">
                            <strong>{{ $group->name }}</strong>
                            @if ($group->branch)<small class="d-block text-muted">{{ $group->branch->name }}</small>@endif
                        </a>
                    @endforeach
                </div>
            </x-valex.card>
        @endif

        @if ($user->channels->count() > 0)
            <x-valex.card :title="__('Channels') . ' (' . $user->channels->count() . ')'">
                <div class="list-group">
                    @foreach ($user->channels as $channel)
                        <a href="{{ route('dashboard.channels.show', [$organization, $channel]) }}" class="list-group-item list-group-item-action">
                            <strong>{{ $channel->name }}</strong>
                            <small class="d-block text-muted">{{ ucfirst($channel->type) }} {{ __('Channel') }}</small>
                        </a>
                    @endforeach
                </div>
            </x-valex.card>
        @endif
    </div>

    <div class="col-lg-4">
        <x-valex.card :title="__('Actions')">
            <a href="{{ route('dashboard.users.edit', [$organization, $user]) }}" class="btn btn-warning btn-block mb-2"><i class="fe fe-edit"></i> {{ __('Edit User') }}</a>
            <form action="{{ route('dashboard.users.destroy', [$organization, $user]) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('{{ __('Are you sure? This action cannot be undone.') }}')"><i class="fe fe-trash-2"></i> {{ __('Delete User') }}</button>
            </form>
        </x-valex.card>

        <x-valex.card :title="__('System Info')">
            <small class="text-muted d-block mb-2"><strong>{{ __('Last Seen') }}:</strong><br>{{ $user->last_seen_at?->diffForHumans() ?? __('Never') }}</small>
            <small class="text-muted d-block mb-2"><strong>{{ __('Email Verified') }}:</strong><br>{{ $user->email_verified_at ? __('Yes') : __('No') }}</small>
            <small class="text-muted d-block"><strong>{{ __('Created') }}:</strong><br>{{ $user->created_at->format('M d, Y H:i') }}</small>
        </x-valex.card>
    </div>
</div>
@endsection
