@extends('layouts.app-modern')

@section('title', __('Add Team Member'))

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-top">
        <div class="page-header-info">
            <h1>{{ __('Add Team Member') }}</h1>
            <p>{{ __('Create a brand-new account, or invite someone who already has one.') }}</p>
        </div>
    </div>
</div>

<!-- Mode Tabs -->
<div style="display: flex; gap: var(--space-2); margin-bottom: var(--space-6); max-width: 500px;">
    <button type="button" id="tabNewUserBtn" class="add-member-tab active" onclick="switchAddMemberTab('new')">
        <i class="bi bi-person-plus"></i> {{ __('New User') }}
    </button>
    <button type="button" id="tabInviteBtn" class="add-member-tab" onclick="switchAddMemberTab('invite')">
        <i class="bi bi-search"></i> {{ __('Invite Existing User') }}
    </button>
</div>

<style>
    .add-member-tab {
        flex: 1; padding: var(--space-3) var(--space-4); border-radius: var(--radius-md);
        border: 1px solid var(--surface-border); background-color: var(--surface-bg);
        color: var(--text-secondary); font-size: var(--text-sm); font-weight: var(--font-weight-medium);
        cursor: pointer; display: flex; align-items: center; justify-content: center; gap: var(--space-2);
    }
    .add-member-tab.active { background-color: var(--primary-600); color: white; border-color: var(--primary-600); }
    .invite-search-input {
        width: 100%; padding: var(--space-3) var(--space-4); border: 1px solid var(--surface-border);
        border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg);
    }
    .invite-result-row {
        display: flex; align-items: center; gap: var(--space-3); padding: var(--space-3);
        border: 1px solid var(--surface-border); border-radius: var(--radius-md); margin-top: var(--space-2);
    }
    .invite-result-avatar {
        width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        color: white; font-weight: var(--font-weight-bold); font-size: var(--text-xs); flex-shrink: 0; overflow: hidden;
        background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
    }
    .invite-result-avatar img { width: 100%; height: 100%; object-fit: cover; }
</style>

<!-- New User Form -->
<div id="newUserPanel">
    <x-card-modern style="max-width: 500px;">
        <form method="POST" action="{{ route('dashboard.users.store', $organization) }}">
            @csrf

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

            <div style="background-color: var(--info-50); border: 1px solid var(--info-200); border-radius: var(--radius-md); padding: var(--space-3) var(--space-4); margin-bottom: var(--space-8); display: flex; gap: var(--space-3);">
                <div style="flex-shrink: 0; color: var(--info-600); font-size: var(--text-lg);">
                    <i class="bi bi-info-circle"></i>
                </div>
                <div>
                    <p style="margin: 0; font-size: var(--text-sm); color: var(--info-700); line-height: var(--line-height-normal);">
                        {{ __('This creates a brand-new account and adds them as an active member immediately.') }}
                    </p>
                </div>
            </div>

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
</div>

<!-- Invite Existing User Panel -->
<div id="invitePanel" style="display: none;">
    <x-card-modern style="max-width: 500px;">
        @if(session('error'))
            <div style="background-color: var(--danger-50); color: var(--danger-700); border: 1px solid var(--danger-200); border-radius: var(--radius-md); padding: var(--space-3) var(--space-4); margin-bottom: var(--space-4); font-size: var(--text-sm);">
                {{ session('error') }}
            </div>
        @endif

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label class="form-label" style="display: block; margin-bottom: var(--space-2); font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                {{ __('Search by name or @username') }}
            </label>
            <input type="text" id="inviteSearchInput" autocomplete="off" class="invite-search-input" placeholder="{{ __('e.g. John or @john.doe') }}">
            <div id="inviteSearchResults"></div>
        </div>

        <form method="POST" action="{{ route('organizations.membership.invite-user', $organization) }}" id="inviteForm" style="display: none;">
            @csrf
            <input type="hidden" name="user_id" id="inviteUserId">

            <div style="display: flex; align-items: center; gap: var(--space-3); padding: var(--space-3); background-color: var(--surface-hover); border-radius: var(--radius-md); margin-bottom: var(--space-4);">
                <div class="invite-result-avatar" id="inviteSelectedAvatar"></div>
                <div>
                    <div style="font-weight: var(--font-weight-semibold); font-size: var(--text-sm);" id="inviteSelectedName"></div>
                    <div style="font-size: var(--text-xs); color: var(--text-tertiary);" id="inviteSelectedHandle"></div>
                </div>
                <button type="button" onclick="clearInviteSelection()" style="margin-inline-start: auto; background: none; border: none; color: var(--text-tertiary); cursor: pointer;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="form-group" style="margin-bottom: var(--space-6);">
                <label class="form-label" style="display: block; margin-bottom: var(--space-2); font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                    {{ __('Role') }} <span style="color: var(--danger-600);">*</span>
                </label>
                <select name="role" required
                        style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg);">
                    <option value="staff">{{ __('Staff — can post') }}</option>
                    <option value="moderator">{{ __('Moderator — moderates comments') }}</option>
                    <option value="manager">{{ __('Manager — manages channels & members') }}</option>
                    <option value="organization_admin">{{ __('Organization Admin — full control') }}</option>
                </select>
            </div>

            <div style="background-color: var(--info-50); border: 1px solid var(--info-200); border-radius: var(--radius-md); padding: var(--space-3) var(--space-4); margin-bottom: var(--space-6); display: flex; gap: var(--space-3);">
                <div style="flex-shrink: 0; color: var(--info-600); font-size: var(--text-lg);"><i class="bi bi-info-circle"></i></div>
                <p style="margin: 0; font-size: var(--text-sm); color: var(--info-700);">
                    {{ __('They\'ll get a notification to accept before becoming a member — nothing changes for them until they approve.') }}
                </p>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; background-color: var(--primary-600); color: white; border: 1px solid var(--primary-600); padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); font-size: var(--text-sm); font-weight: var(--font-weight-medium); cursor: pointer;">
                <i class="bi bi-send"></i> {{ __('Send Invitation') }}
            </button>
        </form>
    </x-card-modern>
