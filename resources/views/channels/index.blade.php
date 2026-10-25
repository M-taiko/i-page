@extends('layouts.valex')

@section('title', __('View Channels'))

@section('page-header')
    <x-valex.page-header :title="__('All Channels')" :subtitle="__('View and manage all channels in your organization')">
        <x-slot:actions>
            <a href="{{ route('dashboard.channels.create', $organization) }}" class="btn btn-primary"><i class="fe fe-plus-circle"></i> {{ __('Create New Channel') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<x-valex.card>
    <form method="GET" action="{{ route('dashboard.channels.index', $organization) }}" class="form-row align-items-center">
        <div class="col-md-6 mb-2 mb-md-0">
            <input type="text" name="q" class="form-control" placeholder="{{ __('Search channels by name...') }}" value="{{ request('q') }}">
        </div>
        <div class="col-md-3 mb-2 mb-md-0">
            <select name="type" class="form-control">
                <option value="">{{ __('All Types') }}</option>
                <option value="public" @selected(request('type') === 'public')>{{ __('Public Only') }}</option>
                <option value="private" @selected(request('type') === 'private')>{{ __('Private Only') }}</option>
            </select>
        </div>
        <div class="col-md-3"><button type="submit" class="btn btn-primary btn-block"><i class="fe fe-search"></i> {{ __('Search') }}</button></div>
    </form>
</x-valex.card>

@forelse ($channels as $channel)
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <h5 class="mb-1">{{ $channel->name }}</h5>
                    <p class="text-muted mb-0">{{ $channel->description ?? __('No description') }}</p>
                </div>
                <div>
                    <x-valex.badge :variant="$channel->type === 'public' ? 'success' : 'pink'"><i class="fe fe-{{ $channel->type === 'public' ? 'globe' : 'lock' }}"></i> {{ $channel->type === 'public' ? __('Public') : __('Private') }}</x-valex.badge>
                </div>
            </div>

            <div class="row text-center border-top border-bottom my-3 py-3">
                <div class="col-4"><h4 class="text-primary mb-0">{{ $channel->users()->count() }}</h4><small class="text-muted text-uppercase">{{ __('Members') }}</small></div>
                <div class="col-4"><h4 class="text-primary mb-0">{{ $channel->posts()->count() }}</h4><small class="text-muted text-uppercase">{{ __('Posts') }}</small></div>
                <div class="col-4"><h4 class="text-primary mb-0">{{ $channel->created_at->diffForHumans(parts: 1, syntax: 'short') }}</h4><small class="text-muted text-uppercase">{{ __('Created') }}</small></div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('dashboard.channels.show', [$organization, $channel->id]) }}" class="btn btn-sm btn-outline-primary mx-1"><i class="fe fe-eye"></i> {{ __('View') }}</a>
                <a href="{{ route('dashboard.channels.edit', [$organization, $channel->id]) }}" class="btn btn-sm btn-outline-warning mx-1"><i class="fe fe-edit"></i> {{ __('Edit') }}</a>
                <form action="{{ route('dashboard.channels.destroy', [$organization, $channel->id]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('Are you sure you want to delete this channel?') }}')"><i class="fe fe-trash-2"></i> {{ __('Delete') }}</button>
                </form>
            </div>
        </div>
    </div>
@empty
    <x-valex.empty-state :title="__('No channels yet')" :message="__('Create your first channel now')" icon="inbox">
        <a href="{{ route('dashboard.channels.create', $organization) }}" class="btn btn-primary mt-3"><i class="fe fe-plus-circle"></i> {{ __('Create First Channel') }}</a>
    </x-valex.empty-state>
@endforelse

@if ($channels->hasPages())
    <div class="d-flex justify-content-center mt-4">{{ $channels->links('pagination::bootstrap-4') }}</div>
@endif
@endsection
