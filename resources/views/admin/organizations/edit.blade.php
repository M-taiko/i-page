@extends('layouts.app-modern')

@section('title', __('Edit') . ' ' . $organization->name)

@section('content')
<!-- Page Header with Back Button -->
<div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-6);">
    <a href="{{ route('admin.organizations.show', $organization->id) }}" class="btn" style="background-color: var(--surface-hover); color: var(--text-primary); border: 1px solid var(--surface-border); padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-2);">
        <i class="bi bi-arrow-left"></i>
        <span>{{ __('Back') }}</span>
    </a>
</div>

<!-- Header Section -->
<div style="display: flex; align-items: center; gap: var(--space-4); margin-bottom: var(--space-6);">
    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-600), var(--secondary-600)); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center; color: white; font-size: var(--text-3xl); font-weight: var(--font-weight-bold);">
        {{ substr($organization->name, 0, 1) }}
    </div>
    <div>
        <h1 style="margin: 0 0 var(--space-1); font-size: var(--text-3xl); font-weight: var(--font-weight-bold); color: var(--text-primary);">
            {{ __('Edit Organization') }}
        </h1>
        <p style="margin: 0; color: var(--text-secondary);">
            {{ $organization->name }}
        </p>
    </div>
</div>

<!-- Error Messages -->
@if($errors->any())
    <div style="background-color: var(--danger-50); border: 1px solid var(--danger-200); border-radius: var(--radius-md); padding: var(--space-4); margin-bottom: var(--space-6);">
        <div style="display: flex; align-items: flex-start; gap: var(--space-3);">
            <i class="bi bi-exclamation-circle" style="color: var(--danger-600); font-size: var(--text-lg); flex-shrink: 0; margin-top: var(--space-1);"></i>
            <div style="flex: 1;">
                <h3 style="margin: 0 0 var(--space-2); color: var(--danger-700); font-size: var(--text-base); font-weight: var(--font-weight-semibold);">
                    {{ __('Please fix the following errors:') }}
                </h3>
                <ul style="margin: 0; padding-left: var(--space-4); color: var(--danger-700);">
                    @foreach($errors->all() as $error)
                        <li style="margin-bottom: var(--space-1);">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<!-- Form -->
