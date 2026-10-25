@extends('layouts.valex')

@section('title', __('Add Team Member'))

@section('page-header')
    <x-valex.page-header :title="__('Add Team Member')" :subtitle="__('Create a brand-new account, or invite someone who already has one.')">
        <x-slot:actions>
            <a href="{{ route('dashboard.users.index', $organization) }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $roleOptions = [
        'staff' => __('Staff — can post'),
        'moderator' => __('Moderator — moderates comments'),
        'manager' => __('Manager — manages channels & members'),
        'organization_admin' => __('Organization Admin — full control'),
    ];
@endphp

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-pills nav-justified mb-4" id="addMemberTabs">
                    <li class="nav-item"><a class="nav-link active" href="#newUserPanel" data-toggle="tab"><i class="fe fe-user-plus"></i> {{ __('New User') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#invitePanel" data-toggle="tab"><i class="fe fe-search"></i> {{ __('Invite Existing User') }}</a></li>
                </ul>

                <div class="tab-content">
                    {{-- New user --}}
                    <div class="tab-pane active" id="newUserPanel">
                        <form method="POST" action="{{ route('dashboard.users.store', $organization) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6"><x-valex.input name="first_name" :label="__('First Name')" required :placeholder="__('Enter first name')" /></div>
                                <div class="col-md-6"><x-valex.input name="last_name" :label="__('Last Name')" required :placeholder="__('Enter last name')" /></div>
                            </div>
                            <x-valex.input name="email" type="email" :label="__('Email Address')" required placeholder="example@organization.com" />
                            <x-valex.input name="mobile" type="tel" :label="__('Mobile Number')" :placeholder="__('(Optional) +1 555-123-4567')" />
                            <x-valex.select name="role" :label="__('Role')" required :options="$roleOptions" />
                            <x-valex.input name="password" :label="__('Temporary Password')" :placeholder="__('(Optional) leave blank to auto-generate')" />

                            <div class="alert alert-info">{{ __('This creates a brand-new account and adds them as an active member immediately.') }}</div>

                            <button type="submit" class="btn btn-primary"><i class="fe fe-check"></i> {{ __('Add Member') }}</button>
                            <a href="{{ route('dashboard.users.index', $organization) }}" class="btn btn-light">{{ __('Cancel') }}</a>
                        </form>
                    </div>

                    {{-- Invite existing user --}}
                    <div class="tab-pane" id="invitePanel">
                        <div class="form-group">
                            <label for="inviteSearchInput">{{ __('Search by name or @username') }}</label>
                            <input type="text" id="inviteSearchInput" autocomplete="off" class="form-control" placeholder="{{ __('e.g. John or @john.doe') }}">
                            <div id="inviteSearchResults"></div>
                        </div>

                        <form method="POST" action="{{ route('organizations.membership.invite-user', $organization) }}" id="inviteForm" style="display: none;">
                            @csrf
                            <input type="hidden" name="user_id" id="inviteUserId">

                            <div class="d-flex align-items-center bg-light rounded p-3 mb-3">
                                <span class="avatar avatar-md brround bg-primary mx-2" id="inviteSelectedAvatar"></span>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold" id="inviteSelectedName"></div>
                                    <small class="text-muted" id="inviteSelectedHandle"></small>
                                </div>
                                <button type="button" class="close" onclick="clearInviteSelection()" aria-label="{{ __('Clear') }}"><span aria-hidden="true">&times;</span></button>
                            </div>

                            <x-valex.select name="role" :label="__('Role')" required :options="$roleOptions" id="inviteRole" />

                            <div class="alert alert-info">{{ __('They\'ll get a notification to accept before becoming a member — nothing changes for them until they approve.') }}</div>

                            <button type="submit" class="btn btn-primary btn-block"><i class="fe fe-send"></i> {{ __('Send Invitation') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
                resultsBox.innerHTML = '<p class="small text-muted mt-2">{{ __('No people found.') }}</p>';
                return;
            }

            resultsBox.innerHTML = users.map(u => `
                <div class="d-flex align-items-center border rounded p-2 mt-2" style="cursor: pointer;" data-select-user='${JSON.stringify(u).replace(/'/g, "&apos;")}'>
                    <span class="avatar avatar-md brround bg-primary mx-2">${u.avatar_path ? `<img src="${u.avatar_path}" alt="">` : escapeHtml(u.initials)}</span>
                    <div>
                        <div class="font-weight-bold">${escapeHtml(u.full_name)}</div>
                        <small class="text-muted">${u.username ? '@' + escapeHtml(u.username) : ''}</small>
                    </div>
                </div>
            `).join('');

            resultsBox.querySelectorAll('[data-select-user]').forEach(row => {
                row.addEventListener('click', function () {
                    selectInviteUser(JSON.parse(this.getAttribute('data-select-user').replace(/&apos;/g, "'")));
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
            input.value = '';
            resultsBox.innerHTML = '';
        };

        window.clearInviteSelection = function () {
            document.getElementById('inviteForm').style.display = 'none';
            document.getElementById('inviteUserId').value = '';
        };
    })();
</script>
@endpush
