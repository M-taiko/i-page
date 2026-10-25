@extends('layouts.valex')

@section('title', __('Dashboard'))

@section('page-header')
    <x-valex.page-header :title="__('Welcome back, ') . auth()->user()->first_name" :subtitle="__('Here\'s what\'s happening with your organization today.')" />
@endsection

@section('content')
<div class="row row-sm">
    <div class="col-sm-6 col-xl-4"><x-valex.stat-card :title="__('Total Channels')" :value="$kpis['total_channels']" icon="message-circle" variant="primary" /></div>
    <div class="col-sm-6 col-xl-4"><x-valex.stat-card :title="__('Active Users')" :value="$kpis['active_users']" icon="users" variant="success" /></div>
    <div class="col-sm-6 col-xl-4"><x-valex.stat-card :title="__('Posts Today')" :value="$kpis['posts_today']" icon="file-text" variant="info" /></div>
    <div class="col-sm-6 col-xl-4"><x-valex.stat-card :title="__('Groups')" :value="$kpis['groups']" icon="share-2" variant="warning" /></div>
    <div class="col-sm-6 col-xl-4"><x-valex.stat-card :title="__('VIP Guests')" :value="$kpis['vip_guests']" icon="star" variant="danger" /></div>
    <div class="col-sm-6 col-xl-4"><x-valex.stat-card :title="__('Pending Approvals')" :value="$kpis['pending_notices']" icon="alert-circle" variant="warning" /></div>
</div>

<div class="row row-sm">
    <div class="col-lg-6">
        <x-valex.card :title="__('Recent Posts')">
            @if ($recent_posts->count() > 0)
                <ul class="list-group list-group-flush">
                    @foreach ($recent_posts as $post)
                        <li class="list-group-item px-0">
                            <div class="d-flex align-items-center mb-2">
                                <span class="avatar avatar-md brround bg-primary mx-2">{{ $post->author->initials }}</span>
                                <div>
                                    <div class="font-weight-bold">{{ $post->author->full_name }}</div>
                                    <small class="text-muted">{{ $post->published_at?->diffForHumans() }}</small>
                                </div>
                            </div>
                            <p class="text-muted mb-0">{{ Str::limit($post->body, 80) }}</p>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted text-center mb-0">{{ __('No posts yet. Start creating posts to keep your team informed.') }}</p>
            @endif
        </x-valex.card>
    </div>

    <div class="col-lg-6">
        <x-valex.card :title="__('Quick Stats')">
            @foreach ([[__('Engagement Rate'), 78, 'success'], [__('Team Participation'), 92, 'primary'], [__('System Uptime'), 99.9, 'info']] as [$label, $pct, $variant])
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">{{ $label }}</span><strong class="text-{{ $variant }}">{{ $pct }}%</strong>
                    </div>
                    <div class="progress" style="height: 6px;"><div class="progress-bar bg-{{ $variant }}" style="width: {{ $pct }}%"></div></div>
                </div>
            @endforeach
        </x-valex.card>
    </div>
</div>

<h5 class="mb-3">{{ __('Quick Actions') }}</h5>
<div class="row row-sm">
    <div class="col-sm-6 col-xl-3 mb-3"><a href="{{ route('dashboard.feeds.create', session('current_organization_id')) }}" class="btn btn-primary btn-lg btn-block"><i class="fe fe-edit-3"></i> {{ __('Create Post') }}</a></div>
    <div class="col-sm-6 col-xl-3 mb-3"><a href="{{ route('dashboard.channels.create', session('current_organization_id')) }}" class="btn btn-secondary btn-lg btn-block"><i class="fe fe-plus-circle"></i> {{ __('New Channel') }}</a></div>
    <div class="col-sm-6 col-xl-3 mb-3"><a href="{{ route('dashboard.users.index', session('current_organization_id')) }}" class="btn btn-outline-primary btn-lg btn-block"><i class="fe fe-users"></i> {{ __('Manage Users') }}</a></div>
    <div class="col-sm-6 col-xl-3 mb-3"><a href="{{ route('dashboard.groups.index', session('current_organization_id')) }}" class="btn btn-outline-primary btn-lg btn-block"><i class="fe fe-share-2"></i> {{ __('View Groups') }}</a></div>
</div>
@endsection
