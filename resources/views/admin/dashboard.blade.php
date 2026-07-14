@extends('layouts.app-modern')

@section('content')
<style>
    .stat-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }

    .stat-card-icon {
        width: 56px;
        height: 56px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .stat-card-icon.primary { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .stat-card-icon.success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
    .stat-card-icon.warning { background: rgba(247, 144, 9, 0.1); color: #f79009; }
    .stat-card-icon.danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .stat-card-icon.info { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

    .header-section {
        background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
        color: white;
        padding: 3rem 1.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
    }

    .header-section h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .header-section p {
        opacity: 0.9;
        margin-bottom: 0;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        text-decoration: none;
        color: var(--text-primary);
        background: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: 8px;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .action-btn:hover {
        background: var(--primary-50);
        border-color: var(--primary-600);
        color: var(--primary-600);
    }

    .recent-item {
        padding: 1rem;
        border-bottom: 1px solid var(--surface-border);
        transition: background 0.3s ease;
    }

    .recent-item:hover {
        background: var(--surface-bg-secondary);
    }

    .recent-item:last-child {
        border-bottom: none;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--text-tertiary);
        font-weight: 500;
    }

    .org-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        flex-shrink: 0;
    }
</style>

@php
    $avatarPalette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777'];
    $colorFor = fn($seed) => $avatarPalette[crc32($seed) % count($avatarPalette)];

    $totalOrganizations = \App\Models\Organization::count();
    $activeOrganizations = \App\Models\Organization::where('status', 'active')->count();
    $suspendedOrganizations = \App\Models\Organization::where('status', 'suspended')->count();
    $totalUsers = \App\Models\User::count();
    $totalPosts = \App\Models\Post::count();
    $publishedPosts = \App\Models\Post::where('status', 'published')->count();
    $openTickets = \App\Models\Ticket::where('status', '!=', 'closed')->count();
    $totalChannels = \App\Models\Channel::count();
@endphp

<div class="container-lg py-4">
    <!-- Header -->
    <div class="header-section">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1>{{ __('Super Admin Dashboard') }}</h1>
                <p>{{ __('Welcome back,') }} {{ auth()->user()->full_name }} — {{ __('platform-wide overview') }}</p>
            </div>
            <a href="{{ route('admin.organizations.index') }}" class="btn btn-light">
                <i class="bi bi-building"></i> {{ __('Manage Organizations') }}
            </a>
        </div>
    </div>

    <!-- Key Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon primary">
                        <i class="bi bi-building"></i>
                    </div>
                    <div>
                        <p class="stat-label">{{ __('Organizations') }}</p>
                        <p class="stat-value">{{ $totalOrganizations }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon success">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <p class="stat-label">{{ __('Total Users') }}</p>
                        <p class="stat-value">{{ $totalUsers }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon info">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <div>
                        <p class="stat-label">{{ __('Total Posts') }}</p>
                        <p class="stat-value">{{ $totalPosts }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon warning">
                        <i class="bi bi-ticket"></i>
                    </div>
                    <div>
                        <p class="stat-label">{{ __('Open Tickets') }}</p>
                        <p class="stat-value">{{ $openTickets }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <p class="stat-label">{{ __('Active Organizations') }}</p>
                        <p class="stat-value">{{ $activeOrganizations }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon danger">
                        <i class="bi bi-pause-circle"></i>
                    </div>
                    <div>
                        <p class="stat-label">{{ __('Suspended') }}</p>
                        <p class="stat-value">{{ $suspendedOrganizations }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon primary">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div>
                        <p class="stat-label">{{ __('Total Channels') }}</p>
                        <p class="stat-value">{{ $totalChannels }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon info">
                        <i class="bi bi-send-check"></i>
                    </div>
                    <div>
                        <p class="stat-label">{{ __('Published Posts') }}</p>
                        <p class="stat-value">{{ $publishedPosts }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row g-4 mb-4">
        <!-- Recent Organizations -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-building"></i> {{ __('Recent Organizations') }}</h5>
                    <a href="{{ route('admin.organizations.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }} →</a>
                </div>
                <div class="card-body p-0">
                    @forelse(\App\Models\Organization::withCount('users', 'posts', 'channels')->latest()->take(6)->get() as $org)
                        <div class="recent-item d-flex align-items-center gap-3">
                            <div class="org-avatar" style="background-color: {{ $colorFor($org->name) }};">
                                {{ substr($org->name, 0, 1) }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <a href="{{ route('admin.organizations.show', $org) }}" class="text-decoration-none fw-500">
                                        {{ $org->name }}
                                    </a>
                                    <span class="badge ms-2 bg-{{ match($org->status) {
                                        'active' => 'success',
                                        'suspended' => 'warning',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    } }}">
                                        {{ ucfirst($org->status) }}
                                    </span>
                                </div>
                                <small class="text-muted">
                                    {{ $org->users_count }} {{ __('users') }} · {{ $org->channels_count }} {{ __('channels') }} · {{ $org->posts_count }} {{ __('posts') }}
                                </small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                            <p class="mt-2">{{ __('No organizations yet') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> {{ __('Quick Actions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.organizations.create') }}" class="action-btn">
                            <i class="bi bi-plus-circle"></i> {{ __('Create Organization') }}
                        </a>
                        <a href="{{ route('admin.organizations.index') }}" class="action-btn">
                            <i class="bi bi-building"></i> {{ __('Manage Organizations') }}
                        </a>
                        <a href="{{ route('posts.index') }}" class="action-btn">
                            <i class="bi bi-file-text"></i> {{ __('Manage Posts') }}
                        </a>
                        <a href="{{ route('tickets.index') }}" class="action-btn">
                            <i class="bi bi-ticket"></i> {{ __('Manage Tickets') }}
                        </a>
                        <a href="{{ route('audience-segments.index') }}" class="action-btn">
                            <i class="bi bi-people"></i> {{ __('Manage Audience Segments') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Open Tickets -->
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-ticket"></i> {{ __('Recent Open Tickets') }}</h5>
                    <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }} →</a>
                </div>
                <div class="card-body p-0">
                    @forelse(\App\Models\Ticket::where('status', '!=', 'closed')->with('organization')->latest()->take(5)->get() as $ticket)
                        <div class="recent-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-500">
                                    <code>{{ $ticket->ticket_number }}</code>
                                    <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none ms-2">
                                        {{ Str::limit($ticket->title, 40) }}
                                    </a>
                                </div>
                                <small class="text-muted">{{ $ticket->organization->name ?? __('Unknown org') }} · {{ $ticket->opened_at?->format('M d, Y') ?? __('Recently') }}</small>
                            </div>
                            <span class="badge bg-{{ match($ticket->priority) {
                                'urgent' => 'danger',
                                'high' => 'warning',
                                'medium' => 'info',
                                'low' => 'secondary',
                                default => 'secondary'
                            } }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-check-circle" style="font-size: 2rem; opacity: 0.5;"></i>
                            <p class="mt-2">{{ __('No open tickets') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
