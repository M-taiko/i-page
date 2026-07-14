@extends('layouts.app-modern')

@section('content')
@php
    // Route-aware: super admin manages brands under the admin panel; org admin under org settings.
    $isSuperAdmin = auth()->user()->hasRole('super_admin');
    $storeRoute = $isSuperAdmin ? 'admin.organizations.brands.store' : 'organizations.brands.store';
    $updateRoute = $isSuperAdmin ? 'admin.organizations.brands.update' : 'organizations.brands.update';
    $cancelUrl = $isSuperAdmin ? route('admin.organizations.show', $organization) : route('organizations.settings', $organization);
    $brandColors = isset($brand) ? ($brand->colors ?? []) : [];
@endphp
<div class="container-lg py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-4">{{ isset($brand) ? 'Edit Brand' : 'Create Brand' }}</h1>

            <form action="{{ isset($brand) ? route($updateRoute, [$organization, $brand]) : route($storeRoute, $organization) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($brand))
                    @method('PUT')
                @endif

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Brand Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $brand->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $brand->slug ?? '') }}" placeholder="auto-generated from name">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $brand->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Branding</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="primary_color" class="form-label">Primary Color</label>
                            <input type="color" class="form-control form-control-color @error('primary_color') is-invalid @enderror" id="primary_color" name="primary_color" value="{{ old('primary_color', $brandColors['primary'] ?? '#007bff') }}">
                            @error('primary_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="secondary_color" class="form-label">Secondary Color</label>
                            <input type="color" class="form-control form-control-color @error('secondary_color') is-invalid @enderror" id="secondary_color" name="secondary_color" value="{{ old('secondary_color', $brandColors['secondary'] ?? '#6c757d') }}">
                            @error('secondary_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $brand->is_active ?? true))>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> {{ isset($brand) ? 'Update' : 'Create' }} Brand
                    </button>
                    <a href="{{ $cancelUrl }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
