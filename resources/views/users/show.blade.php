@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $user->name }}</h1>
            <small class="text-muted">{{ $user->ipage_id }}</small>
        </div>
        <div>
            <a href="{{ route('dashboard.users.edit', $user) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('dashboard.users.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <x-card class="mb-4">
                <x-slot:header>
                    <h5 class="mb-0">Profile Information</h5>
                </x-slot:header>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>First Name</strong>
                        <p class="text-muted">{{ $user->first_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Last Name</strong>
                        <p class="text-muted">{{ $user->last_name }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Email</strong>
                        <p class="text-muted">{{ $user->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Mobile</strong>
                        <p class="text-muted">{{ $user->mobile ?? '—' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Gender</strong>
                        <p class="text-muted">{{ $user->gender ? ucfirst($user->gender) : '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Nationality</strong>
                        <p class="text-muted">{{ $user->nationality ?? '—' }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <strong>Status</strong>
                        <p class="text-muted">
                            @if ($user->is_active)
                                <x-badge color="success">Active</x-badge>
                            @else
                                <x-badge color="danger">Inactive</x-badge>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Member Since</strong>
                        <p class="text-muted">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </x-card>

            <x-card class="mb-4">
                <x-slot:header>
                    <h5 class="mb-0">Role & Permissions</h5>
                </x-slot:header>

                <div class="mb-3">
                    <strong>Role</strong>
                    <p class="text-muted">
                        @if ($user->roles()->first())
                            <x-badge color="primary">{{ $user->roles()->first()->name }}</x-badge>
                        @else
                            <span class="text-muted">No role assigned</span>
                        @endif
                    </p>
                </div>

                @if ($user->roles()->first())
                    <div>
                        <strong>Permissions</strong>
                        <div class="mt-2">
                            @forelse ($user->roles()->first()->permissions as $permission)
                                <span class="badge bg-light text-dark mb-1">{{ $permission->name }}</span>
                            @empty
                                <span class="text-muted">No permissions</span>
                            @endforelse
                        </div>
                    </div>
                @endif
            </x-card>

            @if ($user->groups->count() > 0)
                <x-card class="mb-4">
                    <x-slot:header>
                        <h5 class="mb-0">Groups ({{ $user->groups->count() }})</h5>
                    </x-slot:header>

                    <div class="list-group">
                        @foreach ($user->groups as $group)
                            <a href="{{ route('dashboard.groups.show', $group) }}" class="list-group-item list-group-item-action">
                                <strong>{{ $group->name }}</strong>
                                @if ($group->branch)
                                    <small class="d-block text-muted">{{ $group->branch->name }}</small>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </x-card>
            @endif

            @if ($user->channels->count() > 0)
                <x-card>
                    <x-slot:header>
                        <h5 class="mb-0">Channels ({{ $user->channels->count() }})</h5>
                    </x-slot:header>

                    <div class="list-group">
                        @foreach ($user->channels as $channel)
                            <a href="{{ route('dashboard.channels.show', $channel) }}" class="list-group-item list-group-item-action">
                                <strong>{{ $channel->name }}</strong>
                                <small class="d-block text-muted">{{ ucfirst($channel->type) }} Channel</small>
                            </a>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>

        <div class="col-lg-4">
            <x-card class="mb-4">
                <x-slot:header>
                    <h5 class="mb-0">Actions</h5>
                </x-slot:header>

                <a href="{{ route('dashboard.users.edit', $user) }}" class="btn btn-warning w-100 mb-2">
                    <i class="bi bi-pencil"></i> Edit User
                </a>

                <form action="{{ route('dashboard.users.destroy', $user) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure? This action cannot be undone.')">
                        <i class="bi bi-trash"></i> Delete User
                    </button>
                </form>
            </x-card>

            <x-card>
                <x-slot:header>
                    <h5 class="mb-0">System Info</h5>
                </x-slot:header>

                <small class="text-muted d-block mb-2">
                    <strong>Last Seen:</strong><br>
                    {{ $user->last_seen_at?->diffForHumans() ?? 'Never' }}
                </small>

                <small class="text-muted d-block mb-2">
                    <strong>Email Verified:</strong><br>
                    {{ $user->email_verified_at ? 'Yes' : 'No' }}
                </small>

                <small class="text-muted d-block">
                    <strong>Created:</strong><br>
                    {{ $user->created_at->format('M d, Y H:i') }}
                </small>
            </x-card>
        </div>
    </div>
</div>
@endsection
