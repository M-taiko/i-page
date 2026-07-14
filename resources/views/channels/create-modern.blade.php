@extends('layouts.app-modern')

@section('title', __('Create Channel'))

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-info">
        <h1>{{ __('Create Channel') }}</h1>
        <p>{{ __('Launch a new communication channel') }}</p>
    </div>
</div>

<div style="max-width: 600px;">
    <x-card-modern title="{{ __('New Channel') }}" icon="chat-dots" elevated>
        <form action="{{ route('dashboard.channels.store', session('current_organization_id')) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <x-form-input
                name="name"
                label="{{ __('Channel Name') }}"
                placeholder="{{ __('Enter channel name') }}"
                icon="chat-dots"
                required
                error="{{ $errors->first('name') }}"
            />

            <div class="form-group">
                <label class="form-label">{{ __('Type') }}<span style="color: var(--danger-500);">*</span></label>
                <select name="type" class="form-input @error('type') is-invalid @enderror" required>
                    <option value="">{{ __('— Select Type —') }}</option>
                    <option value="public" {{ old('type') === 'public' ? 'selected' : '' }}>{{ __('Public') }}</option>
                    <option value="private" {{ old('type') === 'private' ? 'selected' : '' }}>{{ __('Private') }}</option>
                </select>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Audience Profile') }}<span style="color: var(--danger-500);">*</span></label>
                <select name="audience_profile" class="form-input @error('audience_profile') is-invalid @enderror" required>
                    <option value="">{{ __('— Select Profile —') }}</option>
                    <option value="business" {{ old('audience_profile') === 'business' ? 'selected' : '' }}>{{ __('Business') }}</option>
                    <option value="public" {{ old('audience_profile') === 'public' ? 'selected' : '' }}>{{ __('Public') }}</option>
                    <option value="private" {{ old('audience_profile') === 'private' ? 'selected' : '' }}>{{ __('Private') }}</option>
                </select>
                @error('audience_profile')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <x-form-input
                name="audience_count"
                label="{{ __('Expected Audience Count') }}"
                type="number"
                placeholder="1000"
                icon="people"
                error="{{ $errors->first('audience_count') }}"
            />

            <div class="form-group">
                <label class="form-label">{{ __('Channel Logo') }}</label>
                <input type="file" name="logo" class="form-input @error('logo') is-invalid @enderror" accept="image/png,image/jpeg,image/svg+xml">
                <small style="display: block; margin-top: var(--space-2); color: var(--text-tertiary);">{{ __('PNG, JPG, SVG (max 2MB)') }}</small>
                @error('logo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid var(--surface-border); display: flex; gap: var(--space-3);">
                <x-btn type="submit" variant="primary">{{ __('Create Channel') }}</x-btn>
                <x-btn href="{{ route('dashboard.home', session('current_organization_id')) }}" variant="outline">{{ __('Cancel') }}</x-btn>
            </div>
        </form>
    </x-card-modern>
</div>
@endsection
