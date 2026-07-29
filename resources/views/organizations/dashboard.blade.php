@extends('layouts.app-modern')

@section('content')
<style>
    .org-hero {
        background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
        color: white;
        padding: var(--space-8) var(--space-6);
        border-radius: var(--radius-xl);
        margin-bottom: var(--space-6);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-4);
        flex-wrap: wrap;
    }
    .org-hero h1 { margin: 0 0 4px; font-size: var(--text-2xl); }
    .org-hero p { margin: 0; opacity: 0.9; font-size: var(--text-sm); }
    .org-hero-settings-btn {
        display: inline-flex; align-items: center; gap: var(--space-2);
        background-color: rgba(255,255,255,0.18); color: white; text-decoration: none;
        padding: var(--space-2) var(--space-4); border-radius: var(--radius-md); font-size: var(--text-sm); font-weight: var(--font-weight-medium);
    }
    .org-hero-settings-btn:hover { background-color: rgba(255,255,255,0.28); }

    .dash-grid { display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-bottom: var(--space-6); }
    @media (max-width: 900px) { .dash-grid { grid-template-columns: 1fr; } }

    .list-card .card-body { padding: 0; }
    .list-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: var(--space-4); border-bottom: 1px solid var(--surface-border);
    }
    .list-card-header h5 { margin: 0; font-size: var(--text-base); font-weight: var(--font-weight-bold); color: var(--text-primary); display: flex; align-items: center; gap: var(--space-2); }
    .list-card-header a { font-size: var(--text-xs); color: var(--primary-600); text-decoration: none; font-weight: var(--font-weight-medium); }

    .list-row { padding: var(--space-3) var(--space-4); border-bottom: 1px solid var(--surface-border); }
    .list-row:last-child { border-bottom: none; }
    .list-row:hover { background-color: var(--surface-hover); }
    .list-row-top { display: flex; align-items: flex-start; justify-content: space-between; gap: var(--space-2); margin-bottom: 4px; }
    .list-row-title { font-size: var(--text-sm); font-weight: var(--font-weight-medium); color: var(--text-primary); text-decoration: none; }
    .list-row-title:hover { color: var(--primary-600); }
    .list-row-meta { font-size: var(--text-xs); color: var(--text-tertiary); }

    .pill { display: inline-flex; align-items: center; padding: 2px 10px; border-radius: var(--radius-full); font-size: 10px; font-weight: var(--font-weight-bold); white-space: nowrap; }
    .pill-secondary { background-color: var(--surface-hover); color: var(--text-tertiary); }
    .pill-success { background-color: var(--success-50); color: var(--success-700); }
    .pill-warning { background-color: var(--warning-50, #fffbeb); color: var(--warning-700, #b45309); }
    .pill-danger { background-color: var(--danger-50); color: var(--danger-700); }
    .pill-info { background-color: var(--info-50, #eff6ff); color: var(--info-700, #1d4ed8); }

    .empty-row { text-align: center; padding: var(--space-8) var(--space-4); color: var(--text-tertiary); font-size: var(--text-sm); }
    .empty-row i { font-size: 1.75rem; display: block; margin-bottom: var(--space-2); opacity: 0.5; }

    .action-btn {
        display: flex; align-items: center; gap: var(--space-3);
        padding: var(--space-3) var(--space-4);
        text-decoration: none; color: var(--text-primary);
        background-color: var(--surface-bg); border: 1px solid var(--surface-border);
        border-radius: var(--radius-md); font-size: var(--text-sm); font-weight: var(--font-weight-medium);
        transition: all var(--transition-fast);
    }
    .action-btn:hover { background-color: var(--primary-50); border-color: var(--primary-600); color: var(--primary-600); }
    .action-btn i { font-size: var(--text-lg); }

    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); padding: var(--space-4); }
    .info-grid-item p:first-child { margin: 0 0 4px; font-size: var(--text-xs); color: var(--text-tertiary); }
    .info-grid-item p:last-child { margin: 0; font-size: var(--text-xl); font-weight: var(--font-weight-bold); color: var(--text-primary); }
</style>

<!-- Header -->
<div class="org-hero">
    <div>
        <h1>{{ $organization->name }}</h1>
        <p>{{ $organization->email ?? __('No email set') }}</p>
    </div>
    <a href="{{ route('organizations.settings', $organization) }}" class="org-hero-settings-btn">
        <i class="bi bi-gear"></i> {{ __('Settings') }}
    </a>
</div>

<!-- Key Statistics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-6);">
    <x-stat-card title="{{ __('Posts') }}" value="{{ $organization->posts()->count() }}" icon="file-text" />
    <x-stat-card title="{{ __('Open Tickets') }}" value="{{ $organization->tickets()->where('status', '!=', 'closed')->count() }}" icon="ticket-perforated" />
    <x-stat-card title="{{ __('Team Members') }}" value="{{ $organization->users()->count() }}" icon="people" />
    <x-stat-card title="{{ __('Locations') }}" value="{{ $organization->locations()->count() }}" icon="geo-alt" />
</div>

<!-- Recent Posts / Open Tickets -->
<div class="dash-grid">
    <x-card-modern class="list-card">
        <div class="list-card-header">
            <h5><i class="bi bi-file-text"></i> {{ __('Recent Posts') }}</h5>
            <a href="{{ route('posts.index') }}">{{ __('View All') }} →</a>
        </div>
        @forelse($organization->posts()->latest()->take(5)->get() as $post)
            <div class="list-row">
                <div class="list-row-top">
                    <a href="{{ route('posts.show', $post) }}" class="list-row-title">{{ Str::limit($post->title, 45) }}</a>
                    <span class="pill {{ match($post->status) {
                        'draft' => 'pill-secondary',
                        'published' => 'pill-success',
                        'pending_approval' => 'pill-warning',
                        default => 'pill-info',
                    } }}">{{ str_replace('_', ' ', ucfirst($post->status)) }}</span>
                </div>
                <div class="list-row-meta">{{ $post->created_at->format('M d, Y · h:i A') }}</div>
            </div>
        @empty
            <div class="empty-row"><i class="bi bi-inbox"></i>{{ __('No posts yet') }}</div>
        @endforelse
    </x-card-modern>

    <x-card-modern class="list-card">
        <div class="list-card-header">
            <h5><i class="bi bi-ticket-perforated"></i> {{ __('Open Tickets') }}</h5>
            <a href="{{ route('tickets.index') }}">{{ __('View All') }} →</a>
        </div>
        @forelse($organization->tickets()->where('status', '!=', 'closed')->latest()->take(5)->get() as $ticket)
            <div class="list-row">
                <div class="list-row-top">
                    <a href="{{ route('tickets.show', $ticket) }}" class="list-row-title">
                        <code style="font-size: var(--text-xs);">{{ $ticket->ticket_number }}</code>
                        {{ Str::limit($ticket->title, 30) }}
                    </a>
                    <span class="pill {{ match($ticket->priority) {
                        'urgent' => 'pill-danger',
                        'high' => 'pill-warning',
                        'medium' => 'pill-info',
                        default => 'pill-secondary',
                    } }}">{{ ucfirst($ticket->priority) }}</span>
                </div>
                <div class="list-row-meta">{{ $ticket->opened_at?->format('M d, Y') ?? __('Recently') }}</div>
            </div>
        @empty
            <div class="empty-row"><i class="bi bi-check-circle"></i>{{ __('No open tickets') }}</div>
        @endforelse
    </x-card-modern>
