@extends('layouts.app-modern')

@section('title', __('Edit Group'))

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-info">
        <h1>{{ __('Edit Group') }}</h1>
        <p>{{ $group->name }}</p>
    </div>
</div>

<div style="max-width: 600px;">
    <x-card-modern title="{{ __('Group Information') }}" icon="diagram-3" elevated>
        <form action="{{ route('dashboard.groups.update', [session('current_organization_id'), $group]) }}" method="POST">
            @csrf
            @method('PUT')

            <x-form-input
                name="name"
                label="{{ __('Group Name') }}"
                placeholder="{{ __('Enter group name') }}"
                icon="diagram-3"
                value="{{ $group->name }}"
                required
                error="{{ $errors->first('name') }}"
            />

            <div class="form-group">
                <label class="form-label">{{ __('Description') }}</label>
                <textarea name="description" class="form-input @error('description') is-invalid @enderror" rows="4" placeholder="{{ __('Optional description') }}">{{ old('description', $group->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Branch') }}<span style="color: var(--danger-500);">*</span></label>
                <select name="branch_id" class="form-input @error('branch_id') is-invalid @enderror" required>
                    <option value="">{{ __('— Select Branch —') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $group->branch_id) == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @error('branch_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid var(--surface-border); display: flex; gap: var(--space-3);">
                <x-btn type="submit" variant="primary">{{ __('Update Group') }}</x-btn>
                <x-btn href="{{ route('dashboard.groups.show', [session('current_organization_id'), $group]) }}" variant="outline">{{ __('Cancel') }}</x-btn>
            </div>
        </form>
    </x-card-modern>
</div>
@endsection
