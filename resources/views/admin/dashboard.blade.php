@extends('layouts.app-modern')

@section('content')
<style>
    .dash-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: var(--space-4);
        padding-bottom: var(--space-5);
        margin-bottom: var(--space-6);
        border-bottom: 1px solid var(--surface-border);
    }

    .dash-header-eyebrow {
        font-size: var(--text-xs);
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--primary-600);
        margin-bottom: 4px;
    }

    .dash-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .dash-header p {
        color: var(--text-tertiary);
        font-size: var(--text-sm);
        margin: 4px 0 0;
    }

    /* KPI cards — left accent border, no heavy shadow */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: var(--space-4);
        margin-bottom: var(--space-6);
    }

    @media (max-width: 992px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 576px) { .kpi-grid { grid-template-columns: 1fr; } }

    .kpi-card {
        background: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-inline-start: 3px solid var(--kpi-accent, var(--primary-600));
        border-radius: 10px;
        padding: var(--space-4) var(--space-5);
        display: flex;
        flex-direction: column;
        gap: var(--space-2);
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .kpi-card:hover { box-shadow: 0 6px 20px rgba(17, 24, 39, 0.06); transform: translateY(-1px); }

    .kpi-card-top { display: flex; align-items: center; justify-content: space-between; }

    .kpi-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--text-base);
        background: var(--kpi-icon-bg, var(--primary-50));
        color: var(--kpi-accent, var(--primary-600));
        flex-shrink: 0;
    }

    .kpi-value { font-size: 1.9rem; font-weight: 700; color: var(--text-primary); line-height: 1; }
    .kpi-label { font-size: var(--text-xs); font-weight: 600; color: var(--text-tertiary); text-transform: uppercase; letter-spacing: 0.03em; }
    .kpi-sub { font-size: var(--text-xs); color: var(--text-tertiary); }

    /* Panels */
    .panel {
        background: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: 12px;
        overflow: hidden;
    }

    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--space-4) var(--space-5);
        border-bottom: 1px solid var(--surface-border);
    }

    .panel-header h2 {
        font-size: var(--text-sm);
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }

    .panel-header h2 i { color: var(--primary-600); }

    .panel-link { font-size: var(--text-xs); font-weight: 600; color: var(--primary-600); text-decoration: none; }
    .panel-link:hover { text-decoration: underline; }

    .dash-grid { display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-5); margin-bottom: var(--space-5); align-items: start; }
    @media (max-width: 992px) { .dash-grid { grid-template-columns: 1fr; } }

    /* Status distribution bar */
    .status-bar {
        display: flex;
        height: 10px;
        border-radius: 999px;
        overflow: hidden;
        background: var(--surface-bg-secondary);
        margin-bottom: var(--space-3);
    }

    .status-bar-segment { height: 100%; }
    .status-legend { display: flex; flex-wrap: wrap; gap: var(--space-4); }
    .status-legend-item { display: flex; align-items: center; gap: 6px; font-size: var(--text-xs); color: var(--text-secondary); }
    .status-legend-dot { width: 8px; height: 8px; border-radius: 999px; flex-shrink: 0; }

    /* Organizations table */
    .org-table { width: 100%; border-collapse: collapse; }
    .org-table th {
        text-align: start;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
        padding: var(--space-2) var(--space-5);
        border-bottom: 1px solid var(--surface-border);
        background: var(--surface-bg-secondary);
    }
    .org-table td { padding: var(--space-3) var(--space-5); border-bottom: 1px solid var(--surface-border); vertical-align: middle; }
    .org-table tr:last-child td { border-bottom: none; }
    .org-table tr:hover td { background: var(--surface-bg-secondary); }

    .org-row-name { display: flex; align-items: center; gap: var(--space-3); }
    .org-avatar {
        width: 36px; height: 36px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        color: white; font-weight: 700; font-size: var(--text-sm); flex-shrink: 0;
    }
    .org-row-name a { color: var(--text-primary); font-weight: 600; font-size: var(--text-sm); text-decoration: none; }
    .org-row-name a:hover { color: var(--primary-600); }
    .org-row-meta { font-size: 11px; color: var(--text-tertiary); }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
    }
    .pill-active { background: var(--success-50); color: var(--success-700); }
    .pill-suspended { background: var(--warning-50); color: var(--warning-700); }
    .pill-cancelled { background: var(--danger-50); color: var(--danger-700); }
    .pill-default { background: var(--neutral-100); color: var(--neutral-600); }

    .table-stat { font-size: var(--text-sm); color: var(--text-secondary); }

    /* Quick actions grid */
    .qa-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--space-3); padding: var(--space-5); }
    .qa-tile {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-2);
        padding: var(--space-4);
        border: 1px solid var(--surface-border);
        border-radius: 10px;
        text-decoration: none;
        color: var(--text-primary);
        transition: all 0.2s ease;
    }
    .qa-tile:hover { border-color: var(--primary-600); background: var(--primary-50); color: var(--primary-700); }
    .qa-tile i { font-size: 1.25rem; color: var(--primary-600); }
    .qa-tile span { font-size: var(--text-xs); font-weight: 600; }

    /* Activity timeline */
    .timeline { padding: var(--space-4) var(--space-5); }
    .timeline-item { display: flex; gap: var(--space-3); padding-bottom: var(--space-4); position: relative; }
    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 30px;
        bottom: 0;
        width: 1px;
        background: var(--surface-border);
    }
    .timeline-dot {
        width: 30px; height: 30px; border-radius: 999px;
        display: flex; align-items: center; justify-content: center;
        font-size: var(--text-xs); flex-shrink: 0; z-index: 1;
    }
    .timeline-body { flex: 1; padding-top: 2px; }
    .timeline-title { font-size: var(--text-xs); font-weight: 600; color: var(--text-primary); }
    .timeline-meta { font-size: 11px; color: var(--text-tertiary); margin-top: 2px; }

    .empty-panel { text-align: center; padding: var(--space-8) var(--space-4); color: var(--text-tertiary); }
    .empty-panel i { font-size: 1.75rem; opacity: 0.4; display: block; margin-bottom: var(--space-2); }
