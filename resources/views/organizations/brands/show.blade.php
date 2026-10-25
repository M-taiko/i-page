@extends('layouts.valex')

@section('title', $brand->name)

@section('page-header')
    <x-valex.page-header :title="$brand->name" :subtitle="__('Brand')">
        <x-slot:actions>
            <a href="{{ route('organizations.brands.index', $organization) }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back to Brands') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<x-valex.hero :title="$brand->name" :subtitle="$brand->description ?? __('No description')">
    <x-slot:actions>
        <a href="{{ route('organizations.brands.edit', [$organization, $brand]) }}" class="btn btn-light mx-1"><i class="fe fe-edit"></i> {{ __('Edit') }}</a>
        <form action="{{ route('organizations.brands.destroy', [$organization, $brand]) }}" method="POST" class="d-inline"
              onsubmit="return confirm('{{ __('Delete this brand? Its channels will be unassigned, not deleted.') }}')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"><i class="fe fe-trash-2"></i> {{ __('Delete') }}</button>
        </form>
    </x-slot:actions>
</x-valex.hero>

<div class="row row-sm">
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Channels')" :value="$channels->count()" icon="message-circle" variant="primary" /></div>
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Followers')" :value="$brand->followers_count" icon="users" variant="success" /></div>
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Posts')" :value="$postsCount" icon="file-text" variant="warning" /></div>
    <div class="col-sm-6 col-xl-3"><x-valex.stat-card :title="__('Status')" :value="$brand->is_active ? __('Active') : __('Inactive')" icon="toggle-right" variant="info" /></div>
</div>

<x-valex.card :title="__('Channels')">
    <x-slot:actions>
        <a href="{{ route('tenant.channels.create') }}" class="btn btn-sm btn-outline-primary"><i class="fe fe-plus"></i> {{ __('New Channel') }}</a>
    </x-slot:actions>

    <ul class="list-group list-group-flush">
        @forelse ($channels as $channel)
            <li class="list-group-item d-flex align-items-center px-0">
                <span class="avatar avatar-md brround bg-primary-transparent text-primary mx-2"><i class="fe fe-message-circle"></i></span>
                <div class="flex-grow-1">
                    <div class="font-weight-bold">{{ $channel->name }}</div>
                    <small class="text-muted">{{ $channel->users_count }} {{ __('members') }} · {{ $channel->posts_count }} {{ __('posts') }}</small>
                </div>
                <x-valex.badge :variant="$channel->type === 'public' ? 'primary' : 'warning'" class="mx-2">{{ $channel->type }}</x-valex.badge>
                <a href="{{ route('tenant.channels.show', $channel) }}" class="btn btn-sm btn-outline-secondary"><i class="fe fe-arrow-right"></i></a>
            </li>
        @empty
            <li class="list-group-item text-center text-muted">{{ __('No channels under this brand yet.') }}</li>
        @endforelse
    </ul>
</x-valex.card>
@endsection
