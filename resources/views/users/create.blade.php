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
                            <label for="inviteSearchInput">{{ __('Search by name, email or phone number') }}</label>
                            <input type="search" id="inviteSearchInput" autocomplete="off" class="form-control" placeholder="{{ __('Enter at least two characters') }}" aria-controls="inviteSearchResults">
                            <div id="inviteSearchResults" role="status" aria-live="polite" class="invite-search-results"></div>
                        </div>

                        <form method="POST" action="{{ route('organizations.membership.invite-user', $organization) }}" id="inviteForm" style="display: none;">
                            @csrf
                            <input type="hidden" name="user_id" id="inviteUserId">

                            <div class="d-flex align-items-center rounded p-3 mb-3 invite-selected">
                                <span class="avatar avatar-md brround bg-primary mx-2" id="inviteSelectedAvatar"></span>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold" id="inviteSelectedName"></div>
                                    <small class="d-block text-muted" id="inviteSelectedEmail"></small>
                                    <small class="d-block text-muted" id="inviteSelectedMobile"></small>
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

@push('styles')
<style>
.invite-search-results{max-height:380px;overflow:auto;margin-top:10px}.invite-result{display:flex;align-items:center;gap:12px;width:100%;text-align:start;background:var(--ip-surface,#fff);color:var(--ip-ink,#222);border:1px solid var(--ip-border,#e4e8f0);border-radius:12px;padding:12px;margin:8px 0;cursor:pointer}.invite-result:hover,.invite-result:focus{border-color:var(--ip-accent,#5264ec);background:var(--ip-surface-soft,#f8f9fe)}.invite-result-details{min-width:0;overflow-wrap:anywhere}.invite-selected{background:var(--ip-surface-soft,#f8f9fe);border:1px solid var(--ip-border,#e4e8f0)}.invite-result .avatar{flex-shrink:0}
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('inviteSearchInput');
        const resultsBox = document.getElementById('inviteSearchResults');
        const inviteForm = document.getElementById('inviteForm');
        let debounceTimer = null;
        let searchRequest = null;
        let searchSequence = 0;
        const message = text => { resultsBox.replaceChildren(); const line = document.createElement('p'); line.className = 'small text-muted mt-2'; line.textContent = text; resultsBox.append(line); };

        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            if (searchRequest) searchRequest.abort();
            searchSequence++;
            clearInviteSelection();
            const query = input.value.trim();
            if (query.length < 2) { resultsBox.replaceChildren(); return; }
            message(@json(__('Searching...')));
            const sequence = searchSequence;
            debounceTimer = setTimeout(() => runSearch(query, sequence), 300);
        });

        function runSearch(query, sequence) {
            searchRequest = new AbortController();
            fetch(@json(route('organizations.membership.invite-user.search', $organization)) + '?q=' + encodeURIComponent(query), {
                signal: searchRequest.signal,
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            }).then(res => { if (!res.ok) throw new Error('Search failed'); return res.json(); })
                .then(data => { if (sequence === searchSequence) renderResults(data.users || []); })
                .catch(error => { if (error.name !== 'AbortError' && sequence === searchSequence) message(@json(__('Search failed. Please try again.'))); });
        }

        function renderResults(users) {
            resultsBox.replaceChildren();
            if (users.length === 0) {
                message(@json(__('No people found.')));
                return;
            }
            users.forEach(user => {
                const row = document.createElement('button');
                row.type = 'button'; row.className = 'invite-result';
                const avatar = document.createElement('span');
                avatar.className = 'avatar avatar-md brround bg-primary'; avatar.textContent = user.initials || '?';
                const details = document.createElement('span'); details.className = 'invite-result-details';
                const name = document.createElement('strong'); name.className = 'd-block'; name.textContent = user.name || '';
                const email = document.createElement('small'); email.className = 'd-block text-muted'; email.textContent = @json(__('Email')) + ': ' + (user.email || @json(__('Not provided')));
                const phone = document.createElement('small'); phone.className = 'd-block text-muted'; phone.textContent = @json(__('Phone')) + ': ' + (user.mobile || @json(__('Not provided')));
                details.append(name, email, phone); row.append(avatar, details);
                row.addEventListener('click', () => selectInviteUser(user));
                resultsBox.append(row);
            });
        }

        function selectInviteUser(u) {
            document.getElementById('inviteUserId').value = u.id;
            document.getElementById('inviteSelectedName').textContent = u.name || '';
            document.getElementById('inviteSelectedEmail').textContent = @json(__('Email')) + ': ' + (u.email || @json(__('Not provided')));
            document.getElementById('inviteSelectedMobile').textContent = @json(__('Phone')) + ': ' + (u.mobile || @json(__('Not provided')));
            document.getElementById('inviteSelectedAvatar').textContent = u.initials || '?';
            inviteForm.style.display = 'block';
            input.value = '';
            resultsBox.replaceChildren();
        }

        window.clearInviteSelection = function () {
            inviteForm.style.display = 'none';
            document.getElementById('inviteUserId').value = '';
        };
    })();
</script>
@endpush
