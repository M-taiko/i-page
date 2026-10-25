@extends('layouts.valex')

@section('title', $post->title)

@section('page-header')
    <x-valex.page-header :title="$post->title" :subtitle="__('Post')">
        <x-slot:actions>
            <a href="{{ route('posts.index') }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back to Posts') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $statusVariant = match ($post->status) { 'published' => 'success', 'pending_approval' => 'warning', 'rejected' => 'danger', 'scheduled' => 'info', default => 'secondary' };
@endphp

<x-valex.hero :title="$post->title">
    <div class="small op-8 mb-2">
        <i class="fe fe-user"></i> {{ $post->author->full_name ?? __('System') }} &middot;
        <i class="fe fe-calendar"></i> {{ $post->created_at->diffForHumans() }}
    </div>
    <x-valex.badge :variant="$statusVariant">{{ str_replace('_', ' ', $post->status) }}</x-valex.badge>
    <x-valex.badge variant="light">{{ $post->priority }}</x-valex.badge>
</x-valex.hero>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                @if ($post->summary)<p class="text-muted font-italic">{{ $post->summary }}</p>@endif
                @include('posts.partials._content', ['post' => $post, 'showWidgets' => true, 'flash' => true])

                @if ($post->media()->count())
                    <div class="row border-top mt-4 pt-4">
                        @foreach ($post->media as $media)
                            <div class="col-4 col-md-3 mb-3">
                                @if ($media->isImage())
                                    <img src="{{ $media->getUrl() }}" alt="{{ __('Attachment') }}" class="img-fluid rounded" style="height: 90px; width: 100%; object-fit: cover;">
                                @else
                                    <a href="{{ $media->getUrl() }}" class="d-block text-center border rounded p-3 small">
                                        <i class="fe fe-file-text tx-20 d-block"></i> {{ Str::limit($media->file_name, 16) }}
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @if ($post->status === 'published' || $post->status === 'scheduled')
            @php $stats = $post->getStats(); @endphp
            <x-valex.card :title="__('Engagement')">
                <div class="row text-center">
                    @foreach (['delivered' => __('Delivered'), 'viewed' => __('Viewed'), 'read' => __('Read'), 'acknowledged' => __('Acknowledged')] as $key => $label)
                        <div class="col-6 col-md-3 mb-3 mb-md-0">
                            <h3 class="mb-0">{{ $stats[$key] }}</h3>
                            <small class="text-muted text-uppercase">{{ $label }}</small>
                            @if ($stats['total_recipients'])
                                <div class="small text-primary font-weight-bold">{{ round($stats[$key] / $stats['total_recipients'] * 100) }}%</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-valex.card>
        @endif
    </div>

    <div class="col-lg-4">
        <x-valex.card :title="__('Details')">
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between px-0"><span class="text-muted">{{ __('Type') }}</span><strong>{{ str_replace('_', ' ', ucfirst($post->post_type)) }}</strong></li>
                <li class="list-group-item d-flex justify-content-between px-0"><span class="text-muted">{{ __('Language') }}</span><strong>{{ $post->language === 'ar' ? 'العربية' : 'English' }}</strong></li>
                @if ($post->scheduled_for)
                    <li class="list-group-item d-flex justify-content-between px-0"><span class="text-muted">{{ __('Scheduled For') }}</span><strong>{{ $post->scheduled_for->format('M d, Y H:i') }}</strong></li>
                @endif
                @if ($post->published_at)
                    <li class="list-group-item d-flex justify-content-between px-0"><span class="text-muted">{{ __('Published') }}</span><strong>{{ $post->published_at->format('M d, Y H:i') }}</strong></li>
                @endif
                @if ($post->approved_by)
                    <li class="list-group-item d-flex justify-content-between px-0"><span class="text-muted">{{ __('Approved By') }}</span><strong>{{ $post->approver->full_name ?? __('Admin') }}</strong></li>
                @endif
                <li class="list-group-item d-flex justify-content-between px-0"><span class="text-muted">{{ __('Needs Acknowledgment') }}</span><strong>{{ $post->requires_acknowledgment ? __('Yes') : __('No') }}</strong></li>
                @if ($post->brand)
                    <li class="list-group-item d-flex justify-content-between px-0"><span class="text-muted">{{ __('Brand') }}</span><strong>{{ $post->brand->name }}</strong></li>
                @endif
                @if ($post->location)
                    <li class="list-group-item d-flex justify-content-between px-0"><span class="text-muted">{{ __('Location') }}</span><strong>{{ $post->location->name }}</strong></li>
                @endif
            </ul>
        </x-valex.card>

        @can('update', $post)
            @if ($post->status === 'draft' || $post->status === 'pending_approval')
                <x-valex.card :title="__('Actions')">
                    @if ($post->status === 'draft')
                        <a href="{{ route('posts.edit', $post) }}" class="btn btn-primary btn-block mb-2"><i class="fe fe-edit"></i> {{ __('Edit') }}</a>
                        <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('{{ __('Delete this post?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-block"><i class="fe fe-trash-2"></i> {{ __('Delete') }}</button>
                        </form>
                    @else
                        <p class="text-muted text-center mb-0">{{ __('Awaiting approval from administrator') }}</p>
                    @endif
                </x-valex.card>
            @endif
        @endcan

        @can('approve', $post)
            @if ($post->status === 'pending_approval')
                <x-valex.card :title="__('Admin Actions')">
                    <form action="{{ route('posts.approve', $post) }}" method="POST" class="mb-2">
                        @csrf
                        <button class="btn btn-success btn-block" type="submit"><i class="fe fe-check-circle"></i> {{ __('Approve') }}</button>
                    </form>
                    <form action="{{ route('posts.reject', $post) }}" method="POST">
                        @csrf
                        <button class="btn btn-danger btn-block" type="submit"><i class="fe fe-x-circle"></i> {{ __('Reject') }}</button>
                    </form>
                </x-valex.card>
            @endif
        @endcan
    </div>
</div>
@endsection
