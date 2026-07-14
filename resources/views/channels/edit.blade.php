@extends('layouts.app-modern')

@section('title', __('Edit Channel'))

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-info">
        <h1>{{ __('Edit Channel') }}</h1>
        <p>{{ $channel->name }}</p>
    </div>
</div>

<div style="max-width: 600px;">
    <x-card-modern title="{{ __('Channel Information') }}" icon="chat-dots" elevated>
        <form action="{{ route('dashboard.channels.update', [session('current_organization_id'), $channel], $channel) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <x-form-input
                name="name"
                label="{{ __('Channel Name') }}"
                placeholder="{{ __('Enter channel name') }}"
                icon="chat-dots"
                value="{{ $channel->name }}"
                required
                error="{{ $errors->first('name') }}"
            />

            <div class="form-group">
                <label class="form-label">{{ __('Type') }}<span style="color: var(--danger-500);">*</span></label>
                <select name="type" class="form-input @error('type') is-invalid @enderror" required>
                    <option value="">{{ __('— Select Type —') }}</option>
                    <option value="public" {{ old('type', $channel->type) === 'public' ? 'selected' : '' }}>{{ __('Public') }}</option>
                    <option value="private" {{ old('type', $channel->type) === 'private' ? 'selected' : '' }}>{{ __('Private') }}</option>
                </select>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Audience Profile') }}<span style="color: var(--danger-500);">*</span></label>
                <select name="audience_profile" class="form-input @error('audience_profile') is-invalid @enderror" required>
                    <option value="">{{ __('— Select Profile —') }}</option>
                    <option value="business" {{ old('audience_profile', $channel->audience_profile) === 'business' ? 'selected' : '' }}>{{ __('Business') }}</option>
                    <option value="public" {{ old('audience_profile', $channel->audience_profile) === 'public' ? 'selected' : '' }}>{{ __('Public') }}</option>
                    <option value="private" {{ old('audience_profile', $channel->audience_profile) === 'private' ? 'selected' : '' }}>{{ __('Private') }}</option>
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
                value="{{ $channel->audience_count }}"
                error="{{ $errors->first('audience_count') }}"
            />

            <div class="form-group">
                <label class="form-label">{{ __('Channel Logo') }}</label>
                @if ($channel->logo_path)
                    <div style="margin-bottom: var(--space-3);">
                        <img src="{{ Storage::url($channel->logo_path) }}" alt="{{ $channel->name }}" style="width: 80px; height: 80px; border-radius: var(--radius-lg); object-fit: cover;">
                    </div>
                @endif
                <input type="file" name="logo" class="form-input @error('logo') is-invalid @enderror" accept="image/png,image/jpeg,image/svg+xml">
                <small style="display: block; margin-top: var(--space-2); color: var(--text-tertiary);">{{ __('PNG, JPG, SVG (max 2MB)') }}</small>
                @error('logo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid var(--surface-border); display: flex; gap: var(--space-3);">
                <x-btn type="submit" variant="primary">{{ __('Update Channel') }}</x-btn>
                <x-btn href="{{ route('dashboard.channels.show', [session('current_organization_id'), $channel], $channel) }}" variant="outline">{{ __('Cancel') }}</x-btn>
            </div>
        </form>
    </x-card-modern>
</div>
@endsection