<form action="{{ route('admin.organizations.update', $organization->id) }}" method="POST">
    @csrf
    @method('PUT')

    <!-- Content Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-6); margin-bottom: var(--space-6);">
        <!-- Left Column - Main Information -->
        <div>
            <!-- Organization Information Card -->
            <x-card-modern style="margin-bottom: var(--space-6);">
                <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-6); padding-bottom: var(--space-6); border-bottom: 1px solid var(--surface-border);">
                    <i class="bi bi-info-circle" style="font-size: var(--text-2xl); color: var(--primary-600);"></i>
                    <h2 style="margin: 0; font-size: var(--text-xl); font-weight: var(--font-weight-bold); color: var(--text-primary);">
                        {{ __('Organization Information') }}
                    </h2>
                </div>

                <!-- Organization Name -->
                <div style="margin-bottom: var(--space-6);">
                    <label class="form-label" style="display: block; margin-bottom: var(--space-2); font-weight: var(--font-weight-semibold); color: var(--text-primary); font-size: var(--text-sm);">
                        {{ __('Organization Name') }} <span style="color: var(--danger-600);">*</span>
                    </label>
                    <input type="text"
                           class="form-control @error('name') is-invalid @enderror"
                           name="name"
                           value="{{ old('name', $organization->name) }}"
                           placeholder="{{ __('Enter organization name') }}"
                           required
                           style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg);">
                    <p style="margin: var(--space-1) 0 0; font-size: var(--text-xs); color: var(--text-tertiary);">
                        {{ __('The official name of the organization') }}
                    </p>
                    @error('name')
                        <p style="color: var(--danger-600); font-size: var(--text-xs); margin-top: var(--space-1);">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label class="form-label" style="display: block; margin-bottom: var(--space-2); font-weight: var(--font-weight-semibold); color: var(--text-primary); font-size: var(--text-sm);">
                        {{ __('Description') }}
                    </label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              name="description"
                              rows="6"
                              placeholder="{{ __('Enter a detailed description...') }}"
                              style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg); font-family: inherit; resize: vertical;">{{ old('description', $organization->description) }}</textarea>
                    <p style="margin: var(--space-1) 0 0; font-size: var(--text-xs); color: var(--text-tertiary);">
                        {{ __('A short description to help identify the organization') }}
                    </p>
                    @error('description')
                        <p style="color: var(--danger-600); font-size: var(--text-xs); margin-top: var(--space-1);">{{ $message }}</p>
                    @enderror
                </div>
            </x-card-modern>

            <!-- Location Card -->
            <x-card-modern>
                <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-6); padding-bottom: var(--space-6); border-bottom: 1px solid var(--surface-border);">
                    <i class="bi bi-geo-alt" style="font-size: var(--text-2xl); color: var(--primary-600);"></i>
                    <h2 style="margin: 0; font-size: var(--text-xl); font-weight: var(--font-weight-bold); color: var(--text-primary);">
                        {{ __('Location') }}
                    </h2>
                </div>

                <!-- City & Country -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                    <div>
                        <label class="form-label" style="display: block; margin-bottom: var(--space-2); font-weight: var(--font-weight-semibold); color: var(--text-primary); font-size: var(--text-sm);">
                            {{ __('City') }}
                        </label>
                        <input type="text"
                               class="form-control"
                               name="city"
                               value="{{ old('city', $organization->city) }}"
                               placeholder="{{ __('e.g., Cairo') }}"
                               style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg);">
                    </div>
                    <div>
                        <label class="form-label" style="display: block; margin-bottom: var(--space-2); font-weight: var(--font-weight-semibold); color: var(--text-primary); font-size: var(--text-sm);">
                            {{ __('Country') }}
                        </label>
                        <input type="text"
                               class="form-control"
                               name="country"
                               value="{{ old('country', $organization->country) }}"
                               placeholder="{{ __('e.g., Egypt') }}"
                               style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg);">
                    </div>
                </div>
            </x-card-modern>
        </div>

        <!-- Right Column - Settings -->
        <div>
            <!-- Settings Card -->
            <x-card-modern style="position: sticky; top: var(--space-6);">
                <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-6); padding-bottom: var(--space-6); border-bottom: 1px solid var(--surface-border);">
                    <i class="bi bi-gear" style="font-size: var(--text-2xl); color: var(--primary-600);"></i>
                    <h2 style="margin: 0; font-size: var(--text-xl); font-weight: var(--font-weight-bold); color: var(--text-primary);">
                        {{ __('Settings') }}
                    </h2>
                </div>

                <!-- Max Channels -->
                <div style="margin-bottom: var(--space-6);">
                    <label class="form-label" style="display: block; margin-bottom: var(--space-2); font-weight: var(--font-weight-semibold); color: var(--text-primary); font-size: var(--text-sm);">
                        {{ __('Max Channels') }}
                    </label>
                    <input type="number"
                           class="form-control @error('max_channels') is-invalid @enderror"
                           name="max_channels"
                           value="{{ old('max_channels', $organization->max_channels) }}"
                           min="1"
                           max="1000"
                           required
                           style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg);">
                    <p style="margin: var(--space-1) 0 0; font-size: var(--text-xs); color: var(--text-tertiary);">
                        {{ $organization->channels()->count() }} {{ __('active') }}
                    </p>
                    @error('max_channels')
                        <p style="color: var(--danger-600); font-size: var(--text-xs); margin-top: var(--space-1);">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status Checkbox -->
                <div style="display: flex; align-items: flex-start; gap: var(--space-3); padding: var(--space-4); background-color: var(--surface-hover); border-radius: var(--radius-md);">
                    <input type="checkbox"
                           id="is_active"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $organization->is_active) ? 'checked' : '' }}
                           style="width: 20px; height: 20px; cursor: pointer; accent-color: var(--primary-600); margin-top: var(--space-1); flex-shrink: 0;">
                    <div style="flex: 1;">
                        <label for="is_active" style="margin: 0; font-weight: var(--font-weight-semibold); color: var(--text-primary); cursor: pointer; display: block;">
                            {{ __('Active') }}
                        </label>
                        <p style="margin: var(--space-1) 0 0; font-size: var(--text-xs); color: var(--text-secondary);">
                            {{ __('Allow users to access') }}
                        </p>
                    </div>
                </div>
            </x-card-modern>
        </div>
    </div>

    <!-- Info Box -->
    <div style="background-color: var(--info-50); border: 1px solid var(--info-200); border-radius: var(--radius-md); padding: var(--space-4); margin-bottom: var(--space-6); display: flex; gap: var(--space-3);">
        <i class="bi bi-info-circle" style="color: var(--info-600); font-size: var(--text-lg); flex-shrink: 0; margin-top: var(--space-1);"></i>
        <div style="color: var(--info-700); font-size: var(--text-sm);">
            <strong>{{ __('Note:') }}</strong> {{ __('All changes will be applied immediately. Make sure the data is correct before saving.') }}
        </div>
    </div>

    <!-- Action Buttons -->
    <div style="display: flex; gap: var(--space-3);">
        <button type="submit" class="btn btn-primary" style="flex: 1; background-color: var(--primary-600); color: white; padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); font-size: var(--text-sm); font-weight: var(--font-weight-semibold); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: var(--space-2);">
            <i class="bi bi-check-circle"></i>
            <span>{{ __('Save Changes') }}</span>
        </button>
        <a href="{{ route('admin.organizations.show', $organization->id) }}" class="btn" style="flex: 1; background-color: var(--surface-hover); color: var(--text-primary); border: 1px solid var(--surface-border); padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); font-size: var(--text-sm); font-weight: var(--font-weight-semibold); text-decoration: none; display: flex; align-items: center; justify-content: center; gap: var(--space-2);">
            <i class="bi bi-x-circle"></i>
            <span>{{ __('Cancel') }}</span>
        </a>
    </div>
</form>

@endsection
