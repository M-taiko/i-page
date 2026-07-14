@extends('layouts.app-modern')

@section('title', __('Settings'))

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-info">
        <h1>{{ __('Settings') }}</h1>
        <p>{{ __('Manage your account and preferences') }}</p>
    </div>
</div>

<!-- Settings Tabs/Sections -->
<div style="display: grid; grid-template-columns: 1fr 2fr; gap: var(--space-8);">
    <!-- Sidebar Navigation -->
    <div style="display: flex; flex-direction: column; gap: var(--space-2);">
        <button class="settings-nav-btn active" data-section="profile">
            <i class="bi bi-person"></i>
            {{ __('Profile') }}
        </button>
        <button class="settings-nav-btn" data-section="appearance">
            <i class="bi bi-palette"></i>
            {{ __('Appearance') }}
        </button>
        <button class="settings-nav-btn" data-section="notifications">
            <i class="bi bi-bell"></i>
            {{ __('Notifications') }}
        </button>
    </div>

    <!-- Settings Content -->
    <div>
        <!-- Profile Settings -->
        <div class="settings-section active" id="profile">
            <x-card-modern title="{{ __('Profile Settings') }}" icon="person" elevated>
                <form action="{{ route('dashboard.settings.updateProfile', session('current_organization_id')) }}" method="POST">
                    @csrf

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6); margin-bottom: var(--space-6);">
                        <div class="form-group">
                            <label class="form-label">{{ __('First Name') }}<span style="color: var(--danger-500);">*</span></label>
                            <input type="text" class="form-input @error('first_name') is-invalid @enderror" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ __('Last Name') }}<span style="color: var(--danger-500);">*</span></label>
                            <input type="text" class="form-input @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('Mobile') }}</label>
                        <input type="text" class="form-input @error('mobile') is-invalid @enderror" name="mobile" value="{{ old('mobile', $user->mobile) }}">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6);">
                        <div class="form-group">
                            <label class="form-label">{{ __('Gender') }}</label>
                            <select class="form-input @error('gender') is-invalid @enderror" name="gender">
                                <option value="">{{ __('Select...') }}</option>
                                <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                                <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ __('Nationality') }}</label>
                            <input type="text" class="form-input @error('nationality') is-invalid @enderror" name="nationality" value="{{ old('nationality', $user->nationality) }}">
                        </div>
                    </div>

                    <div style="margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid var(--surface-border);">
                        <x-btn type="submit" variant="primary">
                            {{ __('Save Changes') }}
                        </x-btn>
                    </div>
                </form>
            </x-card-modern>
        </div>

        <!-- Appearance Settings -->
        <div class="settings-section" id="appearance" style="display: none;">
            <x-card-modern title="{{ __('Appearance') }}" icon="palette" elevated>
                <form action="{{ route('dashboard.settings.updateAppearance', session('current_organization_id')) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">{{ __('Theme') }}</label>
                        <select class="form-input @error('theme') is-invalid @enderror" name="theme">
                            <option value="auto" {{ old('theme', $user->theme) === 'auto' ? 'selected' : '' }}>{{ __('Auto') }}</option>
                            <option value="light" {{ old('theme', $user->theme) === 'light' ? 'selected' : '' }}>{{ __('Light') }}</option>
                            <option value="dark" {{ old('theme', $user->theme) === 'dark' ? 'selected' : '' }}>{{ __('Dark') }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('Language') }}</label>
                        <select class="form-input @error('language') is-invalid @enderror" name="language">
                            <option value="en" {{ old('language', $user->language) === 'en' ? 'selected' : '' }}>English</option>
                            <option value="ar" {{ old('language', $user->language) === 'ar' ? 'selected' : '' }}>العربية</option>
                        </select>
                    </div>

                    <div style="margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid var(--surface-border);">
                        <x-btn type="submit" variant="primary">
                            {{ __('Save Changes') }}
                        </x-btn>
                    </div>
                </form>
            </x-card-modern>
        </div>

        <!-- Notification Settings -->
        <div class="settings-section" id="notifications" style="display: none;">
            <x-card-modern title="{{ __('Notifications') }}" icon="bell" elevated>
                <form action="{{ route('dashboard.settings.updateNotifications', session('current_organization_id')) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-check-login" style="margin-bottom: var(--space-4); display: flex; align-items: center; gap: var(--space-3); padding: var(--space-3) 0;">
                            <input type="checkbox" name="notify_posts" {{ $user->preferences?->notification_posts ? 'checked' : '' }}>
                            <span style="cursor: pointer; flex: 1;">
                                <strong style="display: block;">{{ __('Post Notifications') }}</strong>
                                <small style="color: var(--text-tertiary);">{{ __('Get notified when new posts are published') }}</small>
                            </span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-check-login" style="margin-bottom: var(--space-4); display: flex; align-items: center; gap: var(--space-3); padding: var(--space-3) 0; border-top: 1px solid var(--surface-border); border-bottom: 1px solid var(--surface-border);">
                            <input type="checkbox" name="notify_channels" {{ $user->preferences?->notification_channels ? 'checked' : '' }}>
                            <span style="cursor: pointer; flex: 1;">
                                <strong style="display: block;">{{ __('Channel Updates') }}</strong>
                                <small style="color: var(--text-tertiary);">{{ __('Get notified about channel updates') }}</small>
                            </span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-check-login" style="margin-bottom: var(--space-4); display: flex; align-items: center; gap: var(--space-3); padding: var(--space-3) 0;">
                            <input type="checkbox" name="notify_mentions" {{ $user->preferences?->notification_mentions ? 'checked' : '' }}>
                            <span style="cursor: pointer; flex: 1;">
                                <strong style="display: block;">{{ __('Mentions') }}</strong>
                                <small style="color: var(--text-tertiary);">{{ __('Get notified when you\'re mentioned') }}</small>
                            </span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-check-login" style="display: flex; align-items: center; gap: var(--space-3); padding: var(--space-3) 0;">
                            <input type="checkbox" name="notify_groups" {{ $user->preferences?->notification_groups ? 'checked' : '' }}>
                            <span style="cursor: pointer; flex: 1;">
                                <strong style="display: block;">{{ __('Group Activities') }}</strong>
                                <small style="color: var(--text-tertiary);">{{ __('Get notified about group activities') }}</small>
                            </span>
                        </label>
                    </div>

                    <div style="margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid var(--surface-border);">
                        <x-btn type="submit" variant="primary">
                            {{ __('Save Changes') }}
                        </x-btn>
                    </div>
                </form>
            </x-card-modern>
        </div>
    </div>
</div>

<style>
    .settings-nav-btn {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        padding: var(--space-4);
        background: none;
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-lg);
        color: var(--text-secondary);
        cursor: pointer;
        font-size: var(--text-sm);
        font-weight: var(--font-weight-medium);
        transition: all var(--transition-fast);
    }

    .settings-nav-btn:hover {
        background-color: var(--surface-hover);
        color: var(--text-primary);
    }

    .settings-nav-btn.active {
        background-color: var(--primary-50);
        border-color: var(--primary-600);
        color: var(--primary-600);
    }

    .settings-section {
        animation: slideUp var(--transition-base);
    }

    @media (max-width: 1024px) {
        [style*="grid-template-columns: 1fr 2fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
    document.querySelectorAll('.settings-nav-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const section = btn.dataset.section;

            // Hide all sections
            document.querySelectorAll('.settings-section').forEach(s => {
                s.style.display = 'none';
            });

            // Remove active class from all buttons
            document.querySelectorAll('.settings-nav-btn').forEach(b => {
                b.classList.remove('active');
            });

            // Show selected section
            document.getElementById(section).style.display = 'block';
            btn.classList.add('active');
        });
    });
</script>
@endsection
