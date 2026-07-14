@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3">Create Channel</h1>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <x-card>
                <form action="{{ route('dashboard.channels.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Channel Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror"
                            id="type" name="type" required>
                            <option value="">— Select Type —</option>
                            <option value="public" {{ old('type') === 'public' ? 'selected' : '' }}>Public</option>
                            <option value="private" {{ old('type') === 'private' ? 'selected' : '' }}>Private</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="audience_profile" class="form-label">Audience Profile <span class="text-danger">*</span></label>
                        <select class="form-select @error('audience_profile') is-invalid @enderror"
                            id="audience_profile" name="audience_profile" required>
                            <option value="">— Select Profile —</option>
                            <option value="business" {{ old('audience_profile') === 'business' ? 'selected' : '' }}>Business</option>
                            <option value="public" {{ old('audience_profile') === 'public' ? 'selected' : '' }}>Public</option>
                            <option value="private" {{ old('audience_profile') === 'private' ? 'selected' : '' }}>Private</option>
                        </select>
                        @error('audience_profile')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="audience_count" class="form-label">Expected Audience Count</label>
                        <input type="number" class="form-control @error('audience_count') is-invalid @enderror"
                            id="audience_count" name="audience_count" value="{{ old('audience_count') }}" min="1">
                        @error('audience_count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="logo" class="form-label">Channel Logo</label>
                        <input type="file" class="form-control @error('logo') is-invalid @enderror"
                            id="logo" name="logo" accept="image/png,image/jpeg,image/svg+xml">
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">PNG, JPG, SVG (max 2MB)</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Create Channel</button>
                        <a href="{{ route('dashboard.home') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</div>
@endsection
