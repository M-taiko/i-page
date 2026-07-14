@extends('layouts.app-modern')

@section('title', __('Add Team Member'))

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-top">
        <div class="page-header-info">
            <h1>{{ __('Add Team Member') }}</h1>
            <p>{{ __('Invite a new member to your organization') }}</p>
        </div>
    </div>
</div>

<!-- Form Card -->
<x-card-modern style="max-width: 500px;">
    <form method="POST" action="{{ route('dashboard.users.store', $organization) }}">
        @csrf

        <!-- First Name -->
        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label class="form-label" style="display: block; margin-bottom: var(--space-2); font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                {{ __('First Name') }} <span style="color: var(--danger-600);">*</span>
            </label>
            <input type="text"
                   class="form-control @error('first_name') is-invalid @enderror"
                   name="first_name"
                   value="{{ old('first_name') }}"
                   placeholder="{{ __('Enter first name') }}"
                   required
                   style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg);">
            @error('first_name')
                <div style="color: var(--danger-600); font-size: var(--text-xs); margin-top: var(--space-1);">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Last Name -->
        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label class="form-label" style="display: block; margin-bottom: var(--space-2); font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                {{ __('Last Name') }} <span style="color: var(--danger-600);">*</span>
            </label>
            <input type="text"
                   class="form-control @error('last_name') is-invalid @enderror"
                   name="last_name"
                   value="{{ old('last_name') }}"
                   placeholder="{{ __('Enter last name') }}"
                   required
                   style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg);">
            @error('last_name')
                <div style="color: var(--danger-600); font-size: var(--text-xs); margin-top: var(--space-1);">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Email -->
        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label class="form-label" style="display: block; margin-bottom: var(--space-2); font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                {{ __('Email Address') }} <span style="color: var(--danger-600);">*</span>
            </label>
            <input type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   name="email"
                   value="{{ old('email') }}"
                   placeholder="{{ __('example@organization.com') }}"
                   required
                   style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg);">
            @error('email')
                <div style="color: var(--danger-600); font-size: var(--text-xs); margin-top: var(--space-1);">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Mobile (Optional) -->
        <div class="form-group" style="margin-bottom: var(--space-8);">
            <label class="form-label" style="display: block; margin-bottom: var(--space-2); font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                {{ __('Mobile Number') }}
            </label>
            <input type="tel"
                   class="form-control @error('mobile') is-invalid @enderror"
                   name="mobile"
                   value="{{ old('mobile') }}"
                   placeholder="{{ __('(Optional) +1 555-123-4567') }}"
                   style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg);">
            @error('mobile')
                <div style="color: var(--danger-600); font-size: var(--text-xs); margin-top: var(--space-1);">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Role -->
        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label class="form-label" style="display: block; margin-bottom: var(--space-2); font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                {{ __('Role') }} <span style="color: var(--danger-600);">*</span>
            </label>
            <select name="role" required
                    class="form-control @error('role') is-invalid @enderror"
                    style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg);">
                <option value="staff" @selected(old('role')==='staff')>{{ __('Staff — can post') }}</option>
                <option value="moderator" @selected(old('role')==='moderator')>{{ __('Moderator — moderates comments') }}</option>
                <option value="manager" @selected(old('role')==='manager')>{{ __('Manager — manages channels & members') }}</option>
                <option value="organization_admin" @selected(old('role')==='organization_admin')>{{ __('Organization Admin — full control') }}</option>
            </select>
            @error('role')
                <div style="color: var(--danger-600); font-size: var(--text-xs); margin-top: var(--space-1);">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password (optional) -->
        <div class="form-group" style="margin-bottom: var(--space-8);">
            <label class="form-label" style="display: block; margin-bottom: var(--space-2); font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                {{ __('Temporary Password') }}
            </label>
            <input type="text" name="password" value="{{ old('password') }}"
                   placeholder="{{ __('(Optional) leave blank to auto-generate') }}"
                   class="form-control @error('password') is-invalid @enderror"
                   style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg);">
            @error('password')
                <div style="color: var(--danger-600); font-size: var(--text-xs); margin-top: var(--space-1);">{{ $message }}</div>
            @enderror
        </div>

        <!-- Info Box -->
        <div style="background-color: var(--info-50); border: 1px solid var(--info-200); border-radius: var(--radius-md); padding: var(--space-3) var(--space-4); margin-bottom: var(--space-8); display: flex; gap: var(--space-3);">
            <div style="flex-shrink: 0; color: var(--info-600); font-size: var(--text-lg);">
                <i class="bi bi-info-circle"></i>
            </div>
            <div>
                <p style="margin: 0; font-size: var(--text-sm); color: var(--info-700); line-height: var(--line-height-normal);">
                    {{ __('The team member will receive an invitation email with login instructions.') }}
                </p>
            </div>
        </div>

        <!-- Form Footer -->
        <div style="display: flex; gap: var(--space-3); border-top: 1px solid var(--surface-border); padding-top: var(--space-6);">
            <a href="{{ route('dashboard.users.index', $organization) }}"
               class="btn"
               style="flex: 1; background-color: var(--surface-hover); color: var(--text-primary); border: 1px solid var(--surface-border); padding: var(--space-2) var(--space-4); border-radius: var(--radius-md); font-size: var(--text-sm); font-weight: var(--font-weight-medium); text-decoration: none; display: flex; align-items: center; justify-content: center; gap: var(--space-2);">
                <i class="bi bi-arrow-left"></i>
                {{ __('Cancel') }}
            </a>
            <button type="submit"
                    class="btn btn-primary"
                    style="flex: 1; background-color: var(--primary-600); color: white; border: 1px solid var(--primary-600); padding: var(--space-2) var(--space-4); border-radius: var(--radius-md); font-size: var(--text-sm); font-weight: var(--font-weight-medium); cursor: pointer; display: flex; align-items: center; justify-content: center; gap: var(--space-2);">
                <i class="bi bi-check"></i>
                {{ __('Add Member') }}
            </button>
        </div>
    </form>
</x-card-modern>

@endsection