</div>

<script>
    function switchAddMemberTab(tab) {
        document.getElementById('newUserPanel').style.display = tab === 'new' ? 'block' : 'none';
        document.getElementById('invitePanel').style.display = tab === 'invite' ? 'block' : 'none';
        document.getElementById('tabNewUserBtn').classList.toggle('active', tab === 'new');
        document.getElementById('tabInviteBtn').classList.toggle('active', tab === 'invite');
    }

    (function () {
        const input = document.getElementById('inviteSearchInput');
        const resultsBox = document.getElementById('inviteSearchResults');
        let debounceTimer = null;

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str || '';
            return div.innerHTML;
        }

        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const query = input.value.trim();
            if (query.length < 1) { resultsBox.innerHTML = ''; return; }
            debounceTimer = setTimeout(() => runSearch(query), 300);
        });

        function runSearch(query) {
            fetch('{{ route('api.people.search') }}?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => renderResults(data.users || []))
                .catch(() => renderResults([]));
        }

        function renderResults(users) {
            if (users.length === 0) {
                resultsBox.innerHTML = '<p style="font-size: var(--text-sm); color: var(--text-tertiary); margin-top: var(--space-2);">{{ __('No people found.') }}</p>';
                return;
            }

            resultsBox.innerHTML = users.map(u => `
                <div class="invite-result-row" style="cursor: pointer;" data-select-user='${JSON.stringify(u).replace(/'/g, "&apos;")}'>
                    <div class="invite-result-avatar">${u.avatar_path ? `<img src="${u.avatar_path}" alt="">` : escapeHtml(u.initials)}</div>
                    <div>
                        <div style="font-weight: var(--font-weight-semibold); font-size: var(--text-sm);">${escapeHtml(u.full_name)}</div>
                        <div style="font-size: var(--text-xs); color: var(--text-tertiary);">${u.username ? '@' + escapeHtml(u.username) : ''}</div>
                    </div>
                </div>
            `).join('');

            resultsBox.querySelectorAll('[data-select-user]').forEach(row => {
                row.addEventListener('click', function () {
                    const u = JSON.parse(this.getAttribute('data-select-user').replace(/&apos;/g, "'"));
                    selectInviteUser(u);
                });
            });
        }

        window.selectInviteUser = function (u) {
            document.getElementById('inviteUserId').value = u.id;
            document.getElementById('inviteSelectedName').textContent = u.full_name;
            document.getElementById('inviteSelectedHandle').textContent = u.username ? '@' + u.username : '';
            document.getElementById('inviteSelectedAvatar').innerHTML = u.avatar_path
                ? `<img src="${u.avatar_path}" alt="">` : escapeHtml(u.initials);
            document.getElementById('inviteForm').style.display = 'block';
            document.getElementById('inviteSearchInput').value = '';
            resultsBox.innerHTML = '';
        };

        window.clearInviteSelection = function () {
            document.getElementById('inviteForm').style.display = 'none';
            document.getElementById('inviteUserId').value = '';
        };
    })();
</script>
@endsection
