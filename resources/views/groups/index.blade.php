@extends('layouts.valex')

@section('title', __('Groups'))

@section('page-header')
    <x-valex.page-header :title="__('Groups')" :subtitle="__('Organize your team into groups for better collaboration')">
        <x-slot:actions>
            <a href="{{ route('dashboard.groups.create', session('current_organization_id')) }}" class="btn btn-primary"><i class="fe fe-plus-circle"></i> {{ __('Create Group') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@if ($groups->count() > 0)
    <div class="row row-sm">
        @foreach ($groups as $group)
            <div class="col-md-6 col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-1">{{ $group->name }}</h5>
                        @if ($group->branch)<small class="text-muted">{{ $group->branch->name }}</small>@endif

                        @if ($group->description)
                            <p class="text-muted mt-3 mb-0">{{ Str::limit($group->description, 100) }}</p>
                        @endif

                        <div class="border-top border-bottom my-3 py-3 text-muted">
                            <i class="fe fe-users text-primary"></i> {{ $group->users->count() }} {{ __('members') }}
                        </div>

                        <div class="d-flex">
                            <a href="{{ route('dashboard.groups.show', [session('current_organization_id'), $group], $group) }}" class="btn btn-sm btn-outline-primary flex-fill mx-1">{{ __('View') }}</a>
                            <a href="{{ route('dashboard.groups.edit', [session('current_organization_id'), $group], $group) }}" class="btn btn-sm btn-light flex-fill mx-1">{{ __('Edit') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center">{{ $groups->links('pagination::bootstrap-4') }}</div>
@else
    <x-valex.empty-state :title="__('No Groups')" :message="__('Create your first group to start organizing your team.')" icon="share-2">
        <a href="{{ route('dashboard.groups.create', session('current_organization_id')) }}" class="btn btn-primary mt-3"><i class="fe fe-plus-circle"></i> {{ __('Create Group') }}</a>
    </x-valex.empty-state>
@endif
@endsection
