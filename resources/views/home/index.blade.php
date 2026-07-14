@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-house me-2"></i>
        Welcome to Hilton Jeddah Communication Hub
    </h1>
</div>

<!-- Channel Feeds Row -->
<div class="row g-4">
    @forelse($channels ?? [] as $channel)
        <div class="col-md-6 col-lg-4">
            <x-card class="h-100">
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>{{ $channel->name }}</span>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-eye me-2"></i>View</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-bell-slash me-2"></i>Mute</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-people me-2"></i>Members</a></li>
                            </ul>
                        </div>
                    </div>
                </x-slot>

                <!-- Messages -->
                <div class="space-y-2">
                    @forelse($channel->posts ?? [] as $post)
                        <div class="p-3 bg-light rounded mb-2">
                            <p class="mb-2">{{ Str::limit($post->body, 100) }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button class="btn btn-sm btn-link text-warning p-0 me-2" type="button">
                                        <i class="bi bi-hand-thumbs-up"></i>
                                    </button>
                                    <button class="btn btn-sm btn-link text-danger p-0" type="button">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                </div>
                                <small class="text-muted">{{ $post->created_at?->format('H:i') }}</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">No messages yet</p>
                    @endforelse
                </div>
            </x-card>
        </div>
    @empty
        <div class="col-12">
            <x-empty-state
                icon="inbox"
                title="No channels available"
                message="Channels will appear here once they are created."
            />
        </div>
    @endforelse
</div>
@endsection
