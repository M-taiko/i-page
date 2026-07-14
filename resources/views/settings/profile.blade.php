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

    <!-- Edit Profile -->
    <div class="settings-group">
        <div class="settings-group-title">{{ __('Edit Profile') }}</div>
        <form action="{{ route('profile.updateProfile') }}" method="POST">
            @csrf
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
            </div>
            <div class="save-btn-row">
                <button type="submit" class="save-btn"><i class="bi bi-check-lg"></i> {{ __('Save Changes') }}</button>
            </div>
        </form>
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
    function confirmDelete() {
        if (confirm('{{ __('Are you sure you want to delete your account? This action cannot be undone.') }}')) {
            alert('{{ __('Account deletion is not yet implemented. Please contact support.') }}');
        }
    }

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
