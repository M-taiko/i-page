@extends('layouts.app-modern')

@section('title', __('Dashboard'))

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-top">
        <div class="page-header-info">
            <h1>{{ __('Welcome back, ') }}{{ auth()->user()->first_name }}! 👋</h1>
            <p>{{ __('Here\'s what\'s happening with your hotel today.') }}</p>
        </div>
    </div>
</div>

<!-- KPI Cards Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-12);">
    <x-stat-card
        title="{{ __('Total Channels') }}"
        value="{{ $kpis['total_channels'] }}"
        icon="chat-dots"
        color="primary"
    />

    <x-stat-card
        title="{{ __('Active Users') }}"
        value="{{ $kpis['active_users'] }}"
        icon="people"
        color="success"
        trend="up"
        trendValue="+12% from last week"
    />

    <x-stat-card
        title="{{ __('Posts Today') }}"
        value="{{ $kpis['posts_today'] }}"
        icon="newspaper"
        color="info"
    />

    <x-stat-card
        title="{{ __('Groups') }}"
        value="{{ $kpis['groups'] }}"
        icon="diagram-3"
        color="warning"
    />

    <x-stat-card
        title="{{ __('VIP Guests') }}"
        value="{{ $kpis['vip_guests'] }}"
        icon="star"
        color="danger"
    />

    <x-stat-card
        title="{{ __('Pending Approvals') }}"
        value="{{ $kpis['pending_notices'] }}"
        icon="exclamation-circle"
        color="warning"
    />
</div>

<!-- Recent Activity Section -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6); margin-top: var(--space-12);">
    <!-- Recent Posts -->
    <x-card-modern title="{{ __('Recent Posts') }}" icon="newspaper">
        @if ($recent_posts->count() > 0)
            <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                @foreach ($recent_posts as $post)
                    <div style="padding-bottom: var(--space-4); border-bottom: 1px solid var(--surface-border);">
                        <div style="display: flex; gap: var(--space-3); margin-bottom: var(--space-2);">
                            <div style="width: 40px; height: 40px; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--primary-100), var(--secondary-100)); display: flex; align-items: center; justify-content: center; font-size: var(--text-sm); font-weight: var(--font-weight-bold); color: var(--primary-700); flex-shrink: 0;">
                                {{ $post->author->initials }}
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 var(--space-1); font-size: var(--text-sm);">{{ $post->author->full_name }}</h4>
                                <small style="color: var(--text-tertiary);">{{ $post->published_at?->diffForHumans() }}</small>
                            </div>
                        </div>
                        <p style="margin: var(--space-2) 0 0; font-size: var(--text-sm); color: var(--text-secondary); line-height: var(--line-height-normal);">
                            {{ Str::limit($post->body, 80) }}
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            <x-empty-state-modern
                title="{{ __('No Posts Yet') }}"
                message="{{ __('Start creating posts to keep your team informed.') }}"
                icon="newspaper"
            />
        @endif
    </x-card-modern>

    <!-- Quick Stats -->
    <x-card-modern title="{{ __('Quick Stats') }}" icon="graph-up">
        <div style="display: flex; flex-direction: column; gap: var(--space-6);">
            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-2);">
                    <span style="font-size: var(--text-sm); color: var(--text-secondary);">{{ __('Engagement Rate') }}</span>
                    <strong style="color: var(--success-600);">78%</strong>
                </div>
                <div style="height: 4px; background-color: var(--neutral-200); border-radius: var(--radius-full); overflow: hidden;">
                    <div style="height: 100%; width: 78%; background: linear-gradient(90deg, var(--success-500), var(--success-600));"></div>
                </div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-2);">
                    <span style="font-size: var(--text-sm); color: var(--text-secondary);">{{ __('Team Participation') }}</span>
                    <strong style="color: var(--primary-600);">92%</strong>
                </div>
                <div style="height: 4px; background-color: var(--neutral-200); border-radius: var(--radius-full); overflow: hidden;">
                    <div style="height: 100%; width: 92%; background: linear-gradient(90deg, var(--primary-500), var(--primary-600));"></div>
                </div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-2);">
                    <span style="font-size: var(--text-sm); color: var(--text-secondary);">{{ __('System Uptime') }}</span>
                    <strong style="color: var(--info-600);">99.9%</strong>
                </div>
                <div style="height: 4px; background-color: var(--neutral-200); border-radius: var(--radius-full); overflow: hidden;">
                    <div style="height: 100%; width: 99.9%; background: linear-gradient(90deg, var(--info-500), var(--info-600));"></div>
                </div>
            </div>
        </div>
    </x-card-modern>
</div>

<!-- Quick Actions -->
<div style="margin-top: var(--space-12);">
    <h3 style="margin-bottom: var(--space-6);">{{ __('Quick Actions') }}</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4);">
        <x-btn
            href="{{ route('dashboard.feeds.create', session('current_organization_id')) }}"
            variant="primary"
            size="lg"
            icon="pencil-square"
            class="w-100"
        >
            {{ __('Create Post') }}
        </x-btn>

        <x-btn
            href="{{ route('dashboard.channels.create', session('current_organization_id')) }}"
            variant="secondary"
            size="lg"
            icon="plus-circle"
            class="w-100"
        >
            {{ __('New Channel') }}
        </x-btn>

        <x-btn
            href="{{ route('dashboard.users.index', session('current_organization_id')) }}"
            variant="outline"
            size="lg"
            icon="people"
            class="w-100"
        >
            {{ __('Manage Users') }}
        </x-btn>

        <x-btn
            href="{{ route('dashboard.groups.index', session('current_organization_id')) }}"
            variant="outline"
            size="lg"
            icon="diagram-3"
            class="w-100"
        >
            {{ __('View Groups') }}
        </x-btn>
    </div>
</div>

<style>
    @media (max-width: 1024px) {
        [style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection
