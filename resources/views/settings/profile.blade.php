@extends('layouts.mobile-shell')

@section('title', __('Settings') . ' - i-Page')

@section('app-bar')
    <a href="{{ route('user.feed') }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-bar-title">{{ __('Settings') }}</div>
@endsection

@section('extra-styles')
    .profile-hero {
        position: relative;
        color: white;
        text-align: center;
    }

    .profile-cover {
        height: 140px;
        width: 100%;
        background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
        background-size: cover;
        background-position: center;
        position: relative;
    }

    .profile-cover-edit-btn {
        position: absolute;
        top: var(--space-3);
        right: var(--space-3);
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.4);
        color: white;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: var(--text-sm);
    }

    .profile-hero-body {
        padding: 0 var(--space-4) var(--space-5);
        margin-top: -48px;
    }

    .profile-hero-avatar-wrap {
        position: relative;
        width: 92px;
        height: 92px;
        margin: 0 auto var(--space-3);
    }

    .profile-hero-avatar {
        width: 92px;
        height: 92px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: var(--font-weight-bold);
        color: white;
        border: 4px solid var(--surface-bg-secondary);
        overflow: hidden;
    }

    .profile-hero-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .profile-avatar-edit-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: var(--primary-600);
        color: white;
        border: 2px solid var(--surface-bg-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 13px;
    }

    .profile-hero-name { font-size: var(--text-xl); font-weight: var(--font-weight-bold); margin-bottom: 2px; color: var(--text-primary); }
    .profile-hero-meta { font-size: var(--text-sm); color: var(--text-tertiary); }
    .profile-hero-id { font-size: var(--text-xs); color: var(--text-tertiary); opacity: 0.8; margin-top: 4px; }

    .photo-menu-overlay {
        display: none;
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1050;
        align-items: flex-end;
        justify-content: center;
    }
    .photo-menu-overlay.show { display: flex; }
    .photo-menu {
        background-color: var(--surface-bg);
        width: 100%; max-width: 480px;
        border-radius: 20px 20px 0 0;
        padding: var(--space-3) 0 max(var(--space-4), env(safe-area-inset-bottom));
    }
    .photo-menu-handle { width: 40px; height: 4px; background-color: var(--surface-border); border-radius: 999px; margin: 0 auto var(--space-3); }
    .photo-menu-action {
        display: flex; align-items: center; gap: var(--space-3); width: 100%;
        padding: var(--space-3) var(--space-4); background: none; border: none;
        text-align: start; cursor: pointer; font-size: var(--text-sm); color: var(--text-primary);
    }
    .photo-menu-action:hover { background-color: var(--surface-hover); }
    .photo-menu-action i { font-size: var(--text-lg); color: var(--text-secondary); width: 22px; }
    .photo-menu-action.danger { color: var(--danger-600); }
    .photo-menu-action.danger i { color: var(--danger-600); }

    .confirm-modal-overlay {
        display: none;
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1060;
        align-items: center;
        justify-content: center;
        padding: var(--space-4);
    }
    .confirm-modal-overlay.show { display: flex; }
    .confirm-modal {
        background-color: var(--surface-bg);
        border-radius: var(--radius-xl);
        padding: var(--space-6);
        max-width: 360px;
        width: 100%;
        text-align: center;
    }
    .confirm-modal i.icon { font-size: 2.25rem; color: var(--danger-600); display: block; margin-bottom: var(--space-3); }
    .confirm-modal h3 { font-size: var(--text-lg); margin-bottom: var(--space-2); color: var(--text-primary); }
    .confirm-modal p { color: var(--text-secondary); margin-bottom: var(--space-4); font-size: var(--text-sm); }
    .confirm-modal input {
        width: 100%; padding: var(--space-3); border-radius: var(--radius-md);
        border: 1px solid var(--surface-border); background-color: var(--surface-bg-secondary);
        color: var(--text-primary); font-size: var(--text-sm); margin-bottom: var(--space-2);
    }
    .confirm-modal .field-error { color: var(--danger-600); font-size: var(--text-xs); margin-bottom: var(--space-3); text-align: start; }
    .confirm-modal-actions { display: flex; gap: var(--space-3); }
    .confirm-modal-actions button {
        flex: 1; padding: var(--space-3); border-radius: var(--radius-md); font-weight: var(--font-weight-medium);
        font-size: var(--text-sm); cursor: pointer; border: none;
    }
    .confirm-modal-actions .btn-cancel { background-color: var(--surface-hover); color: var(--text-primary); }
    .confirm-modal-actions .btn-danger { background-color: var(--danger-600); color: white; }

    .settings-group { margin-top: var(--space-4); }

    .settings-group-title {
        padding: 0 var(--space-4);
        font-size: 11px;
        font-weight: var(--font-weight-bold);
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: var(--space-2);
    }

    .settings-card {
        background-color: var(--surface-bg);
        border-top: 1px solid var(--surface-border);
        border-bottom: 1px solid var(--surface-border);
    }

    .settings-row {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        padding: var(--space-3) var(--space-4);
        border-bottom: 1px solid var(--surface-border);
    }

    .settings-row:last-child { border-bottom: none; }

    .settings-row-icon {
        width: 30px; height: 30px; border-radius: var(--radius-md);
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: var(--text-sm); flex-shrink: 0;
    }

    .settings-row-label { flex: 1; font-size: var(--text-sm); color: var(--text-primary); }
    .settings-row-value { font-size: var(--text-sm); color: var(--text-tertiary); }

    .settings-input {
        border: none;
        background: none;
        outline: none;
        font-size: var(--text-sm);
        color: var(--text-primary);
        flex: 1;
        text-align: end;
        padding: 0;
    }

    .settings-input:disabled { color: var(--text-tertiary); }

    .settings-select {
        border: none;
        background: none;
        outline: none;
        font-size: var(--text-sm);
        color: var(--text-primary);
        text-align: end;
        direction: ltr;
    }

    /* Telegram-style pill toggle */
    .toggle-switch { position: relative; display: inline-block; width: 44px; height: 26px; flex-shrink: 0; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; cursor: pointer; inset: 0;
        background-color: var(--surface-border); border-radius: 999px; transition: 0.2s;
    }
    .toggle-slider::before {
        content: ""; position: absolute; height: 20px; width: 20px; left: 3px; bottom: 3px;
        background-color: white; border-radius: 50%; transition: 0.2s;
    }
    .toggle-switch input:checked + .toggle-slider { background-color: var(--success-500); }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(18px); }

    .save-btn-row { padding: var(--space-4); }
    .save-btn {
        width: 100%; padding: var(--space-3); border-radius: var(--radius-md);
        background-color: var(--primary-600); color: white; border: none;
        font-weight: var(--font-weight-semibold); font-size: var(--text-sm); cursor: pointer;
    }

    .logout-btn {
        width: 100%; padding: var(--space-3) var(--space-4); border: none; background-color: var(--surface-bg);
        color: var(--danger-600); font-weight: var(--font-weight-medium); font-size: var(--text-sm);
        text-align: center; cursor: pointer;
    }

    .danger-btn {
        width: 100%; padding: var(--space-3) var(--space-4); border: none; background-color: var(--surface-bg);
        color: var(--danger-600); font-weight: var(--font-weight-medium); font-size: var(--text-sm);
        text-align: center; cursor: pointer;
    }

    .bottom-spacer { height: var(--space-6); }
