@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">{{ $group->name }}</h1>
        <div>
            <a href="{{ route('dashboard.groups.edit', $group) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('dashboard.groups.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <x-card class="mb-4">
                <x-slot:header>
                    <h5 class="mb-0">Group Details</h5>
                </x-slot:header>

                <div class="mb-3">
                    <strong>Name</strong>
                    <p class="text-muted">{{ $group->name }}</p>
                </div>

                @if ($group->description)
                    <div class="mb-3">
                        <strong>Description</strong>
                        <p class="text-muted">{{ $group->description }}</p>
                    </div>
                @endif

                <div class="mb-3">
                    <strong>Branch</strong>
                    <p class="text-muted">
                        @if ($group->branch)
                            <span class="badge bg-info">{{ $group->branch->name }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </p>
                </div>

                <div class="mb-3">
                    <strong>Created</strong>
                    <p class="text-muted">{{ $group->created_at->format('M d, Y H:i') }}</p>
                </div>
            </x-card>

            <x-card>
                <x-slot:header>
                    <h5 class="mb-0">Members ({{ $group->users->count() }})</h5>
                </x-slot:header>

                @if ($group->users->count() > 0)
                    <div class="list-group">
                        @foreach ($group->users as $user)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $user->name }}</strong>
                                    <small class="d-block text-muted">{{ $user->email }}</small>
                                </div>
                                <span class="badge bg-secondary">{{ $user->roles()->first()?->name ?? 'No Role' }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No members in this group yet.</p>
                @endif
            </x-card>
        </div>

        <div class="col-lg-4">
            <x-card>
                <x-slot:header>
                    <h5 class="mb-0">Actions</h5>
                </x-slot:header>

                <a href="{{ route('dashboard.groups.edit', $group) }}" class="btn btn-warning w-100 mb-2">
                    <i class="bi bi-pencil"></i> Edit Group
                </a>

                <form action="{{ route('dashboard.groups.destroy', $group) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure? This action cannot be undone.')">
                        <i class="bi bi-trash"></i> Delete Group
                    </button>
                </form>
            </x-card>
        </div>
    </div>
</div>
@endsection
