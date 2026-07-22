@extends('layouts.mobile-shell')

@section('title', __('Notifications') . ' - i-Page')

@section('app-bar')
    <a href="{{ route('user.feed') }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-bar-title">{{ __('Notifications') }}</div>
    <div class="app-bar-actions">
        <form action="{{ route('user.notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="app-bar-icon-btn" style="font-size: var(--text-sm);" title="{{ __('Mark all as read') }}">
                <i class="bi bi-check2-all"></i>
            </button>
        </form>
    </div>
@endsection

@section('extra-styles')
    .notif-list { padding: var(--space-4); display: flex; flex-direction: column; gap: var(--space-2); }

    .notif-item {
        display: flex;
        align-items: flex-start;
        gap: var(--space-3);
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-lg);
        padding: var(--space-3);
    }

    .notif-item.unread { border-color: var(--primary-200); background-color: var(--primary-50); }

    .notif-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, var(--primary-600), var(--secondary-600));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    .notif-body { flex: 1; min-width: 0; }
    .notif-title { font-size: var(--text-sm); color: var(--text-primary); font-weight: var(--font-weight-medium); }
    .notif-time { font-size: var(--text-xs); color: var(--text-tertiary); margin-top: 2px; }
@endsection

@section('content')
    <div class="notif-list">
        @forelse($notifications as $notification)
            <div class="notif-item {{ $notification->isRead() ? '' : 'unread' }}">
                <div class="notif-icon"><i class="bi bi-bell"></i></div>
                <div class="notif-body">
                    @if($notification->data['link'] ?? null)
                        <a href="{{ $notification->data['link'] }}" class="notif-title" style="color: inherit; text-decoration: none;">
                            {{ $notification->data['message'] ?? ucfirst(str_replace('_', ' ', $notification->type)) }}
                        </a>
                    @else
                        <div class="notif-title">{{ $notification->data['message'] ?? ucfirst(str_replace('_', ' ', $notification->type)) }}</div>
                    @endif
                    <div class="notif-time">{{ $notification->created_at->diffForHumans() }}</div>
                </div>
                @if(!$notification->isRead())
                    <form action="{{ route('user.notifications.read', $notification->id) }}" method="POST">
                        @csrf
                        <button type="submit" style="border: none; background: none; color: var(--primary-600); cursor: pointer;">
                            <i class="bi bi-check2"></i>
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div style="text-align: center; padding: var(--space-8) var(--space-4); color: var(--text-secondary);">
                <i class="bi bi-bell-slash" style="font-size: 2.5rem; display: block; margin-bottom: var(--space-3); opacity: 0.5;"></i>
                <p>{{ __('No notifications yet') }}</p>
            </div>
        @endforelse

        @if($notifications->hasPages())
            <div style="display: flex; justify-content: center; padding-top: var(--space-2);">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection
