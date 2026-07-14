@extends('layouts.mobile-shell')

@section('title', __('Search Organizations') . ' - i-Page')

@section('app-bar')
    <a href="{{ route('guest.home') }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-bar-title">{{ __('Organizations') }}</div>
@endsection

@section('extra-styles')
    .search-bar-wrap {
        padding: var(--space-4);
        background-color: var(--surface-bg);
        border-bottom: 1px solid var(--surface-border);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .search-input-group {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        background-color: var(--surface-bg-secondary);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-full);
        padding: var(--space-2) var(--space-4);
    }

    .search-input-group i { color: var(--text-tertiary); }

    .search-input-group input {
        flex: 1;
        border: none;
        background: none;
        outline: none;
        font-size: var(--text-sm);
        color: var(--text-primary);
    }

    .orgs-content { padding: var(--space-4); }

    .orgs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: var(--space-3);
    }

    .org-card {
        background-color: var(--surface-bg);
        border-radius: var(--radius-lg);
        border: 1px solid var(--surface-border);
        padding: var(--space-4);
        text-align: center;
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .org-card:active { background-color: var(--surface-hover); }

    .org-avatar {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-xl);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: var(--font-weight-bold);
        font-size: var(--text-xl);
        margin-bottom: var(--space-3);
    }

    .org-name {
        font-weight: var(--font-weight-semibold);
        font-size: var(--text-sm);
        margin-bottom: var(--space-2);
        word-break: break-word;
    }

    .org-stats {
        display: flex;
        justify-content: center;
        gap: var(--space-3);
        font-size: 11px;
        color: var(--text-tertiary);
    }

    .org-stat { display: flex; align-items: center; gap: 3px; }

    .empty-state { text-align: center; padding: var(--space-10) var(--space-4); color: var(--text-secondary); }
@endsection

@section('content')
    @php
        $avatarPalette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777'];
        $colorFor = fn($seed) => $avatarPalette[crc32($seed) % count($avatarPalette)];
    @endphp

    <div class="search-bar-wrap">
        <form action="{{ route('guest.search-organizations') }}" method="GET">
            <div class="search-input-group">
                <i class="bi bi-search"></i>
                <input type="text" name="q" placeholder="{{ __('Search organizations...') }}" value="{{ $search }}" autofocus>
            </div>
        </form>
    </div>

    <div class="orgs-content">
        @if ($organizations->count() > 0)
            <div class="orgs-grid">
                @foreach ($organizations as $org)
                    <a href="{{ route('guest.organization-detail', $org) }}" class="org-card">
                        <div class="org-avatar" style="background-color: {{ $colorFor($org->name) }};">
                            {{ substr($org->name, 0, 1) }}
                        </div>
                        <div class="org-name">{{ Str::limit($org->name, 22) }}</div>
                        <div class="org-stats">
                            <span class="org-stat"><i class="bi bi-people"></i> {{ $org->users_count }}</span>
                            <span class="org-stat"><i class="bi bi-chat-dots"></i> {{ $org->channels_count }}</span>
                            <span class="org-stat"><i class="bi bi-newspaper"></i> {{ $org->posts_count }}</span>
                        </div>
                    </a>
                @endforeach
            </div>

            @if ($organizations->hasPages())
                <div style="display: flex; justify-content: center; padding-top: var(--space-6);">
                    {{ $organizations->onEachSide(1)->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-search" style="font-size: 2.5rem; display: block; margin-bottom: var(--space-3); opacity: 0.5;"></i>
                @if ($search)
                    <p>{{ __('No organizations found for') }} "{{ $search }}"</p>
                @else
                    <p>{{ __('No organizations available') }}</p>
                @endif
            </div>
        @endif
    </div>
@endsection
