@extends('layouts.valex')

@section('title', __('Posts & Communications'))

@section('page-header')
    <x-valex.page-header :title="__('Posts & Communications')" :subtitle="__('Announcements, news, and updates for your organization')">
        <x-slot:actions>
            @can('create', App\Models\Post::class)
                <a href="{{ route('posts.create') }}" class="btn btn-primary"><i class="fe fe-plus"></i> {{ __('New Post') }}</a>
            @endcan
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $statusVariant = fn ($s) => match ($s) {
        'published' => 'success', 'pending_approval' => 'warning', 'rejected' => 'danger', 'scheduled' => 'info', default => 'secondary',
    };
@endphp

@if ($posts->isEmpty())
    <x-valex.empty-state :title="__('No posts yet')" :message="__('Create one to get started.')" icon="file-text" />
@else
    @foreach ($posts as $post)
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div>
                        <h5 class="mb-1">{{ $post->title ?? __('Untitled') }}</h5>
                        <div class="small text-muted">
                            <i class="fe fe-user"></i> {{ $post->author->full_name }} &middot;
                            <i class="fe fe-calendar"></i> {{ $post->created_at->format('M d, Y') }}
                        </div>
                    </div>
                    <x-valex.badge :variant="$statusVariant($post->status)">{{ str_replace('_', ' ', $post->status) }}</x-valex.badge>
                </div>

                <p class="text-muted my-3">{{ Str::limit($post->body, 150) }}</p>

                <div class="d-flex flex-wrap">
                    @can('view', $post)
                        <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-outline-primary m-1"><i class="fe fe-eye"></i> {{ __('View') }}</a>
                    @endcan
                    @can('update', $post)
                        <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-outline-secondary m-1"><i class="fe fe-edit"></i> {{ __('Edit') }}</a>
                    @endcan
                    @if ($post->status === 'pending_approval' && auth()->user()->can('approvePost', $post))
                        <form action="{{ route('posts.approve', $post) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success m-1"><i class="fe fe-check-circle"></i> {{ __('Approve') }}</button>
                        </form>
                        <form action="{{ route('posts.reject', $post) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger m-1"><i class="fe fe-x-circle"></i> {{ __('Reject') }}</button>
                        </form>
                    @endif
                </div>

                @if ($post->audience)
                    <div class="small text-muted border-top mt-3 pt-3"><i class="fe fe-users"></i> {{ __('Audience') }}: {{ $post->audience->segment->name ?? __('Custom rules') }}</div>
                @endif
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-center mt-4">{{ $posts->links('pagination::bootstrap-4') }}</div>
@endif
@endsection