@endsection

@section('content')
    <div class="profile-hero">
        <div class="profile-cover" style="{{ $user->cover_path ? 'background-image: url(' . \Illuminate\Support\Facades\Storage::url($user->cover_path) . ');' : '' }}">
            <button type="button" class="profile-cover-edit-btn" onclick="openPhotoMenu('cover')" aria-label="{{ __('Change cover photo') }}">
                <i class="bi bi-camera-fill"></i>
            </button>
        </div>

        <div class="profile-hero-body">
            <div class="profile-hero-avatar-wrap">
                <div class="profile-hero-avatar">
                    @if($user->avatar_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($user->avatar_path) }}" alt="{{ $user->full_name }}">
                    @else
                        {{ $user->initials }}
                    @endif
                </div>
                <button type="button" class="profile-avatar-edit-btn" onclick="openPhotoMenu('avatar')" aria-label="{{ __('Change profile photo') }}">
                    <i class="bi bi-camera-fill"></i>
                </button>
            </div>
            <div class="profile-hero-name">{{ $user->full_name }}</div>
            <div class="profile-hero-meta">{{ $user->email }}</div>
            @if($user->ipage_id)
                <div class="profile-hero-id">ID: {{ $user->ipage_id }}</div>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div style="margin: var(--space-4) var(--space-4) 0; padding: var(--space-3); background-color: var(--success-50); color: var(--success-700); border-radius: var(--radius-md); font-size: var(--text-sm);">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Profile Tier Status -->
    @php
        $currentLevel = $user->computeProfileLevel();
        $tierIcon = match($currentLevel) {
            'business' => 'bi-briefcase-fill',
            'private' => 'bi-shield-check',
            default => 'bi-person-check',
        };
        $tierHint = match($currentLevel) {
            'business' => __('Your business details (company, department, role) are managed by your organization admin.'),
            'private' => __('Your profile is verified for organizations that need more detail (e.g. hotel/university registration).'),
            default => __('Fill in your date of birth, nationality, and city/country below to unlock a Private Profile.'),
        };
    @endphp
    <div style="margin: var(--space-4); padding: var(--space-4); background-color: var(--surface-bg); border: 1px solid var(--surface-border); border-radius: var(--radius-lg); display: flex; gap: var(--space-3); align-items: flex-start;">
        <span style="width: 36px; height: 36px; border-radius: var(--radius-full); background-color: var(--primary-50); color: var(--primary-600); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="bi {{ $tierIcon }}"></i>
        </span>
        <div>
            <div style="font-weight: var(--font-weight-bold); font-size: var(--text-sm); color: var(--text-primary);">{{ $user->profile_level_label }}</div>
            <div style="font-size: var(--text-xs); color: var(--text-secondary); margin-top: 2px;">{{ $tierHint }}</div>
        </div>
    </div>

    <!-- Basic Profile + Private Profile (one form, one save action) -->
    <form action="{{ route('profile.updateProfile') }}" method="POST">
        @csrf

        <div class="settings-group">
            <div class="settings-group-title">
                <i class="bi bi-person-check"></i> {{ __('Basic Profile') }}
            </div>
            <div style="margin: 0 var(--space-4) var(--space-2); font-size: var(--text-xs); color: var(--text-tertiary);">
                {{ __('Name, phone, and email — used for joining public channels.') }}
            </div>
            <div class="settings-card">
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #4557f5;"><i class="bi bi-person"></i></span>
                    <span class="settings-row-label">{{ __('First Name') }}</span>
                    <input type="text" name="first_name" class="settings-input" value="{{ old('first_name', $user->first_name) }}" required>
                </div>
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #4557f5;"><i class="bi bi-person"></i></span>
                    <span class="settings-row-label">{{ __('Last Name') }}</span>
                    <input type="text" name="last_name" class="settings-input" value="{{ old('last_name', $user->last_name) }}" required>
                </div>
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #059669;"><i class="bi bi-telephone"></i></span>
                    <span class="settings-row-label">{{ __('Mobile') }}</span>
                    <input type="text" name="mobile" class="settings-input" placeholder="+1 234 567 8900" value="{{ old('mobile', $user->mobile) }}">
                </div>
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #d97706;"><i class="bi bi-envelope"></i></span>
                    <span class="settings-row-label">{{ __('Email') }}</span>
                    <input type="email" class="settings-input" value="{{ $user->email }}" disabled>
                </div>
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #0891b2;"><i class="bi bi-at"></i></span>
                    <span class="settings-row-label">{{ __('Username') }}</span>
                    <input type="text" name="username" class="settings-input" placeholder="{{ __('e.g. john.doe') }}" value="{{ old('username', $user->username) }}">
                </div>
                @if(!$user->username)
                    <div style="padding: 0 var(--space-4) var(--space-2); font-size: var(--text-xs); color: var(--text-tertiary);">
                        {{ __('Choose a username so others can find you.') }}
                    </div>
                @endif
                @if($user->email_verified_at || $user->mobile_verified_at)
                    <div class="settings-row">
                        <span class="settings-row-icon" style="background-color: #059669;"><i class="bi bi-patch-check"></i></span>
                        <span class="settings-row-label">{{ __('Verification') }}</span>
                        <span class="settings-row-value" style="color: var(--success-600);"><i class="bi bi-check-circle-fill"></i> {{ __('Verified') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="settings-group">
            <div class="settings-group-title">
                <i class="bi bi-shield-check"></i> {{ __('Private Profile') }}
            </div>
            <div style="margin: 0 var(--space-4) var(--space-2); font-size: var(--text-xs); color: var(--text-tertiary);">
                {{ __('Nationality, gender, age, and location — for organizations that need more detail (e.g. hotel guest registration, university access).') }}
            </div>
            <div class="settings-card">
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #7c3aed;"><i class="bi bi-gender-ambiguous"></i></span>
                    <span class="settings-row-label">{{ __('Gender') }}</span>
                    <select name="gender" class="settings-select">
                        <option value="">{{ __('Not set') }}</option>
                        <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                        <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                        <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                    </select>
                </div>
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #dc2626;"><i class="bi bi-geo-alt"></i></span>
                    <span class="settings-row-label">{{ __('Nationality') }}</span>
                    <input type="text" name="nationality" class="settings-input" placeholder="{{ __('e.g. Egyptian') }}" value="{{ old('nationality', $user->nationality) }}">
                </div>
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #0d9488;"><i class="bi bi-cake2"></i></span>
                    <span class="settings-row-label">{{ __('Date of Birth') }}</span>
                    <input type="date" name="dob" class="settings-input" value="{{ old('dob', $user->dob?->format('Y-m-d')) }}">
                </div>
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #2563eb;"><i class="bi bi-building"></i></span>
                    <span class="settings-row-label">{{ __('City') }}</span>
                    <input type="text" name="city" class="settings-input" placeholder="{{ __('e.g. Cairo') }}" value="{{ old('city', $user->city) }}">
                </div>
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #db2777;"><i class="bi bi-flag"></i></span>
                    <span class="settings-row-label">{{ __('Country') }}</span>
                    <input type="text" name="country" class="settings-input" placeholder="{{ __('e.g. Egypt') }}" value="{{ old('country', $user->country) }}">
                </div>
            </div>
        </div>

        <div class="save-btn-row">
            <button type="submit" class="save-btn"><i class="bi bi-check-lg"></i> {{ __('Save Changes') }}</button>
        </div>
    </form>

    <!-- Business Profile -->
    @php
        $activeMembership = $user->organizationMemberships()->where('status', 'active')->latest()->first();
        $invitedMembership = $activeMembership ? null : $user->organizationMemberships()->where('status', 'invited')->latest()->first();
        $pendingMembership = $activeMembership || $invitedMembership ? null : $user->organizationMemberships()->where('status', 'pending')->latest()->first();
    @endphp
    <div class="settings-group" id="business">
        <div class="settings-group-title">
            <i class="bi bi-briefcase-fill"></i> {{ __('Business Profile') }}
        </div>
        <div style="margin: 0 var(--space-4) var(--space-2); font-size: var(--text-xs); color: var(--text-tertiary);">
            {{ __('Company, department, role, and employee ID — for internal staff communication.') }}
        </div>

        @if($activeMembership)
            <div class="settings-card">
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #2563eb;"><i class="bi bi-building"></i></span>
                    <span class="settings-row-label">{{ __('Company') }}</span>
                    <span class="settings-row-value">{{ $activeMembership->organization->name }}</span>
                </div>
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #059669;"><i class="bi bi-person-badge"></i></span>
                    <span class="settings-row-label">{{ __('Role') }}</span>
                    <span class="settings-row-value">{{ $user->formatRoleLabel($activeMembership->role) }}</span>
                </div>
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: {{ $activeMembership->isBusinessVerified() ? '#059669' : '#9ca3af' }};">
                        <i class="bi {{ $activeMembership->isBusinessVerified() ? 'bi-patch-check-fill' : 'bi-clock-history' }}"></i>
                    </span>
                    <span class="settings-row-label">{{ __('Verification') }}</span>
                    <span class="settings-row-value" style="{{ $activeMembership->isBusinessVerified() ? 'color: var(--success-600); font-weight: 600;' : '' }}">
                        {{ $activeMembership->isBusinessVerified() ? __('Verified') : __('Pending Verification') }}
                    </span>
                </div>
            </div>

            <form action="{{ route('organizations.membership.business-details.update', $activeMembership->organization_id) }}" method="POST" style="margin-top: var(--space-3);">
                @csrf
                @method('PUT')
                <div class="settings-card">
                    <div class="settings-row">
                        <span class="settings-row-icon" style="background-color: #7c3aed;"><i class="bi bi-briefcase"></i></span>
                        <span class="settings-row-label">{{ __('Job Title') }}</span>
                        <input type="text" name="job_title" class="settings-input" placeholder="{{ __('e.g. Front Desk Manager') }}" value="{{ old('job_title', $activeMembership->job_title) }}">
                    </div>
                    <div class="settings-row">
                        <span class="settings-row-icon" style="background-color: #d97706;"><i class="bi bi-upc-scan"></i></span>
                        <span class="settings-row-label">{{ __('Employee ID') }}</span>
                        <input type="text" name="employee_id" class="settings-input" placeholder="{{ __('e.g. EMP-1024') }}" value="{{ old('employee_id', $activeMembership->employee_id) }}">
                    </div>
                    @if($activeMembership->organization->departments->count() > 0)
                        <div class="settings-row">
                            <span class="settings-row-icon" style="background-color: #7c3aed;"><i class="bi bi-diagram-3"></i></span>
                            <span class="settings-row-label">{{ __('Department') }}</span>
                            <select name="department_id" class="settings-select">
                                <option value="">{{ __('Not set') }}</option>
                                @foreach($activeMembership->organization->departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $activeMembership->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <p style="margin: var(--space-2) var(--space-4) 0; font-size: var(--text-xs); color: var(--text-tertiary);">
                    {{ __('Editing these details resets verification until your admin re-confirms them.') }}
                </p>
                <div class="save-btn-row">
                    <button type="submit" class="save-btn"><i class="bi bi-check-lg"></i> {{ __('Save Business Details') }}</button>
                </div>
            </form>
        @elseif($invitedMembership)
            <div class="settings-card" style="padding: var(--space-4); text-align: center; color: var(--text-secondary); font-size: var(--text-sm);">
                <i class="bi bi-envelope-paper" style="font-size: 1.5rem; display: block; margin-bottom: var(--space-2); color: var(--primary-600);"></i>
                {{ __(':org invited you to join as :role.', ['org' => $invitedMembership->organization->name, 'role' => $user->formatRoleLabel($invitedMembership->role)]) }}
                <div style="margin-top: var(--space-3); display: flex; gap: var(--space-2);">
                    <form action="{{ route('organizations.membership.accept-invite', $invitedMembership->organization_id) }}" method="POST" style="flex: 1;">
                        @csrf
                        <button type="submit" class="save-btn" style="width: 100%;">
                            <i class="bi bi-check-lg"></i> {{ __('Accept') }}
                        </button>
                    </form>
                    <form action="{{ route('organizations.membership.decline-invite', $invitedMembership->organization_id) }}" method="POST" style="flex: 1;" onsubmit="return confirm('{{ __('Decline this invitation?') }}')">
                        @csrf
                        <button type="submit" class="save-btn" style="width: 100%; background-color: var(--danger-600, #dc2626);">
                            <i class="bi bi-x-lg"></i> {{ __('Decline') }}
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div id="businessDynamicZone">
                @if($pendingMembership)
                    @include('settings.partials.business-pending', ['organizationId' => $pendingMembership->organization_id, 'organizationName' => $pendingMembership->organization->name])
                @else
                    @include('settings.partials.business-search')
                @endif
            </div>
        @endif
    </div>

    <!-- Friends (auto-populated from shared organizations) -->
    <div class="settings-group" id="friends">
        <div class="settings-group-title">
            <i class="bi bi-people-fill"></i> {{ __('Friends') }}
        </div>
        <div style="margin: 0 var(--space-4) var(--space-2); font-size: var(--text-xs); color: var(--text-tertiary);">
            {{ __('Automatically includes everyone who works at the same organization(s) as you.') }}
        </div>

        @if($colleagues->count() > 0)
            <div class="settings-card">
                @foreach($colleagues as $colleague)
                    <div class="settings-row">
                        <span style="width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0; overflow: hidden; display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); font-size: var(--text-xs); background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));">
                            @if($colleague->avatar_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($colleague->avatar_path) }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                {{ $colleague->initials }}
                            @endif
                        </span>
                        <span class="settings-row-label">
                            {{ $colleague->full_name }}
                            <span style="display: block; font-size: var(--text-xs); color: var(--text-tertiary);">
                                {{ $colleague->username ? '@' . $colleague->username . ' · ' : '' }}{{ $colleague->sharedOrganizationNames }}
                            </span>
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="settings-card" style="padding: var(--space-5) var(--space-4); text-align: center;">
                <i class="bi bi-people" style="font-size: 1.75rem; display: block; margin-bottom: var(--space-2); color: var(--text-tertiary);"></i>
                <p style="font-size: var(--text-sm); color: var(--text-secondary); margin: 0;">
                    {{ __('Join an organization to see your colleagues here.') }}
                </p>
            </div>
        @endif
    </div>

    <!-- Appearance -->
    <div class="settings-group">
        <div class="settings-group-title">{{ __('Appearance') }}</div>
        <form action="{{ route('profile.updateAppearance') }}" method="POST" id="appearanceForm">
            @csrf
            <div class="settings-card">
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #2563eb;"><i class="bi bi-moon-stars"></i></span>
                    <span class="settings-row-label">{{ __('Theme') }}</span>
                    <select name="theme" class="settings-select" onchange="document.getElementById('appearanceForm').submit()">
                        <option value="light" {{ $user->theme === 'light' || !$user->theme ? 'selected' : '' }}>☀️ {{ __('Light') }}</option>
                        <option value="dark" {{ $user->theme === 'dark' ? 'selected' : '' }}>🌙 {{ __('Dark') }}</option>
                        <option value="auto" {{ $user->theme === 'auto' ? 'selected' : '' }}>🔄 {{ __('Auto') }}</option>
                    </select>
                </div>
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #db2777;"><i class="bi bi-translate"></i></span>
                    <span class="settings-row-label">{{ __('Language') }}</span>
                    <select name="language" class="settings-select" onchange="document.getElementById('appearanceForm').submit()">
                        <option value="en" {{ $user->language === 'en' || !$user->language ? 'selected' : '' }}>🇺🇸 English</option>
                        <option value="ar" {{ $user->language === 'ar' ? 'selected' : '' }}>🇸🇦 العربية</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Notifications -->
    <div class="settings-group">
        <div class="settings-group-title">{{ __('Notifications') }}</div>
        <form action="{{ route('profile.updateNotifications') }}" method="POST" id="notifForm">
            @csrf
            <div class="settings-card">
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #059669;"><i class="bi bi-envelope-fill"></i></span>
                    <span class="settings-row-label">{{ __('Email Notifications') }}</span>
                    <label class="toggle-switch">
                        <input type="checkbox" name="email_notifications" value="1" {{ ($preferences->email_notifications ?? true) ? 'checked' : '' }} onchange="document.getElementById('notifForm').submit()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #4557f5;"><i class="bi bi-bell-fill"></i></span>
                    <span class="settings-row-label">{{ __('Push Notifications') }}</span>
                    <label class="toggle-switch">
                        <input type="checkbox" name="push_notifications" value="1" {{ ($preferences->push_notifications ?? true) ? 'checked' : '' }} onchange="document.getElementById('notifForm').submit()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="settings-row">
                    <span class="settings-row-icon" style="background-color: #d97706;"><i class="bi bi-chat-dots-fill"></i></span>
                    <span class="settings-row-label">{{ __('SMS Notifications') }}</span>
                    <label class="toggle-switch">
                        <input type="checkbox" name="sms_notifications" value="1" {{ ($preferences->sms_notifications ?? false) ? 'checked' : '' }} onchange="document.getElementById('notifForm').submit()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </form>
    </div>

    <!-- Account Info -->
    <div class="settings-group">
        <div class="settings-group-title">{{ __('Account') }}</div>
        <div class="settings-card">
            <div class="settings-row">
                <span class="settings-row-icon" style="background-color: #6b7280;"><i class="bi bi-calendar-check"></i></span>
                <span class="settings-row-label">{{ __('Member Since') }}</span>
                <span class="settings-row-value">{{ $user->created_at->format('M j, Y') }}</span>
            </div>
            <div class="settings-row">
                <span class="settings-row-icon" style="background-color: {{ $user->email_verified_at ? '#059669' : '#d97706' }};"><i class="bi bi-shield-check"></i></span>
                <span class="settings-row-label">{{ __('Account Status') }}</span>
                <span class="settings-row-value">{{ $user->email_verified_at ? __('Verified') : __('Unverified') }}</span>
            </div>
        </div>
    </div>

    <!-- Logout -->
    <div class="settings-group">
        <form id="logout-form" method="POST" action="{{ route('logout') }}">
            @csrf
        </form>
        <div class="settings-card">
            <button type="button" class="logout-btn" onclick="document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-right"></i> {{ __('Log Out') }}
            </button>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="settings-group">
        <div class="settings-group-title">{{ __('Danger Zone') }}</div>
        <div class="settings-card">
            <button type="button" class="danger-btn" onclick="confirmDelete()">
                <i class="bi bi-trash"></i> {{ __('Delete Account') }}
            </button>
        </div>
    </div>

    <div class="bottom-spacer"></div>

    <!-- Hidden upload forms -->
    <form id="avatarForm" action="{{ route('profile.updateAvatar') }}" method="POST" enctype="multipart/form-data" style="display:none;">
        @csrf
        <input type="file" name="avatar" id="avatarInput" accept="image/png,image/jpeg,image/webp" onchange="document.getElementById('avatarForm').submit()">
    </form>
    <form id="avatarRemoveForm" action="{{ route('profile.removeAvatar') }}" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    <form id="coverForm" action="{{ route('profile.updateCover') }}" method="POST" enctype="multipart/form-data" style="display:none;">
        @csrf
        <input type="file" name="cover" id="coverInput" accept="image/png,image/jpeg,image/webp" onchange="document.getElementById('coverForm').submit()">
    </form>
    <form id="coverRemoveForm" action="{{ route('profile.removeCover') }}" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('modals')
    <div class="confirm-modal-overlay" id="deleteAccountModal" onclick="if(event.target===this) closeDeleteAccountModal()">
        <div class="confirm-modal">
            <i class="bi bi-exclamation-triangle-fill icon"></i>
            <h3>{{ __('Delete Your Account?') }}</h3>
            <p>{{ __('This cannot be undone. Enter your password to confirm.') }}</p>
            <form action="{{ route('profile.deleteAccount') }}" method="POST" id="deleteAccountForm">
                @csrf
                @method('DELETE')
                <input type="password" name="password" placeholder="{{ __('Password') }}" required autocomplete="current-password">
                @error('password', 'deleteAccount')
                    <div class="field-error">{{ $message }}</div>
                @enderror
                <div class="confirm-modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeDeleteAccountModal()">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-danger">{{ __('Delete Account') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="photo-menu-overlay" id="photoMenuOverlay" onclick="if(event.target===this) closePhotoMenu()">
        <div class="photo-menu">
            <div class="photo-menu-handle"></div>
            <button type="button" class="photo-menu-action" onclick="triggerPhotoUpload()">
                <i class="bi bi-image"></i> <span id="photoMenuChooseLabel">{{ __('Choose Photo') }}</span>
            </button>
            <button type="button" class="photo-menu-action danger" id="photoMenuRemoveBtn" onclick="removeCurrentPhoto()">
                <i class="bi bi-trash"></i> {{ __('Remove Photo') }}
            </button>
            <button type="button" class="photo-menu-action" onclick="closePhotoMenu()">
                <i class="bi bi-x-lg"></i> {{ __('Cancel') }}
            </button>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    (function () {
        const zone = document.getElementById('businessDynamicZone');
        if (!zone) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const joinUrlTemplate = '{{ route('organizations.membership.request', ['organization' => '__ID__']) }}';
        const cancelUrlTemplate = '{{ route('organizations.membership.cancel', ['organization' => '__ID__']) }}';

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function pendingTemplate(orgId, orgName) {
            return `
                <div class="settings-card" style="padding: var(--space-4); text-align: center; color: var(--text-secondary); font-size: var(--text-sm);">
                    <i class="bi bi-hourglass-split" style="font-size: 1.5rem; display: block; margin-bottom: var(--space-2); color: var(--primary-600);"></i>
                    {{ __('Your request to join') }} ${escapeHtml(orgName)} {{ __('is pending approval.') }}
                    <div style="margin-top: var(--space-3);">
                        <button type="button" id="cancelJoinRequestBtn" data-org-id="${orgId}" class="save-btn" style="background-color: var(--danger-600, #dc2626);">
                            <i class="bi bi-x-lg"></i> {{ __('Cancel Request') }}
                        </button>
                    </div>
                </div>
            `;
        }

        function searchTemplate() {
            return `
                <div class="settings-card" style="padding: var(--space-4); position: relative;">
                    <p style="font-size: var(--text-sm); color: var(--text-secondary); margin: 0 0 var(--space-3);">
                        {{ __('Search for your organization and send a request to join as staff.') }}
                    </p>
                    <div style="position: relative;">
                        <input type="text" id="orgJoinSearchInput" autocomplete="off" class="settings-input"
                               style="border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: var(--space-2) var(--space-3); width: 100%;"
                               placeholder="{{ __('e.g. Cairo University') }}">
                        <div id="orgJoinSearchResults" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 20; background-color: var(--surface-bg); border: 1px solid var(--surface-border); border-radius: var(--radius-md); box-shadow: var(--shadow-lg, 0 8px 24px rgba(0,0,0,0.12)); max-height: 320px; overflow-y: auto;"></div>
                    </div>
                </div>
            `;
        }

        function showPendingState(orgId, orgName) {
            zone.innerHTML = pendingTemplate(orgId, orgName);
            wirePendingZone();
        }

        function showSearchState() {
            zone.innerHTML = searchTemplate();
            wireSearchZone();
        }

        function wirePendingZone() {
            const cancelBtn = document.getElementById('cancelJoinRequestBtn');
            if (!cancelBtn) return;

            cancelBtn.addEventListener('click', function () {
                if (!confirm('{{ __('Cancel this join request?') }}')) return;

                const orgId = cancelBtn.getAttribute('data-org-id');
                cancelBtn.disabled = true;

                fetch(cancelUrlTemplate.replace('__ID__', orgId), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                })
                    .then(res => { if (!res.ok) throw new Error(); return res.json(); })
                    .then(() => showSearchState())
                    .catch(() => {
                        cancelBtn.disabled = false;
                        alert('{{ __('Something went wrong — please try again.') }}');
                    });
            });
        }

        function wireSearchZone() {
            const input = document.getElementById('orgJoinSearchInput');
            const resultsBox = document.getElementById('orgJoinSearchResults');
            if (!input || !resultsBox) return;

            let debounceTimer = null;

            input.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                const query = input.value.trim();

                if (query.length < 1) {
                    resultsBox.style.display = 'none';
                    resultsBox.innerHTML = '';
                    return;
                }

                debounceTimer = setTimeout(() => runSearch(query), 300);
            });

            function runSearch(query) {
                fetch('{{ route('api.organizations.search') }}?q=' + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(data => renderResults(data.organizations || []))
                    .catch(() => renderResults([]));
            }

            function renderResults(organizations) {
                if (organizations.length === 0) {
                    resultsBox.innerHTML = '<div style="padding: var(--space-4); text-align: center; color: var(--text-tertiary); font-size: var(--text-sm);">{{ __('No organizations found.') }}</div>';
                    resultsBox.style.display = 'block';
                    return;
                }

                resultsBox.innerHTML = organizations.map(org => `
                    <div class="settings-row" data-org-row="${org.id}" style="padding: var(--space-3) var(--space-4);">
                        <span class="settings-row-icon" style="background-color: #2563eb;"><i class="bi bi-building"></i></span>
                        <span class="settings-row-label">
                            ${escapeHtml(org.name)}
                            <span style="display: block; font-size: var(--text-xs); color: var(--text-tertiary);">${org.users_count ?? 0} {{ __('members') }}</span>
                        </span>
                        <button type="button" class="save-btn" data-join-btn="${org.id}" data-join-name="${escapeHtml(org.name)}" style="width: auto; padding: var(--space-2) var(--space-3); font-size: var(--text-xs);">
                            {{ __('Request to Join') }}
                        </button>
                    </div>
                `).join('');

                resultsBox.style.display = 'block';

                resultsBox.querySelectorAll('[data-join-btn]').forEach(btn => {
                    btn.addEventListener('click', () => sendJoinRequest(btn));
                });
            }

            function sendJoinRequest(btn) {
                const orgId = btn.getAttribute('data-join-btn');
                const orgName = btn.getAttribute('data-join-name');
                btn.disabled = true;
                btn.textContent = '{{ __('Sending…') }}';

                fetch(joinUrlTemplate.replace('__ID__', orgId), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                })
                    .then(res => { if (!res.ok) throw new Error(); return res.json(); })
                    .then(() => showPendingState(orgId, orgName))
                    .catch(() => {
                        btn.disabled = false;
                        btn.textContent = '{{ __('Request to Join') }}';
                        alert('{{ __('Something went wrong — please try again.') }}');
                    });
            }

            document.addEventListener('click', function (event) {
                if (!resultsBox.contains(event.target) && event.target !== input) {
                    resultsBox.style.display = 'none';
                }
            });
        }

        if (document.getElementById('orgJoinSearchInput')) {
            wireSearchZone();
        } else if (document.getElementById('cancelJoinRequestBtn')) {
            wirePendingZone();
        }
    })();

    function confirmDelete() {
        document.getElementById('deleteAccountModal').classList.add('show');
    }

    function closeDeleteAccountModal() {
        document.getElementById('deleteAccountModal').classList.remove('show');
    }

    @if($errors->getBag('deleteAccount')->any())
        document.addEventListener('DOMContentLoaded', () => confirmDelete());
    @endif

    let currentPhotoTarget = null; // 'avatar' | 'cover'

    function openPhotoMenu(target) {
        currentPhotoTarget = target;
        document.getElementById('photoMenuChooseLabel').textContent = target === 'avatar'
            ? '{{ __('Choose Profile Photo') }}'
            : '{{ __('Choose Cover Photo') }}';

        const hasPhoto = target === 'avatar' ? {{ $user->avatar_path ? 'true' : 'false' }} : {{ $user->cover_path ? 'true' : 'false' }};
        document.getElementById('photoMenuRemoveBtn').style.display = hasPhoto ? 'flex' : 'none';

        document.getElementById('photoMenuOverlay').classList.add('show');
    }

    function closePhotoMenu() {
        document.getElementById('photoMenuOverlay').classList.remove('show');
    }

    function triggerPhotoUpload() {
        closePhotoMenu();
        if (currentPhotoTarget === 'avatar') {
            document.getElementById('avatarInput').click();
        } else {
            document.getElementById('coverInput').click();
        }
    }

    function removeCurrentPhoto() {
        closePhotoMenu();
        if (currentPhotoTarget === 'avatar') {
            document.getElementById('avatarRemoveForm').submit();
        } else {
            document.getElementById('coverRemoveForm').submit();
        }
    }
</script>
@endsection
