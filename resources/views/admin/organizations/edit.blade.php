@extends('layouts.valex')

@section('title', __('Edit') . ' ' . $organization->name)

@section('page-header')
    <x-valex.page-header :title="__('Edit Organization')" :subtitle="$organization->name">
        <x-slot:actions>
            <a href="{{ route('admin.organizations.show', $organization->id) }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<form action="{{ route('admin.organizations.update', $organization->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <x-valex.card :title="__('Organization Information')">
                <x-valex.input name="name" :label="__('Organization Name')" :value="$organization->name" required
                    :help="__('The official name of the organization')" />
                <x-valex.textarea name="description" :label="__('Description')" rows="5" :value="$organization->description"
                    :help="__('A short description to help identify the organization')" />
            </x-valex.card>

            <x-valex.card :title="__('Location')">
                <div class="row">
                    <div class="col-md-6"><x-valex.input name="city" :label="__('City')" :value="$organization->city" /></div>
                    <div class="col-md-6"><x-valex.input name="country" :label="__('Country')" :value="$organization->country" /></div>
                </div>
            </x-valex.card>
        </div>

        <div class="col-lg-4">
            <x-valex.card :title="__('Settings')">
                <x-valex.input name="max_channels" type="number" :label="__('Max Channels')" :value="$organization->max_channels" min="1" max="1000" required
                    :help="$organization->channels()->count() . ' ' . __('active')" />
                <x-valex.switch name="is_active" :label="__('Active')" :checked="$organization->is_active" :help="__('Allow users to access')" />
            </x-valex.card>
        </div>
    </div>

    <div class="alert alert-info">
        <strong>{{ __('Note:') }}</strong> {{ __('All changes will be applied immediately. Make sure the data is correct before saving.') }}
    </div>

    <div class="mb-4">
        <button type="submit" class="btn btn-primary"><i class="fe fe-check"></i> {{ __('Save Changes') }}</button>
        <a href="{{ route('admin.organizations.show', $organization->id) }}" class="btn btn-light">{{ __('Cancel') }}</a>
    </div>
</form>

{{-- Public page design (ADR-012) --}}
<x-valex.card :title="__('Public Page Design')" :subtitle="__('How visitors and subscribers see this organization')">
    @include('organizations._design-form')
</x-valex.card>

{{-- Team & Permissions --}}
@php
    $showAddMemberForm = $members->isEmpty() || $errors->hasAny(['first_name', 'last_name', 'email', 'password', 'role']);
@endphp
<x-valex.card :title="__('Admin & Permissions')" :subtitle="__('Set each member\'s role and what they\'re allowed to do beyond it.')">
    <x-slot:actions>
        <button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#addMemberForm">
            <i class="fe fe-plus"></i> {{ __('Add Member') }}
        </button>
    </x-slot:actions>

    <div id="addMemberForm" class="collapse {{ $showAddMemberForm ? 'show' : '' }}">
        <div class="border rounded p-3 mb-4 bg-light">
            <form action="{{ route('admin.organizations.members.store', $organization->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4"><x-valex.input name="first_name" :label="__('First Name')" required /></div>
                    <div class="col-md-4"><x-valex.input name="last_name" :label="__('Last Name')" required /></div>
                    <div class="col-md-4"><x-valex.input name="email" type="email" :label="__('Email')" required placeholder="admin@example.com" /></div>
                    <div class="col-md-4"><x-valex.input name="password" :label="__('Password')" required minlength="8" :placeholder="__('At least 8 characters')" /></div>
                    <div class="col-md-4">
                        <x-valex.select name="role" :label="__('Role')" required
                            :options="collect($assignableRoles)->mapWithKeys(fn ($r) => [$r => ucfirst(str_replace('_', ' ', $r))])->all()" />
                    </div>
                </div>
                <p class="text-muted small"><i class="fe fe-info"></i> {{ __('This always creates a brand-new account — the email must not already be registered.') }}</p>
                <button type="submit" class="btn btn-primary"><i class="fe fe-check"></i> {{ __('Create & Add to Organization') }}</button>
            </form>
        </div>
    </div>

    @forelse ($members as $member)
        @php
            $memberRole = $member->pivot->role;
            $memberDirectPermissions = $member->getDirectPermissions()->pluck('name');
        @endphp
        <div class="member-permission-block border rounded p-3 mb-3">
            <form action="{{ route('admin.organizations.members.permissions', [$organization->id, $member->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                    <div class="d-flex align-items-center">
                        <span class="avatar avatar-md brround bg-primary mx-2">{{ $member->initials ?? mb_substr($member->full_name, 0, 1) }}</span>
                        <div>
                            <div class="font-weight-bold">{{ $member->full_name }}</div>
                            <div class="small text-muted">{{ $member->email }}</div>
                        </div>
                    </div>
                    <select name="role" class="form-control member-role-select w-auto">
                        @foreach ($assignableRoles as $roleOption)
                            <option value="{{ $roleOption }}" @selected($memberRole === $roleOption)>{{ ucfirst(str_replace('_', ' ', $roleOption)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="small text-muted text-uppercase font-weight-bold mb-2">{{ __('What they can do') }}</div>
                <div class="row mb-3">
                    @foreach ($allPermissions as $permission)
                        <div class="col-md-6 col-xl-4 permission-checkbox" data-permission="{{ $permission }}">
                            <label class="ckbox">
                                <input type="checkbox" class="permission-input" name="permissions[]" value="{{ $permission }}" @checked($memberDirectPermissions->contains($permission))>
                                <span>{{ ucfirst(str_replace('.', ' — ', $permission)) }}</span>
                            </label>
                            <span class="permission-role-tag badge badge-light" style="display:none">{{ __('included in role') }}</span>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-outline-primary"><i class="fe fe-check"></i> {{ __('Save role & permissions') }}</button>
            </form>

            <form action="{{ route('admin.organizations.members.destroy', [$organization->id, $member->id]) }}" method="POST" class="d-inline-block mt-2"
                  onsubmit="return confirm('{{ __('Remove this member from the organization? Their account is kept but they lose access here.') }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="fe fe-trash-2"></i> {{ __('Remove from Organization') }}</button>
            </form>
        </div>
    @empty
        <p class="text-muted text-center py-4 mb-0">{{ __('No members yet.') }}</p>
    @endforelse
</x-valex.card>
@endsection

@push('scripts')
<script>
    const ROLE_PERMISSIONS = {!! $rolePermissions->toJson() !!};

    function applyRoleLock(block) {
        const rolePerms = ROLE_PERMISSIONS[block.querySelector('.member-role-select').value] || [];
        block.querySelectorAll('.permission-checkbox').forEach((wrap) => {
            const input = wrap.querySelector('.permission-input');
            const tag = wrap.querySelector('.permission-role-tag');
            const included = rolePerms.includes(wrap.dataset.permission);
            if (included) input.checked = true;
            input.disabled = included;
            tag.style.display = included ? 'inline-block' : 'none';
        });
    }

    document.querySelectorAll('.member-permission-block').forEach((block) => {
        applyRoleLock(block);
        block.querySelector('.member-role-select').addEventListener('change', () => applyRoleLock(block));
    });
</script>
@endpush
