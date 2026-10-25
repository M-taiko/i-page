@extends('layouts.valex')

@section('title', $group->name)

@section('page-header')
    <x-valex.page-header :title="$group->name" :subtitle="$group->branch?->name">
        <x-slot:actions>
            <a href="{{ route('dashboard.groups.index', session('current_organization_id')) }}" class="btn btn-outline-secondary mx-1"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
            <a href="{{ route('dashboard.groups.edit', [session('current_organization_id'), $group], $group) }}" class="btn btn-primary mx-1"><i class="fe fe-edit"></i> {{ __('Edit') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <x-valex.card :title="__('Group Details')">
            <div class="mb-3"><div class="small text-muted text-uppercase">{{ __('Name') }}</div><strong>{{ $group->name }}</strong></div>
            @if ($group->description)
                <div class="mb-3"><div class="small text-muted text-uppercase">{{ __('Description') }}</div>{{ $group->description }}</div>
            @endif
            <div class="mb-3">
                <div class="small text-muted text-uppercase">{{ __('Branch') }}</div>
                @if ($group->branch)<x-valex.badge variant="info">{{ $group->branch->name }}</x-valex.badge>@else<span class="text-muted">—</span>@endif
            </div>
            <div><div class="small text-muted text-uppercase">{{ __('Created') }}</div>{{ $group->created_at->format('M d, Y H:i') }}</div>
        </x-valex.card>

        <x-valex.card :title="__('Members') . ' (' . $group->users->count() . ')'">
            @if ($group->users->count() > 0)
                <ul class="list-group list-group-flush">
                    @foreach ($group->users as $user)
                        <li class="list-group-item d-flex align-items-center px-0">
                            <span class="avatar avatar-md brround bg-primary mx-2">{{ $user->initials }}</span>
                            <div class="flex-grow-1">
                                <strong class="d-block">{{ $user->full_name }}</strong>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                            <x-valex.badge variant="secondary">{{ $user->roles()->first()?->name ?? __('No Role') }}</x-valex.badge>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted text-center mb-0">{{ __('This group has no members yet.') }}</p>
            @endif
        </x-valex.card>
    </div>

    <div class="col-lg-4">
        <x-valex.card :title="__('Actions')">
            <a href="{{ route('dashboard.groups.edit', [session('current_organization_id'), $group], $group) }}" class="btn btn-primary btn-block mb-2">{{ __('Edit Group') }}</a>
            <form action="{{ route('dashboard.groups.destroy', [session('current_organization_id'), $group], $group) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('{{ __('Are you sure?') }}')">{{ __('Delete Group') }}</button>
            </form>
        </x-valex.card>

        <x-valex.card :title="__('Info')">
            <small class="text-muted d-block">{{ __('Members') }}</small>
            <h3>{{ $group->users->count() }}</h3>
            <hr>
            <small class="text-muted d-block">{{ __('Created') }}</small>
            <small>{{ $group->created_at->diffForHumans() }}</small>
        </x-valex.card>
    </div>
</div>
@endsection
