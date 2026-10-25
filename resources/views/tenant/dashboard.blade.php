@extends('layouts.valex')

@section('title', __('Organization Dashboard'))

@section('page-header')
    <x-valex.page-header :title="$organization->name" :subtitle="__('Manage your organization communications and content')">
        <x-slot:actions>
            <a href="{{ route('tenant.channels.create') }}" class="btn btn-primary"><i class="fe fe-plus-circle"></i> {{ __('Create Channel') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row row-sm">
    <div class="col-sm-6 col-xl-4"><x-valex.stat-card :title="__('Channels')" :value="$stats['total_channels'] . '/' . $stats['max_channels']" icon="message-circle" variant="primary" /></div>
    <div class="col-sm-6 col-xl-4"><x-valex.stat-card :title="__('Posts')" :value="$stats['total_posts']" icon="file-text" variant="success" /></div>
    <div class="col-sm-6 col-xl-4"><x-valex.stat-card :title="__('Team Members')" :value="$stats['total_users']" icon="users" variant="info" /></div>
</div>

@if ($stats['recent_posts']->count() > 0)
    <h5 class="mb-3"><i class="fe fe-file-text"></i> {{ __('Recent Posts') }}</h5>
    <div class="row row-sm">
        @foreach ($stats['recent_posts'] as $post)
            <div class="col-md-6 col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="avatar avatar-md brround bg-primary mx-2">{{ $post->author->initials }}</span>
                            <div>
                                <div class="font-weight-bold">{{ $post->author->full_name }}</div>
                                <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <p>{{ Str::limit($post->body, 120) }}</p>
                        @if ($post->channel)
                            <x-valex.badge variant="primary"><i class="fe fe-message-circle"></i> {{ $post->channel->name }}</x-valex.badge>
                        @endif
                        @if ($post->audience)
                            <x-valex.badge variant="info"><i class="fe fe-eye"></i> {{ ucfirst($post->audience) }}</x-valex.badge>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <x-valex.empty-state :title="__('No Posts Yet')" :message="__('Start by creating your first post to engage with your team.')" icon="file-text">
        <a href="{{ route('tenant.channels.create') }}" class="btn btn-primary mt-3"><i class="fe fe-plus-circle"></i> {{ __('Create First Channel') }}</a>
    </x-valex.empty-state>
@endif
@endsection