</style>

@php
    $avatarPalette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777'];
    $colorFor = fn($seed) => $avatarPalette[crc32($seed) % count($avatarPalette)];

    $totalOrganizations = \App\Models\Organization::count();
    $activeOrganizations = \App\Models\Organization::where('status', 'active')->count();
    $suspendedOrganizations = \App\Models\Organization::where('status', 'suspended')->count();
    $cancelledOrganizations = \App\Models\Organization::where('status', 'cancelled')->count();
    $totalUsers = \App\Models\User::count();
    $totalPosts = \App\Models\Post::count();
    $publishedPosts = \App\Models\Post::where('status', 'published')->count();
    $openTickets = \App\Models\Ticket::where('status', '!=', 'closed')->count();
    $totalChannels = \App\Models\Channel::count();

    $statusSegments = [
        ['label' => __('Active'), 'count' => $activeOrganizations, 'color' => 'var(--success-500)', 'dot' => 'pill-active'],
        ['label' => __('Suspended'), 'count' => $suspendedOrganizations, 'color' => 'var(--warning-500)', 'dot' => 'pill-suspended'],
        ['label' => __('Cancelled'), 'count' => $cancelledOrganizations, 'color' => 'var(--danger-500)', 'dot' => 'pill-cancelled'],
    ];

    $recentOrganizations = \App\Models\Organization::withCount('users', 'posts', 'channels')->latest()->take(8)->get();

    $activity = collect()
        ->concat(
            \App\Models\Organization::latest()->take(5)->get()->map(fn ($o) => [
                'icon' => 'bi-building', 'color' => 'primary',
                'title' => __(':name was added to the platform', ['name' => $o->name]),
                'time' => $o->created_at,
            ])
        )
        ->concat(
            \App\Models\Ticket::with('organization')->latest('opened_at')->take(5)->get()->map(fn ($t) => [
                'icon' => 'bi-ticket', 'color' => 'warning',
                'title' => __('Ticket :num opened by :org', ['num' => $t->ticket_number, 'org' => $t->organization->name ?? __('Unknown org')]),
                'time' => $t->opened_at ?? $t->created_at,
            ])
        )
        ->sortByDesc('time')
        ->take(6);
@endphp

