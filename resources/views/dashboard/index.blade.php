@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-graph-up me-2"></i>
        Dashboard Overview
    </h1>
</div>

<!-- KPI Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-4">
        <x-card>
            <div class="text-center">
                <i class="bi bi-diagram-3 text-primary" style="font-size: 2rem;"></i>
                <p class="text-muted small mt-2 mb-1">Total Channels</p>
                <h3 class="text-primary">{{ $kpis['total_channels'] ?? 0 }}</h3>
            </div>
        </x-card>
    </div>

    <div class="col-md-6 col-lg-4">
        <x-card>
            <div class="text-center">
                <i class="bi bi-person-check text-success" style="font-size: 2rem;"></i>
                <p class="text-muted small mt-2 mb-1">Active Users</p>
                <h3 class="text-success">{{ $kpis['active_users'] ?? 0 }}</h3>
            </div>
        </x-card>
    </div>

    <div class="col-md-6 col-lg-4">
        <x-card>
            <div class="text-center">
                <i class="bi bi-file-post text-info" style="font-size: 2rem;"></i>
                <p class="text-muted small mt-2 mb-1">Posts Today</p>
                <h3 class="text-info">{{ $kpis['posts_today'] ?? 0 }}</h3>
            </div>
        </x-card>
    </div>

    <div class="col-md-6 col-lg-4">
        <x-card>
            <div class="text-center">
                <i class="bi bi-people text-warning" style="font-size: 2rem;"></i>
                <p class="text-muted small mt-2 mb-1">Groups</p>
                <h3 class="text-warning">{{ $kpis['groups'] ?? 0 }}</h3>
            </div>
        </x-card>
    </div>

    <div class="col-md-6 col-lg-4">
        <x-card>
            <div class="text-center">
                <i class="bi bi-star text-danger" style="font-size: 2rem;"></i>
                <p class="text-muted small mt-2 mb-1">VIP Guests</p>
                <h3 class="text-danger">{{ $kpis['vip_guests'] ?? 0 }}</h3>
            </div>
        </x-card>
    </div>

    <div class="col-md-6 col-lg-4">
        <x-card>
            <div class="text-center">
                <i class="bi bi-exclamation-circle text-secondary" style="font-size: 2rem;"></i>
                <p class="text-muted small mt-2 mb-1">Pending Notices</p>
                <h3 class="text-secondary">{{ $kpis['pending_notices'] ?? 0 }}</h3>
            </div>
        </x-card>
    </div>
</div>

<!-- Recent Activity -->
<div class="row g-4">
    <div class="col-lg-6">
        <x-card>
            <x-slot name="header">
                <i class="bi bi-clock-history me-2"></i>
                Recent Posts
            </x-slot>

            @forelse($recent_posts ?? [] as $post)
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">{{ $post->author?->full_name }}</h6>
                            <p class="text-muted small mb-2">{{ Str::limit($post->body, 100) }}</p>
                        </div>
                        <x-badge color="info">{{ $post->status }}</x-badge>
                    </div>
                    <small class="text-muted">{{ $post->created_at?->diffForHumans() }}</small>
                </div>
            @empty
                <p class="text-muted text-center py-3">No recent posts</p>
            @endforelse
        </x-card>
    </div>

    <div class="col-lg-6">
        <x-card>
            <x-slot name="header">
                <i class="bi bi-info-circle me-2"></i>
                System Info
            </x-slot>

            <div class="row g-3">
                <div class="col-6">
                    <p class="text-muted small mb-1">Application</p>
                    <h6>IPAGE</h6>
                </div>
                <div class="col-6">
                    <p class="text-muted small mb-1">Version</p>
                    <h6>1.0.0</h6>
                </div>
                <div class="col-6">
                    <p class="text-muted small mb-1">Environment</p>
                    <h6>{{ config('app.env') }}</h6>
                </div>
                <div class="col-6">
                    <p class="text-muted small mb-1">Database</p>
                    <h6>{{ config('database.default') }}</h6>
                </div>
            </div>
        </x-card>
    </div>
</div>
@endsection
