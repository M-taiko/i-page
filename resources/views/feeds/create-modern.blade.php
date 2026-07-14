@extends('layouts.app-modern')

@section('title', __('Create Post'))

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-info">
        <h1>{{ __('Create Post') }}</h1>
        <p>{{ __('Share news and updates with your team') }}</p>
    </div>
</div>

<div style="max-width: 700px;">
    <x-card-modern title="{{ __('New Post') }}" icon="pencil-square" elevated>
        <form action="{{ route('dashboard.feeds.store', session('current_organization_id')) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label class="form-label">{{ __('Message') }}<span style="color: var(--danger-500);">*</span></label>
                <textarea
                    name="body"
                    class="form-input @error('body') is-invalid @enderror"
                    rows="6"
                    placeholder="{{ __('What\'s on your mind? (max 2000 characters)') }}"
                    maxlength="2000"
                    required
                >{{ old('body') }}</textarea>
                <small style="display: block; margin-top: var(--space-2); color: var(--text-tertiary);">{{ __('Maximum 2000 characters') }}</small>
                @error('body')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Image') }}</label>
                <input
                    type="file"
                    name="image"
                    class="form-input @error('image') is-invalid @enderror"
                    accept="image/png,image/jpeg,image/webp"
                >
                <small style="display: block; margin-top: var(--space-2); color: var(--text-tertiary);">{{ __('PNG, JPG, WebP (max 5MB)') }}</small>
                @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Share With') }}<span style="color: var(--danger-500);">*</span></label>
                <select
                    name="audience"
                    class="form-input @error('audience') is-invalid @enderror"
                    id="audience"
                    required
                    onchange="toggleChannelSelect()"
                >
                    <option value="">{{ __('— Select Audience —') }}</option>
                    <option value="all" {{ old('audience') === 'all' ? 'selected' : '' }}>{{ __('All Users') }}</option>
                    <option value="in_house" {{ old('audience') === 'in_house' ? 'selected' : '' }}>{{ __('In-house Guests') }}</option>
                    <option value="team" {{ old('audience') === 'team' ? 'selected' : '' }}>{{ __('Team Members') }}</option>
                    <option value="channel" {{ old('audience') === 'channel' ? 'selected' : '' }}>{{ __('Specific Channel') }}</option>
                </select>
                @error('audience')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" id="channelSelect" style="display: none;">
                <label class="form-label">{{ __('Channel') }}</label>
                <select
                    name="channel_id"
                    class="form-input @error('channel_id') is-invalid @enderror"
                    id="channel_id"
                >
                    <option value="">{{ __('— Select Channel —') }}</option>
                    @foreach ($channels as $channel)
                        <option value="{{ $channel->id }}" {{ old('channel_id') == $channel->id ? 'selected' : '' }}>
                            {{ $channel->name }}
                        </option>
                    @endforeach
                </select>
                @error('channel_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid var(--surface-border); display: flex; gap: var(--space-3); flex-wrap: wrap;">
                <x-btn type="submit" variant="primary" icon="pencil-square">
                    {{ __('Publish Post') }}
                </x-btn>
                <x-btn href="{{ route('dashboard.feeds.index', session('current_organization_id')) }}" variant="outline" icon="arrow-left">
                    {{ __('Cancel') }}
                </x-btn>
            </div>
        </form>
    </x-card-modern>
</div>

<script>
    function toggleChannelSelect() {
        const audience = document.getElementById('audience').value;
        const channelSelect = document.getElementById('channelSelect');
        const channelInput = document.getElementById('channel_id');

        if (audience === 'channel') {
            channelSelect.style.display = 'block';
            channelInput.required = true;
        } else {
            channelSelect.style.display = 'none';
            channelInput.required = false;
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', toggleChannelSelect);
</script>
@endsection