</div>

<!-- Quick Actions / Organization Info -->
<div class="dash-grid" style="margin-bottom: 0;">
    <x-card-modern title="{{ __('Quick Actions') }}" icon="lightning">
        <div style="display: grid; gap: var(--space-2);">
            <a href="{{ route('posts.create') }}" class="action-btn"><i class="bi bi-pencil-square"></i> {{ __('Create Post') }}</a>
            <a href="{{ route('tickets.create') }}" class="action-btn"><i class="bi bi-plus-circle"></i> {{ __('Create Ticket') }}</a>
            <a href="{{ route('audience-segments.create') }}" class="action-btn"><i class="bi bi-diagram-3"></i> {{ __('Create Audience') }}</a>
            <a href="{{ route('organizations.settings', $organization) }}" class="action-btn"><i class="bi bi-people-fill"></i> {{ __('Manage Team') }}</a>
        </div>
    </x-card-modern>

    <x-card-modern class="list-card" title="{{ __('Organization Info') }}" icon="info-circle">
        <div class="info-grid">
            <div class="info-grid-item"><p>{{ __('Brands') }}</p><p>{{ $organization->brands()->count() }}</p></div>
            <div class="info-grid-item"><p>{{ __('Channels') }}</p><p>{{ $organization->channels()->count() }}</p></div>
            <div class="info-grid-item"><p>{{ __('Audience Segments') }}</p><p>{{ $organization->audienceSegments()->count() }}</p></div>
            <div class="info-grid-item"><p>{{ __('All Tickets') }}</p><p>{{ $organization->tickets()->count() }}</p></div>
        </div>
    </x-card-modern>
</div>
@endsection