<div class="dash-header">
    <div>
        <div class="dash-header-eyebrow">{{ __('Super Admin') }}</div>
        <h1>{{ __('Platform Overview') }}</h1>
        <p>{{ __('Welcome back,') }} {{ auth()->user()->full_name }} · {{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
    <a href="{{ route('admin.organizations.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> {{ __('New Organization') }}
    </a>
</div>

<!-- KPI Row -->
<div class="kpi-grid">
    <div class="kpi-card" style="--kpi-accent: var(--primary-600); --kpi-icon-bg: var(--primary-50);">
        <div class="kpi-card-top">
            <div class="kpi-icon"><i class="bi bi-building"></i></div>
        </div>
        <div class="kpi-value">{{ $totalOrganizations }}</div>
        <div class="kpi-label">{{ __('Organizations') }}</div>
        <div class="kpi-sub">{{ $activeOrganizations }} {{ __('active') }}</div>
    </div>

    <div class="kpi-card" style="--kpi-accent: var(--success-600); --kpi-icon-bg: var(--success-50);">
        <div class="kpi-card-top">
            <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
        </div>
        <div class="kpi-value">{{ $totalUsers }}</div>
        <div class="kpi-label">{{ __('Total Users') }}</div>
        <div class="kpi-sub">{{ __('across all organizations') }}</div>
    </div>

    <div class="kpi-card" style="--kpi-accent: var(--info-600); --kpi-icon-bg: var(--info-50);">
        <div class="kpi-card-top">
            <div class="kpi-icon"><i class="bi bi-file-text"></i></div>
        </div>
        <div class="kpi-value">{{ $totalPosts }}</div>
        <div class="kpi-label">{{ __('Total Posts') }}</div>
        <div class="kpi-sub">{{ $publishedPosts }} {{ __('published') }}</div>
    </div>

    <div class="kpi-card" style="--kpi-accent: var(--warning-600); --kpi-icon-bg: var(--warning-50);">
        <div class="kpi-card-top">
            <div class="kpi-icon"><i class="bi bi-ticket"></i></div>
        </div>
        <div class="kpi-value">{{ $openTickets }}</div>
        <div class="kpi-label">{{ __('Open Tickets') }}</div>
        <div class="kpi-sub">{{ __('need attention') }}</div>
    </div>
</div>

<!-- Organizations Status Distribution -->
<div class="panel" style="margin-bottom: var(--space-5);">
    <div class="panel-header">
        <h2><i class="bi bi-pie-chart"></i> {{ __('Organizations by Status') }}</h2>
        <span class="table-stat">{{ $totalChannels }} {{ __('channels total') }}</span>
    </div>
    <div style="padding: var(--space-5);">
        <div class="status-bar">
            @foreach($statusSegments as $segment)
                @if($totalOrganizations > 0 && $segment['count'] > 0)
                    <div class="status-bar-segment" style="width: {{ ($segment['count'] / $totalOrganizations) * 100 }}%; background-color: {{ $segment['color'] }};"></div>
                @endif
            @endforeach
        </div>
        <div class="status-legend">
            @foreach($statusSegments as $segment)
                <div class="status-legend-item">
                    <span class="status-legend-dot" style="background-color: {{ $segment['color'] }};"></span>
                    {{ $segment['label'] }} — {{ $segment['count'] }}
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="dash-grid">
    <!-- Organizations Table -->
    <div class="panel">
        <div class="panel-header">
            <h2><i class="bi bi-building"></i> {{ __('Organizations') }}</h2>
            <a href="{{ route('admin.organizations.index') }}" class="panel-link">{{ __('View All') }} →</a>
        </div>
        @if($recentOrganizations->isEmpty())
            <div class="empty-panel">
                <i class="bi bi-inbox"></i>
                {{ __('No organizations yet') }}
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="org-table">
                    <thead>
                        <tr>
                            <th>{{ __('Organization') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Users') }}</th>
                            <th>{{ __('Channels') }}</th>
                            <th>{{ __('Posts') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrganizations as $org)
                            <tr>
                                <td>
                                    <div class="org-row-name">
                                        <div class="org-avatar" style="background-color: {{ $colorFor($org->name) }};">
                                            {{ substr($org->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.organizations.show', $org) }}">{{ $org->name }}</a>
                                            <div class="org-row-meta">{{ $org->city ?? __('No city set') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="pill {{ match($org->status) { 'active' => 'pill-active', 'suspended' => 'pill-suspended', 'cancelled' => 'pill-cancelled', default => 'pill-default' } }}">
                                        {{ ucfirst($org->status) }}
                                    </span>
                                </td>
                                <td class="table-stat">{{ $org->users_count }}</td>
                                <td class="table-stat">{{ $org->channels_count }}</td>
                                <td class="table-stat">{{ $org->posts_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Right column: Quick Actions + Activity -->
    <div style="display: flex; flex-direction: column; gap: var(--space-5);">
        <div class="panel">
            <div class="panel-header">
                <h2><i class="bi bi-lightning"></i> {{ __('Quick Actions') }}</h2>
            </div>
            <div class="qa-grid">
                <a href="{{ route('admin.organizations.create') }}" class="qa-tile">
                    <i class="bi bi-plus-circle"></i>
                    <span>{{ __('New Organization') }}</span>
                </a>
                <a href="{{ route('admin.organizations.index') }}" class="qa-tile">
                    <i class="bi bi-building"></i>
                    <span>{{ __('Organizations') }}</span>
                </a>
                <a href="{{ route('posts.index') }}" class="qa-tile">
                    <i class="bi bi-file-text"></i>
                    <span>{{ __('Posts') }}</span>
                </a>
                <a href="{{ route('tickets.index') }}" class="qa-tile">
                    <i class="bi bi-ticket"></i>
                    <span>{{ __('Tickets') }}</span>
                </a>
                <a href="{{ route('audience-segments.index') }}" class="qa-tile">
                    <i class="bi bi-people"></i>
                    <span>{{ __('Audiences') }}</span>
                </a>
                <a href="{{ route('tenant.channels.index') }}" class="qa-tile">
                    <i class="bi bi-chat-dots"></i>
                    <span>{{ __('Channels') }}</span>
                </a>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2><i class="bi bi-clock-history"></i> {{ __('Recent Activity') }}</h2>
            </div>
            @if($activity->isEmpty())
                <div class="empty-panel">
                    <i class="bi bi-clock"></i>
                    {{ __('Nothing yet') }}
                </div>
            @else
                <div class="timeline">
                    @foreach($activity as $item)
                        <div class="timeline-item">
                            <div class="timeline-dot" style="background: var(--{{ $item['color'] }}-50); color: var(--{{ $item['color'] }}-600);">
                                <i class="bi {{ $item['icon'] }}"></i>
                            </div>
                            <div class="timeline-body">
                                <div class="timeline-title">{{ $item['title'] }}</div>
                                <div class="timeline-meta">{{ $item['time']?->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Open Tickets -->
<div class="panel">
    <div class="panel-header">
        <h2><i class="bi bi-ticket"></i> {{ __('Recent Open Tickets') }}</h2>
        <a href="{{ route('tickets.index') }}" class="panel-link">{{ __('View All') }} →</a>
    </div>
    @php
        $recentTickets = \App\Models\Ticket::where('status', '!=', 'closed')->with('organization')->latest()->take(6)->get();
    @endphp
    @if($recentTickets->isEmpty())
        <div class="empty-panel">
            <i class="bi bi-check-circle"></i>
            {{ __('No open tickets') }}
        </div>
    @else
        <div style="overflow-x: auto;">
            <table class="org-table">
                <thead>
                    <tr>
                        <th>{{ __('Ticket') }}</th>
                        <th>{{ __('Organization') }}</th>
                        <th>{{ __('Priority') }}</th>
                        <th>{{ __('Opened') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTickets as $ticket)
                        <tr>
                            <td>
                                <code>{{ $ticket->ticket_number }}</code>
                                <a href="{{ route('tickets.show', $ticket) }}" style="margin-inline-start: 6px; color: var(--text-primary); text-decoration: none; font-weight: 600; font-size: var(--text-sm);">
                                    {{ Str::limit($ticket->title, 40) }}
                                </a>
                            </td>
                            <td class="table-stat">{{ $ticket->organization->name ?? __('Unknown org') }}</td>
                            <td>
                                <span class="pill {{ match($ticket->priority) { 'urgent', 'high' => 'pill-cancelled', 'medium' => 'pill-suspended', default => 'pill-default' } }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td class="table-stat">{{ $ticket->opened_at?->format('M d, Y') ?? __('Recently') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
