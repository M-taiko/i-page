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

    .header-section h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
    .header-section p { opacity: 0.9; margin-bottom: 0; }

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

    .recent-item { padding: 1rem; border-bottom: 1px solid var(--surface-border); transition: background 0.3s ease; }
    .recent-item:hover { background: var(--surface-bg-secondary); }
    .recent-item:last-child { border-bottom: none; }

    .stat-value { font-size: 2rem; font-weight: 700; color: var(--text-primary); }
    .stat-label { font-size: 0.875rem; color: var(--text-tertiary); font-weight: 500; }

    .org-avatar {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: white; font-weight: 700; flex-shrink: 0;
    }

    /* Status distribution bar (pure CSS, sits inside a regular Bootstrap card) */
    .status-bar {
        display: flex;
        height: 10px;
        border-radius: 999px;
        overflow: hidden;
        background: var(--surface-bg-secondary);
        margin-bottom: 1rem;
    }
    .status-bar-segment { height: 100%; }

    .timeline-dot {
        width: 32px; height: 32px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.8rem; flex-shrink: 0;
    }
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
        ['label' => __('Active'), 'count' => $activeOrganizations, 'color' => '#22c55e', 'badge' => 'success'],
        ['label' => __('Suspended'), 'count' => $suspendedOrganizations, 'color' => '#f59e0b', 'badge' => 'warning'],
        ['label' => __('Cancelled'), 'count' => $cancelledOrganizations, 'color' => '#ef4444', 'badge' => 'danger'],
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

<div class="container-lg py-4">
    <!-- Header -->
    <div class="header-section">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1>{{ __('Super Admin Dashboard') }}</h1>
                <p>{{ __('Welcome back,') }} {{ auth()->user()->full_name }} — {{ __('platform-wide overview') }}</p>
            </div>
            <a href="{{ route('admin.organizations.create') }}" class="btn btn-light">
                <i class="bi bi-plus-circle"></i> {{ __('New Organization') }}
            </a>
        </div>
    </div>

    <!-- Key Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon primary"><i class="bi bi-building"></i></div>
                    <div>
                        <p class="stat-label mb-1">{{ __('Organizations') }}</p>
                        <p class="stat-value mb-0">{{ $totalOrganizations }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon success"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <p class="stat-label mb-1">{{ __('Total Users') }}</p>
                        <p class="stat-value mb-0">{{ $totalUsers }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon info"><i class="bi bi-file-text"></i></div>
                    <div>
                        <p class="stat-label mb-1">{{ __('Total Posts') }}</p>
                        <p class="stat-value mb-0">{{ $totalPosts }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-card-icon warning"><i class="bi bi-ticket"></i></div>
                    <div>
                        <p class="stat-label mb-1">{{ __('Open Tickets') }}</p>
                        <p class="stat-value mb-0">{{ $openTickets }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Organizations by Status -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-pie-chart"></i> {{ __('Organizations by Status') }}</h5>
            <small class="text-muted">{{ $totalChannels }} {{ __('channels total') }}</small>
        </div>
        <div class="card-body">
            <div class="status-bar">
                @foreach($statusSegments as $segment)
                    @if($totalOrganizations > 0 && $segment['count'] > 0)
                        <div class="status-bar-segment" style="width: {{ ($segment['count'] / $totalOrganizations) * 100 }}%; background-color: {{ $segment['color'] }};"></div>
                    @endif
                @endforeach
            </div>
            <div class="d-flex flex-wrap gap-3">
                @foreach($statusSegments as $segment)
                    <span class="badge bg-{{ $segment['badge'] }}-subtle text-{{ $segment['badge'] }}-emphasis border border-{{ $segment['badge'] }}-subtle">
                        {{ $segment['label'] }} — {{ $segment['count'] }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row g-4 mb-4">
        <!-- Organizations -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-building"></i> {{ __('Organizations') }}</h5>
                    <a href="{{ route('admin.organizations.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }} →</a>
                </div>
                @if($recentOrganizations->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                        <p class="mt-2">{{ __('No organizations yet') }}</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
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
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="org-avatar" style="background-color: {{ $colorFor($org->name) }};">
                                                    {{ substr($org->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <a href="{{ route('admin.organizations.show', $org) }}" class="text-decoration-none fw-500">{{ $org->name }}</a>
                                                    <div class="small text-muted">{{ $org->city ?? __('No city set') }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ match($org->status) { 'active' => 'success', 'suspended' => 'warning', 'cancelled' => 'danger', default => 'secondary' } }}">
                                                {{ ucfirst($org->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $org->users_count }}</td>
                                        <td>{{ $org->channels_count }}</td>
                                        <td>{{ $org->posts_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions + Activity -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> {{ __('Quick Actions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="{{ route('admin.organizations.create') }}" class="action-btn">
                                <i class="bi bi-plus-circle"></i> {{ __('New Org') }}
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.organizations.index') }}" class="action-btn">
                                <i class="bi bi-building"></i> {{ __('Organizations') }}
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('posts.index') }}" class="action-btn">
                                <i class="bi bi-file-text"></i> {{ __('Posts') }}
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('tickets.index') }}" class="action-btn">
                                <i class="bi bi-ticket"></i> {{ __('Tickets') }}
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('audience-segments.index') }}" class="action-btn">
                                <i class="bi bi-people"></i> {{ __('Audiences') }}
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('tenant.channels.index') }}" class="action-btn">
                                <i class="bi bi-chat-dots"></i> {{ __('Channels') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> {{ __('Recent Activity') }}</h5>
                </div>
                <div class="card-body">
                    @if($activity->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-clock" style="font-size: 2rem; opacity: 0.5;"></i>
                            <p class="mt-2 mb-0">{{ __('Nothing yet') }}</p>
                        </div>
                    @else
                        @foreach($activity as $item)
                            <div class="d-flex gap-3 {{ !$loop->last ? 'mb-3' : '' }}">
                                <div class="timeline-dot bg-{{ $item['color'] }}-subtle text-{{ $item['color'] }}-emphasis">
                                    <i class="bi {{ $item['icon'] }}"></i>
                                </div>
                                <div>
                                    <div class="small fw-500">{{ $item['title'] }}</div>
                                    <div class="small text-muted">{{ $item['time']?->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    @endif
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
                @php
                    $recentTickets = \App\Models\Ticket::where('status', '!=', 'closed')->with('organization')->latest()->take(6)->get();
                @endphp
                @if($recentTickets->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle" style="font-size: 2rem; opacity: 0.5;"></i>
                        <p class="mt-2 mb-0">{{ __('No open tickets') }}</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
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
                                            <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none ms-2">
                                                {{ Str::limit($ticket->title, 40) }}
                                            </a>
                                        </td>
                                        <td class="text-muted">{{ $ticket->organization->name ?? __('Unknown org') }}</td>
                                        <td>
                                            <span class="badge bg-{{ match($ticket->priority) { 'urgent' => 'danger', 'high' => 'warning', 'medium' => 'info', 'low' => 'secondary', default => 'secondary' } }}">
                                                {{ ucfirst($ticket->priority) }}
                                            </span>
                                        </td>
                                        <td class="text-muted">{{ $ticket->opened_at?->format('M d, Y') ?? __('Recently') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
