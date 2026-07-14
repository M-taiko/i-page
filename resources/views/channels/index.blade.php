@extends('layouts.app-modern')

@section('title', 'View Channels')

@section('content')
    <style>
        .channel-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 1.5rem;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        .channel-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border-color: var(--primary-600);
        }
        .channel-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
            gap: 1rem;
        }
        .channel-title h4 {
            margin: 0 0 0.25rem 0;
            font-weight: 700;
            color: var(--text-primary);
            font-size: 1.1rem;
        }
        .channel-desc {
            font-size: 0.9rem;
            color: var(--text-tertiary);
            margin: 0;
        }
        .channel-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 1rem 0;
            padding: 1rem 0;
            border-top: 1px solid #f3f4f6;
            border-bottom: 1px solid #f3f4f6;
        }
        .channel-stat {
            text-align: center;
        }
        .channel-stat-value {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-600);
        }
        .channel-stat-label {
            font-size: 0.8rem;
            color: var(--text-tertiary);
            text-transform: uppercase;
            margin-top: 0.25rem;
        }
        .channel-type {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .channel-type.public {
            background: #d1fae5;
            color: #065f46;
        }
        .channel-type.private {
            background: #fce7f3;
            color: #be185d;
        }
        .channel-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
            margin-top: 1rem;
        }
        @media (max-width: 768px) {
            .channel-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

    <div class="page-header mb-4">
        <div class="page-header-top">
            <div class="page-header-info">
                <h1>📺 All Channels</h1>
                <p>View and manage all channels in your organization</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('dashboard.channels.create', $organization) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create New Channel
                </a>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div style="margin-bottom: 2rem;">
        <form method="GET" action="{{ route('dashboard.channels.index', $organization) }}" style="display: flex; gap: var(--space-3);">
            <input type="text" name="q" placeholder="Search channels by name..." value="{{ request('q') }}" style="flex: 1; padding: var(--space-3) var(--space-4); border: 1px solid var(--surface-border); border-radius: var(--radius-lg); font-size: var(--text-sm); background-color: var(--surface-bg); color: var(--text-primary);">
            <select name="type" style="padding: var(--space-3) var(--space-4); border: 1px solid var(--surface-border); border-radius: var(--radius-lg); font-size: var(--text-sm); background-color: var(--surface-bg); color: var(--text-primary);">
                <option value="">All Types</option>
                <option value="public" {{ request('type') === 'public' ? 'selected' : '' }}>Public Only</option>
                <option value="private" {{ request('type') === 'private' ? 'selected' : '' }}>Private Only</option>
            </select>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Search
            </button>
        </form>
    </div>

    @if(session('success'))
        <x-alert-modern type="success" dismissible>
            {{ session('success') }}
        </x-alert-modern>
    @endif

    <!-- Channels List -->
    @forelse($channels as $channel)
        <div class="channel-card">
            <div class="channel-header">
                <div class="channel-title">
                    <h4>{{ $channel->name }}</h4>
                    <p class="channel-desc">{{ $channel->description ?? 'No description' }}</p>
                </div>
                <span class="channel-type {{ $channel->type }}">
                    {{ $channel->type === 'public' ? '🌍 Public' : '🔒 Private' }}
                </span>
            </div>

            <div class="channel-stats">
                <div class="channel-stat">
                    <div class="channel-stat-value">{{ $channel->users()->count() }}</div>
                    <div class="channel-stat-label">Members</div>
                </div>
                <div class="channel-stat">
                    <div class="channel-stat-value">{{ $channel->posts()->count() }}</div>
                    <div class="channel-stat-label">Posts</div>
                </div>
                <div class="channel-stat">
                    <div class="channel-stat-value">{{ $channel->created_at->diffForHumans(parts: 1, syntax: 'short') }}</div>
                    <div class="channel-stat-label">Created</div>
                </div>
            </div>

            <div class="channel-actions">
                <a href="{{ route('dashboard.channels.show', [$organization, $channel->id]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i> View
                </a>
                <a href="{{ route('dashboard.channels.edit', [$organization, $channel->id]) }}" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <form action="{{ route('dashboard.channels.destroy', [$organization, $channel->id]) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this channel?')">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="card text-center py-5">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
            <h4 class="text-muted">No channels yet</h4>
            <p class="text-muted mb-3">Create your first channel now</p>
            <a href="{{ route('dashboard.channels.create', $organization) }}" class="btn btn-primary" style="width: fit-content; margin: 0 auto;">
                <i class="bi bi-plus-circle"></i> Create First Channel
            </a>
        </div>
    @endforelse

    <!-- Pagination -->
    @if($channels->hasPages())
        <nav class="d-flex justify-content-center mt-4">
            {{ $channels->links() }}
        </nav>
    @endif

@endsection
