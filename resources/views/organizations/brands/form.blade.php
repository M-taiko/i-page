@extends('layouts.valex')

@php
    // Route-aware: super admin manages brands under the admin panel; org admin under org settings.
    $isSuperAdmin = auth()->user()->hasRole('super_admin');
    $storeRoute = $isSuperAdmin ? 'admin.organizations.brands.store' : 'organizations.brands.store';
    $updateRoute = $isSuperAdmin ? 'admin.organizations.brands.update' : 'organizations.brands.update';
    $cancelUrl = $isSuperAdmin ? route('admin.organizations.show', $organization) : route('organizations.settings', $organization);
    $brandColors = isset($brand) ? ($brand->colors ?? []) : [];
@endphp

@section('title', isset($brand) ? __('Edit Brand') : __('Create Brand'))

@section('page-header')
    <x-valex.page-header :title="isset($brand) ? __('Edit Brand') : __('Create Brand')" :subtitle="$organization->name">
        <x-slot:actions>
            <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <form action="{{ isset($brand) ? route($updateRoute, [$organization, $brand]) : route($storeRoute, $organization) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($brand)) @method('PUT') @endif

            <x-valex.card :title="__('Basic Information')">
                <x-valex.input name="name" :label="__('Brand Name')" :value="$brand->name ?? ''" required />
                <x-valex.input name="slug" :label="__('Slug')" :value="$brand->slug ?? ''" :placeholder="__('auto-generated from name')" />
                <x-valex.textarea name="description" :label="__('Description')" rows="3" :value="$brand->description ?? ''" />
            </x-valex.card>

            <x-valex.card :title="__('Branding')">
                <div class="row">
                    <div class="col-md-6"><x-valex.input name="primary_color" type="color" :label="__('Primary Color')" :value="$brandColors['primary'] ?? '#007bff'" /></div>
                    <div class="col-md-6"><x-valex.input name="secondary_color" type="color" :label="__('Secondary Color')" :value="$brandColors['secondary'] ?? '#6c757d'" /></div>
                </div>
            </x-valex.card>

            <x-valex.card :title="__('Settings')">
                <x-valex.switch name="is_active" :label="__('Active')" :checked="$brand->is_active ?? true" />
            </x-valex.card>

            <button type="submit" class="btn btn-primary"><i class="fe fe-check"></i> {{ isset($brand) ? __('Update Brand') : __('Create Brand') }}</button>
            <a href="{{ $cancelUrl }}" class="btn btn-light">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
