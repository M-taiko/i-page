@extends('layouts.valex')

@section('title', __('Brands'))

@section('page-header')
    <x-valex.page-header :title="__('Brands')" :subtitle="__('Manage your organization\'s brands and their channels')">
        <x-slot:actions>
            <a href="{{ route('organizations.brands.create', $organization) }}" class="btn btn-primary"><i class="fe fe-plus"></i> {{ __('New Brand') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $palette = ['primary', 'purple', 'success', 'warning', 'danger', 'info', 'pink'];
@endphp

@if ($brands->isEmpty())
    <x-valex.empty-state :title="__('No brands yet')" :message="__('Create one to start organizing your channels.')" icon="bookmark" />
@else
    <div class="row row-sm">
        @foreach ($brands as $brand)
            <div class="col-md-6 col-xl-4">
                <a href="{{ route('organizations.brands.show', [$organization, $brand]) }}" class="card text-reset text-decoration-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="avatar avatar-lg brround bg-{{ $palette[crc32($brand->name) % count($palette)] }} mx-2">{{ mb_substr($brand->name, 0, 1) }}</span>
                            <div>
                                <h5 class="mb-1">{{ $brand->name }}</h5>
                                <x-valex.badge :variant="$brand->is_active ? 'success' : 'secondary'">{{ $brand->is_active ? __('Active') : __('Inactive') }}</x-valex.badge>
                            </div>
                        </div>
                        <div class="row text-center border-top pt-3">
                            <div class="col"><h5 class="mb-0">{{ $brand->channels_count }}</h5><small class="text-muted text-uppercase">{{ __('Channels') }}</small></div>
                            <div class="col"><h5 class="mb-0">{{ $brand->followers_count }}</h5><small class="text-muted text-uppercase">{{ __('Followers') }}</small></div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endif
@endsection
