@extends('layouts.app-modern')

@section('title', $channel->name)

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="page-header-top">
        <div class="page-header-info" style="display: flex; align-items: center; gap: var(--space-4);">
            @if ($channel->logo_path)
                <img src="{{ Storage::url($channel->logo_path) }}" alt="{{ $channel->name }}" style="width: 60px; height: 60px; border-radius: var(--radius-lg);">
            @endif
            <div>
                <h1>{{ $channel->name }}</h1>
                <p style="margin: var(--space-1) 0 0;">{{ ucfirst($channel->type) }} {{ __('Channel') }}</p>
            </div>
        </div>
        <div class="page-header-actions">
            <x-btn href="{{ route('dashboard.channels.edit', [session('current_organization_id'), $channel], $channel) }}" variant="primary" icon="pencil">
                {{ __('Edit') }}
            </x-btn>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-8);">
    <!-- Main Content -->
    <div>
        <x-card-modern title="{{ __('Channel Information') }}" icon="chat-dots" elevated>
            <div style="display: grid; gap: var(--space-6);">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6);">
                    <div>
                        <h4 style="margin: 0 0 var(--space-2); color: var(--text-secondary); font-size: var(--text-sm); font-weight: var(--font-weight-medium);">{{ __('Type') }}</h4>
                        <p style="margin: 0;">{{ ucfirst($channel->type) }}</p>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 var(--space-2); color: var(--text-secondary); font-size: var(--text-sm); font-weight: var(--font-weight-medium);">{{ __('Audience') }}</h4>
                        <p style="margin: 0;">{{ ucfirst($channel->audience_profile) }}</p>
                    </div>
                </div>

                @if ($channel->audience_count)
                    <div>
                        <h4 style="margin: 0 0 var(--space-2); color: var(--text-secondary); font-size: var(--text-sm); font-weight: var(--font-weight-medium);">{{ __('Expected Audience') }}</h4>
                        <p style="margin: 0;">{{ number_format($channel->audience_count) }} {{ __('users') }}</p>
                    </div>
                @endif

                <div style="padding-top: var(--space-4); border-top: 1px solid var(--surface-border);">
                    <h4 style="margin: 0 0 var(--space-2); color: var(--text-secondary); font-size: var(--text-sm); font-weight: var(--font-weight-medium);">{{ __('Admin') }}</h4>
                    <p style="margin: 0;">{{ $channel->admin?->full_name ?? __('Unassigned') }}</p>
                </div>
            </div>
        </x-card-modern>

        <!-- Subscribers -->
        <x-card-modern title="{{ __('Subscribers') }} ({{ $channel->users->count() }})" icon="people" elevated style="margin-top: var(--space-8);">
            @if ($channel->users->count() > 0)
                <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                    @foreach ($channel->users->take(10) as $user)
                        <div style="padding-bottom: var(--space-4); border-bottom: 1px solid var(--surface-border); display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: var(--space-3);">
                                <div style="width: 36px; height: 36px; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--primary-100), var(--secondary-100)); display: flex; align-items: center; justify-content: center; font-weight: var(--font-weight-bold); color: var(--primary-700); font-size: var(--text-xs);">
                                    {{ $user->initials }}
                                </div>
                                <div>
                                    <strong style="display: block; font-size: var(--text-sm);">{{ $user->full_name }}</strong>
                                    <small style="color: var(--text-tertiary);">{{ $user->email }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state-modern
                    title="{{ __('No Subscribers') }}"
                    message="{{ __('This channel has no subscribers yet.') }}"
                    icon="people"
                />
            @endif
        </x-card-modern>
    </div>

    <!-- Sidebar -->
    <div>
        <x-card-modern title="{{ __('Actions') }}" elevated>
            <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                <x-btn href="{{ route('dashboard.channels.edit', [session('current_organization_id'), $channel], $channel) }}" variant="primary" class="w-100">
                    {{ __('Edit Channel') }}
                </x-btn>

                <form action="{{ route('dashboard.channels.destroy', [session('current_organization_id'), $channel], $channel) }}" method="POST" style="display: contents;">
                    @csrf
                    @method('DELETE')
                    <x-btn type="submit" variant="danger" class="w-100" onclick="return confirm('{{ __('Are you sure?') }}')">
                        {{ __('Delete Channel') }}
                    </x-btn>
                </form>
            </div>
        </x-card-modern>

        <x-card-modern title="{{ __('Info') }}" style="margin-top: var(--space-6);">
            <div style="display: flex; flex-direction: column; gap: var(--space-4); font-size: var(--text-sm);">
                <div>
                    <small style="display: block; color: var(--text-tertiary); font-weight: var(--font-weight-medium); margin-bottom: var(--space-1);">{{ __('Subscribers') }}</small>
                    <strong style="font-size: var(--text-2xl);">{{ $channel->users->count() }}</strong>
                </div>

                <div style="padding-top: var(--space-4); border-top: 1px solid var(--surface-border);">
                    <small style="display: block; color: var(--text-tertiary); font-weight: var(--font-weight-medium); margin-bottom: var(--space-1);">{{ __('Created') }}</small>
                    <small>{{ $channel->created_at->diffForHumans() }}</small>
                </div>
            </div>
        </x-card-modern>
    </div>
</div>

<style>
    @media (max-width: 1024px) {
        [style*="grid-template-columns: 2fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection
