@extends('layouts.app-modern')

@section('title', 'Channels')

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
        .channel-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-inline-start: 0.5rem;
        }
        .channel-status.active {
            background: #dbeafe;
            color: #1e40af;
        }
        .channel-status.archived {
            background: #f3f4f6;
            color: #6b7280;
        }
        .org-switcher {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            margin-bottom: 1.5rem;
        }
        .org-switcher select {
            border: none;
            font-weight: 600;
            color: var(--text-primary);
            background: transparent;
        }
        .org-switcher select:focus {
            outline: none;
        }
        .channel-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
            margin-top: 1rem;
        }
        .stats-header {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-box {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .stat-box-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-600);
        }
        .stat-box-label {
            font-size: 0.9rem;
            color: var(--text-tertiary);
            margin-top: 0.5rem;
        }
        @media (max-width: 768px) {
            .channel-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            .stats-header {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @if(auth()->user()->hasRole('super_admin'))
        <div class="org-switcher">
            <i class="bi bi-building"></i>
            <span>{{ __('Viewing') }}:</span>
            <form action="{{ route('tenant.switch-organization') }}" method="POST" id="orgSwitcherForm">
                @csrf
                <select name="organization_id" onchange="document.getElementById('orgSwitcherForm').submit()">
                    @foreach(\App\Models\Organization::orderBy('name')->get() as $org)
                        <option value="{{ $org->id }}" @selected($org->id === $organization->id)>{{ $org->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    <div class="page-header mb-4">
        <div class="page-header-top">
            <div class="page-header-info">
                <h1>📺 {{ __('Channels') }} — {{ $organization->name }}</h1>
                <p>Create and manage all channels in your organization</p>
            </div>
            <div class="page-header-actions">
                @if($channels->total() < $organization->max_channels)
                    <a href="{{ route('tenant.channels.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create New Channel
                    </a>
                @else
                    <button class="btn btn-secondary" disabled title="Maximum channels reached">
                        Maximum Reached
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if(session('error'))
        <x-alert-modern type="danger" dismissible>
            {{ session('error') }}
        </x-alert-modern>
    @endif

    @if(session('success'))
        <x-alert-modern type="success" dismissible>
            {{ session('success') }}
        </x-alert-modern>
    @endif

    <!-- Statistics Header -->
    <div class="stats-header">
        <div class="stat-box">
            <div class="stat-box-value">{{ $channels->total() }}/{{ $organization->max_channels }}</div>
            <div class="stat-box-label">Channels Created</div>
        </div>
        <div class="stat-box">
            <div class="stat-box-value">{{ $channels->sum(function($c) { return $c->users()->count(); }) }}</div>
            <div class="stat-box-label">Total Members</div>
        </div>
        <div class="stat-box">
            <div class="stat-box-value">{{ $channels->sum(function($c) { return $c->posts()->count(); }) }}</div>
            <div class="stat-box-label">Total Posts</div>
        </div>
    </div>

    <!-- Channels List -->
    @forelse($channels as $channel)
        <div class="channel-card">
            <div class="channel-header">
                <div class="channel-title">
                    <h4>{{ $channel->name }}</h4>
                    <p class="channel-desc">{{ $channel->description ?? 'No description' }}</p>
                </div>
                <div>
                    <span class="channel-type {{ $channel->type }}">
                        {{ $channel->type === 'public' ? '🌍 Public' : '🔒 Private' }}
                    </span>
                    <span class="channel-status {{ $channel->status }}">
                        {{ $channel->status === 'active' ? __('Active') : __('Paused') }}
                    </span>
                </div>
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
                <a href="{{ route('tenant.channels.show', $channel->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i> View
                </a>
                <a href="{{ route('tenant.channels.edit', $channel->id) }}" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <form action="{{ route('tenant.channels.toggle-status', $channel->id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        @if($channel->status === 'active')
                            <i class="bi bi-pause-circle"></i> Pause
                        @else
                            <i class="bi bi-play-circle"></i> Resume
                        @endif
                    </button>
                </form>
                <form action="{{ route('tenant.channels.destroy', $channel->id) }}" method="POST" style="display: inline;">
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
            <a href="{{ route('tenant.channels.create') }}" class="btn btn-primary" style="width: fit-content; margin: 0 auto;">
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
