@extends('layouts.valex')

@section('title', $brand->name)

@section('page-header')
    <x-valex.page-header :title="$brand->name" :subtitle="__('Brand command center')">
        <x-slot:actions>
            @if ($tab === 'overview')<x-valex.range-switch :days="$days" />@endif
            @if ($canManage)
                <a href="{{ route('organizations.brands.posts.create', [$organization, $brand]) }}" class="btn btn-primary mx-2"><i class="fe fe-send"></i> {{ __('New brand post') }}</a>
            @endif
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $tabs = [
        'overview' => ['bar-chart-2', __('Overview')],
        'channels' => ['git-branch', __('Channels')],
        'posts' => ['file-text', __('Posts')],
        'people' => ['users', __('People')],
        'managers' => ['shield', __('Managers')],
        'activity' => ['activity', __('Activity')],
    ];
@endphp

<div class="card mb-3">
    <div class="card-body d-flex flex-wrap align-items-center">
        <span class="avatar avatar-lg brround bg-primary-transparent text-primary mx-2"><i class="fe fe-award"></i></span>
        <div class="flex-grow-1" style="min-width: 0;">
            <h5 class="mb-0">{{ $brand->name }} @unless ($brand->is_active)<x-valex.badge variant="secondary">{{ __('Inactive') }}</x-valex.badge>@endunless</h5>
            <small class="text-muted">{{ $brand->description ?: __('No description') }} · {{ trans_choice(':count channel|:count channels', $channelCount) }}</small>
        </div>
        @if ($canAssign)
            <a href="{{ route('organizations.brands.edit', [$organization, $brand]) }}" class="btn btn-sm btn-outline-secondary mx-1"><i class="fe fe-edit"></i> {{ __('Edit') }}</a>
        @endif
    </div>
    <div class="card-footer p-0 border-top">
        <nav class="nav nav-tabs border-0 flex-nowrap" style="overflow-x: auto;">
            @foreach ($tabs as $key => [$icon, $label])
                <a class="nav-link border-0 text-nowrap {{ $tab === $key ? 'active font-weight-bold text-primary' : 'text-muted' }}"
                   href="{{ route('organizations.brands.show', [$organization, $brand, 'tab' => $key]) }}"
                   @if ($tab === $key) aria-current="page" style="border-bottom: 2px solid currentColor !important;" @endif>
                    <i class="fe fe-{{ $icon }}"></i> {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>
</div>

@include('organizations.brands.tabs.' . $tab)
@endsection
