@extends('layouts.app-modern')

@section('content')
<div class="container-lg py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Super Admin Dashboard</h1>
            <p class="text-muted">Welcome, {{ auth()->user()->full_name }}</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-2">Total Posts</p>
                    <p class="h4 mb-0">{{ \App\Models\Post::count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-2">Total Tickets</p>
                    <p class="h4 mb-0">{{ \App\Models\Ticket::count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-2">Total Organizations</p>
                    <p class="h4 mb-0">{{ \App\Models\Organization::count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-2">Total Users</p>
                    <p class="h4 mb-0">{{ \App\Models\User::count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Quick Links</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('posts.index') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-file-text"></i> Manage Posts
                    </a>
                    <a href="{{ route('tickets.index') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-ticket"></i> Manage Tickets
                    </a>
                    <a href="{{ route('admin.organizations.index') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-building"></i> Manage Organizations
                    </a>
                    <a href="{{ route('audience-segments.index') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-people"></i> Manage Audience Segments
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Recent Organizations</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Users</th>
                                <th>Posts</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\Organization::latest()->take(5)->get() as $org)
                                <tr>
                                    <td>{{ $org->name }}</td>
                                    <td>{{ $org->users()->count() }}</td>
                                    <td>{{ $org->posts()->count() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
