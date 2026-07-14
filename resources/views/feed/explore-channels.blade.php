@extends('layouts.mobile-shell')

@section('title', __('Discover Channels') . ' - i-Page')

@section('app-bar')
    <a href="{{ route('user.explore-organizations') }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-bar-title">{{ __('Discover') }}</div>
    <div class="app-bar-actions">
        <a href="{{ route('user.explore-organizations') }}" class="app-bar-icon-btn" aria-label="{{ __('Organizations') }}">
            <i class="bi bi-building"></i>
        </a>
    </div>
@endsection

@section('extra-styles')
    .discover-tabs { display: flex; background-color: var(--surface-bg); border-bottom: 1px solid var(--surface-border); position: sticky; top: 0; z-index: 10; }
    .discover-tab { flex: 1; text-align: center; padding: var(--space-3) var(--space-2); text-decoration: none; color: var(--text-tertiary); font-size: var(--text-sm); font-weight: var(--font-weight-semibold); border-bottom: 2px solid transparent; }
    .discover-tab.active { color: var(--primary-600); border-bottom-color: var(--primary-600); }

    .channels-content { padding: var(--space-4); }
    .channels-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: var(--space-3); }

    .channel-card { background-color: var(--surface-bg); border: 1px solid var(--surface-border); border-radius: var(--radius-lg); padding: var(--space-4); display: flex; flex-direction: column; align-items: center; text-align: center; gap: var(--space-2); }

    .channel-card-icon { width: 48px; height: 48px; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--primary-600), var(--secondary-600)); display: flex; align-items: center; justify-content: center; color: white; font-size: var(--text-lg); }
    .channel-card-name { font-weight: var(--font-weight-semibold); font-size: var(--text-sm); color: var(--text-primary); word-break: break-word; }

    .channel-card-badge { display: inline-block; padding: 2px 8px; border-radius: var(--radius-full); font-size: 10px; font-weight: var(--font-weight-medium); text-transform: uppercase; letter-spacing: 0.5px; }
    .channel-card-badge.public { background-color: var(--primary-50); color: var(--primary-700); }
    .channel-card-badge.private { background-color: var(--warning-50); color: var(--warning-700); }

    .channel-card-stats { font-size: 11px; color: var(--text-tertiary); }

    .channel-card-actions { display: flex; gap: var(--space-2); width: 100%; margin-top: var(--space-1); }
    .channel-card-actions a, .channel-card-actions button {
        flex: 1; padding: var(--space-2); border-radius: var(--radius-md); font-size: var(--text-xs); font-weight: var(--font-weight-medium);
        text-decoration: none; text-align: center; cursor: pointer; border: none;
    }
    .channel-card-actions .btn-view { background-color: var(--surface-hover); color: var(--text-primary); }
    .channel-card-actions .btn-join { background-color: var(--primary-600); color: white; }
    .channel-card-actions .btn-collect { background-color: var(--surface-hover); color: var(--primary-600); }

    .empty-state { text-align: center; padding: var(--space-10) var(--space-4); color: var(--text-secondary); grid-column: 1 / -1; }
@endsection

@section('content')
    <nav class="discover-tabs">
        <a href="{{ route('user.explore-organizations') }}" class="discover-tab"><i class="bi bi-building"></i> {{ __('Organizations') }}</a>
        <span class="discover-tab active"><i class="bi bi-chat-dots"></i> {{ __('Channels') }}</span>
    </nav>

    <div class="channels-content">
        @if($channels->count() > 0)
            <div class="channels-grid">
                @foreach($channels as $channel)
                    <div class="channel-card">
                        <div class="channel-card-icon"><i class="bi bi-chat-dots"></i></div>
                        <div class="channel-card-name">{{ Str::limit($channel->name, 22) }}</div>
                        <span class="channel-card-badge {{ strtolower($channel->type) }}">{{ ucfirst($channel->type) }}</span>
                        <div class="channel-card-stats">
                            {{ $channel->users_count ?? 0 }} {{ __('members') }} · {{ $channel->posts_count ?? 0 }} {{ __('posts') }}
                        </div>

                        <div class="channel-card-actions">
                            <a href="{{ route('guest.channel-detail', [$channel->organization_id, $channel->slug]) }}" class="btn-view">
                                <i class="bi bi-eye"></i> {{ __('View') }}
                            </a>
                            @if(in_array($channel->id, $subscribedChannelIds ?? []))
                                <button type="button" class="btn-collect" onclick="openAddToCollection('channel', {{ $channel->id }}, '{{ $channel->name }}')">
                                    <i class="bi bi-folder-plus"></i>
                                </button>
                            @else
                                <form action="{{ route('dashboard.channels.subscribe', [$channel->organization_id, $channel->id]) }}" method="POST" style="flex:1;">
                                    @csrf
                                    <button type="submit" class="btn-join" style="width:100%;">
                                        <i class="bi bi-plus"></i> {{ __('Join') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($channels->hasPages())
                <div style="display: flex; justify-content: center; padding-top: var(--space-6);">
                    {{ $channels->onEachSide(1)->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-chat-dots" style="font-size: 2.5rem; display: block; margin-bottom: var(--space-3); opacity: 0.5;"></i>
                <p>{{ __('No channels available yet') }}</p>
            </div>
        @endif
    </div>
@endsection

@section('modals')
    @include('collections._add-to-collection-modal')
@endsection
