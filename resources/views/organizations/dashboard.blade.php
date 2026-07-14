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

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        color: var(--text-primary);
    }

    .section-title-divider {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .section-title-divider h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .section-title-divider .divider {
        flex: 1;
        height: 1px;
        background: var(--surface-border);
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
</style>

<div class="container-lg py-4">
    <!-- Header -->
    <div class="header-section">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1>{{ $organization->name }}</h1>
                <p>{{ $organization->email ?? 'No email' }}</p>
            </div>
            <a href="{{ route('organizations.settings', $organization) }}" class="btn btn-light">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </div>

    <!-- Key Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon primary">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <div>
                        <p class="stat-label">Posts</p>
                        <p class="stat-value">{{ $organization->posts()->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon success">
                        <i class="bi bi-ticket"></i>
                    </div>
                    <div>
                        <p class="stat-label">Open Tickets</p>
                        <p class="stat-value">{{ $organization->tickets()->where('status', '!=', 'closed')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon warning">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <p class="stat-label">Team Members</p>
                        <p class="stat-value">{{ $organization->users()->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon danger">
                        <i class="bi bi-building"></i>
                    </div>
                    <div>
                        <p class="stat-label">Locations</p>
                        <p class="stat-value">{{ $organization->locations()->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row g-4 mb-4">
        <!-- Recent Posts -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-text"></i> Recent Posts</h5>
                    <a href="{{ route('posts.index') }}" class="btn btn-sm btn-outline-primary">View All →</a>
                </div>
                <div class="card-body p-0">
                    @forelse($organization->posts()->latest()->take(5)->get() as $post)
                        <div class="recent-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <a href="{{ route('posts.show', $post) }}" class="text-decoration-none fw-500">
                                    {{ Str::limit($post->title, 45) }}
                                </a>
                                <span class="badge ms-2 bg-{{ match($post->status) {
                                    'draft' => 'secondary',
                                    'published' => 'success',
                                    'pending_approval' => 'warning',
                                    default => 'info'
                                } }}">
                                    {{ str_replace('_', ' ', ucfirst($post->status)) }}
                                </span>
                            </div>
                            <small class="text-muted">{{ $post->created_at->format('M d, Y · h:i A') }}</small>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                            <p class="mt-2">No posts yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Open Tickets -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-ticket"></i> Open Tickets</h5>
                    <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-primary">View All →</a>
                </div>
                <div class="card-body p-0">
                    @forelse($organization->tickets()->where('status', '!=', 'closed')->latest()->take(5)->get() as $ticket)
                        <div class="recent-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="fw-500">
                                        <code>{{ $ticket->ticket_number }}</code>
                                        <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none ms-2">
                                            {{ Str::limit($ticket->title, 30) }}
                                        </a>
                                    </div>
                                </div>
                                <span class="badge ms-2 bg-{{ match($ticket->priority) {
                                    'urgent' => 'danger',
                                    'high' => 'warning',
                                    'medium' => 'info',
                                    'low' => 'secondary',
                                    default => 'secondary'
                                } }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </div>
                            <small class="text-muted">{{ $ticket->opened_at?->format('M d, Y') ?? 'Recently' }}</small>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-check-circle" style="font-size: 2rem; opacity: 0.5;"></i>
                            <p class="mt-2">No open tickets</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section -->
    <div class="row g-4">
        <!-- Quick Actions -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('posts.create') }}" class="action-btn">
                            <i class="bi bi-pencil-square"></i> Create Post
                        </a>
                        <a href="{{ route('tickets.create') }}" class="action-btn">
                            <i class="bi bi-plus-circle"></i> Create Ticket
                        </a>
                        <a href="{{ route('audience-segments.create') }}" class="action-btn">
                            <i class="bi bi-diagram-3"></i> Create Audience
                        </a>
                        <a href="{{ route('organizations.settings', $organization) }}" class="action-btn">
                            <i class="bi bi-people-fill"></i> Manage Team
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Organization Info -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Organization Info</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <p class="text-muted small">Brands</p>
                            <p class="h5 mb-0">{{ $organization->brands()->count() }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small">Channels</p>
                            <p class="h5 mb-0">{{ $organization->channels()->count() }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small">Audience Segments</p>
                            <p class="h5 mb-0">{{ $organization->audienceSegments()->count() }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small">All Tickets</p>
                            <p class="h5 mb-0">{{ $organization->tickets()->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
