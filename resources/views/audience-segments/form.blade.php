@extends('layouts.app-modern')

@section('content')
<style>
    .form-page-header {
        background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
        color: white;
        padding: 2rem 1.5rem;
        border-radius: 16px;
        margin-bottom: 1.5rem;
    }

    .form-page-header h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
    .form-page-header p { margin: 0; opacity: 0.9; font-size: 0.875rem; }

    .form-section {
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: 14px;
        padding: 1.5rem;
        margin-bottom: 1.25rem;
    }

    .form-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.4rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-section-subtitle { font-size: 0.8rem; color: var(--text-tertiary); margin-bottom: 1.25rem; }

    .form-group { margin-bottom: 1.25rem; }
    .form-group:last-child { margin-bottom: 0; }

    .form-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.4rem;
    }

    .form-control-modern, .form-select-modern {
        width: 100%;
        padding: 0.65rem 0.9rem;
        border: 1px solid var(--surface-border);
        border-radius: 10px;
        font-size: 0.9rem;
        background-color: var(--surface-bg-secondary);
        color: var(--text-primary);
        transition: all 0.15s ease;
    }

    .form-control-modern:focus, .form-select-modern:focus {
        outline: none;
        border-color: var(--primary-500);
        box-shadow: 0 0 0 3px rgba(69, 87, 245, 0.1);
        background-color: var(--surface-bg);
    }

    .form-hint { font-size: 0.75rem; color: var(--text-tertiary); margin-top: 0.4rem; }

    .form-check-modern {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.9rem 1rem;
        background-color: var(--surface-bg-secondary);
        border-radius: 10px;
    }

    .form-check-modern input { width: 18px; height: 18px; cursor: pointer; }
    .form-check-modern label { margin: 0; cursor: pointer; font-weight: 500; color: var(--text-primary); font-size: 0.85rem; }

    .info-note {
        background-color: var(--info-50);
        color: var(--info-700);
        border-radius: 10px;
        padding: 0.9rem 1.1rem;
        font-size: 0.8rem;
        margin-bottom: 1.25rem;
        display: flex;
        gap: 0.6rem;
        align-items: flex-start;
    }

    .form-actions { display: flex; gap: 0.75rem; }

    .btn-submit-modern {
        padding: 0.7rem 1.75rem;
        background-color: var(--primary-600);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-submit-modern:hover { background-color: var(--primary-700); }

    .btn-cancel-modern {
        padding: 0.7rem 1.75rem;
        background-color: var(--surface-hover);
        color: var(--text-primary);
        border: 1px solid var(--surface-border);
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
</style>

@php
    // Pre-fill fallback: read the segment's saved rule values (by type) when
    // there's no `old()` input, so editing an existing segment shows its
    // previously chosen targeting values instead of blank selects.
    $savedRules = [];
    if (isset($segment)) {
        $decoded = is_array($segment->rules) ? $segment->rules : (json_decode($segment->rules, true) ?? []);
        foreach ($decoded as $rule) {
            if (!empty($rule['type'])) {
                $savedRules[$rule['type']] = $rule['value'];
            }
        }
    }
    $ruleValue = fn($index, $type) => old("rules.$index.value", $savedRules[$type] ?? '');
@endphp

<div class="container-lg py-4" style="max-width: 760px;">
    <div class="form-page-header">
        <h1>{{ isset($segment) ? 'Edit Audience Segment' : 'Create Audience Segment' }}</h1>
        <p>Define who should receive posts and messages targeting this segment</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ isset($segment) ? route('audience-segments.update', $segment) : route('audience-segments.store') }}" method="POST">
        @csrf
        @if(isset($segment))
            @method('PUT')
        @endif

        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-tag"></i> Basic Information</div>

            <div class="form-group">
                <label for="name">Segment Name</label>
                <input type="text" class="form-control-modern" id="name" name="name" value="{{ old('name', $segment->name ?? '') }}" placeholder="e.g., VIP Members, Support Staff" required>
            </div>

            <div class="form-group">
                <label for="description">Description <span style="font-weight:400;color:var(--text-tertiary);">(optional)</span></label>
                <textarea class="form-control-modern" id="description" name="description" rows="2">{{ old('description', $segment->description ?? '') }}</textarea>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-funnel"></i> Targeting Rules</div>
            <div class="form-section-subtitle">Leave a rule blank to match everyone on that dimension.</div>

            <div class="form-group">
                <label for="language">Language</label>
                <select class="form-select-modern" id="language" name="rules[0][value]">
                    <option value="">— All Languages —</option>
                    <option value="en" @selected($ruleValue(0, 'language') == 'en')>English</option>
                    <option value="ar" @selected($ruleValue(0, 'language') == 'ar')>Arabic</option>
                </select>
                <input type="hidden" name="rules[0][type]" value="language">
            </div>

            <div class="form-group">
                <label for="role">User Role</label>
                <select class="form-select-modern" id="role" name="rules[1][value]">
                    <option value="">— All Roles —</option>
                    <option value="organization_admin" @selected($ruleValue(1, 'role') == 'organization_admin')>Organization Admin</option>
                    <option value="manager" @selected($ruleValue(1, 'role') == 'manager')>Manager</option>
                    <option value="moderator" @selected($ruleValue(1, 'role') == 'moderator')>Moderator</option>
                    <option value="staff" @selected($ruleValue(1, 'role') == 'staff')>Staff</option>
                </select>
                <input type="hidden" name="rules[1][type]" value="role">
            </div>

            <div class="form-group">
                <label for="brand_id">Brand</label>
                <select class="form-select-modern" id="brand_id" name="rules[2][value]">
                    <option value="">— All Brands —</option>
                    @foreach(auth()->user()->currentOrganization?->brands ?? [] as $brand)
                        <option value="{{ $brand->id }}" @selected($ruleValue(2, 'brand_id') == $brand->id)>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="rules[2][type]" value="brand_id">
            </div>

            <div class="form-group">
                <label for="location_id">Location</label>
                <select class="form-select-modern" id="location_id" name="rules[3][value]">
                    <option value="">— All Locations —</option>
                    @foreach(auth()->user()->currentOrganization?->locations ?? [] as $location)
                        <option value="{{ $location->id }}" @selected($ruleValue(3, 'location_id') == $location->id)>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="rules[3][type]" value="location_id">
            </div>

            <div class="form-group">
                <label for="department_id">Department</label>
                <select class="form-select-modern" id="department_id" name="rules[4][value]">
                    <option value="">— All Departments —</option>
                    @foreach(auth()->user()->currentOrganization?->departments ?? [] as $department)
                        <option value="{{ $department->id }}" @selected($ruleValue(4, 'department_id') == $department->id)>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="rules[4][type]" value="department_id">
            </div>
        </div>

        <div class="form-section">
            <div class="form-check-modern">
                <input type="checkbox" id="is_active" name="is_active" @checked(old('is_active', $segment->is_active ?? true))>
                <label for="is_active">Active (available for targeting)</label>
            </div>
        </div>

        <div class="info-note">
            <i class="bi bi-info-circle"></i>
            <div><strong>Note:</strong> When multiple rules are specified, a user must match ALL rules to be included in this segment (AND logic).</div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit-modern">
                <i class="bi bi-check-lg"></i> {{ isset($segment) ? 'Update Segment' : 'Create Segment' }}
            </button>
            <a href="{{ route('audience-segments.index') }}" class="btn-cancel-modern">Cancel</a>
        </div>
    </form>
</div>
@endsection
