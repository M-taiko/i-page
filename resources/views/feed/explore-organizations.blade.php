@extends('layouts.mobile-shell')

@section('title', __('Discover Organizations') . ' - i-Page')

@section('app-bar')
    <a href="{{ route('user.feed') }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-bar-title">{{ __('Discover') }}</div>
    <div class="app-bar-actions">
        <a href="{{ route('user.explore-channels') }}" class="app-bar-icon-btn" aria-label="{{ __('Channels') }}">
            <i class="bi bi-chat-dots"></i>
        </a>
    </div>
@endsection

@section('extra-styles')
    .discover-tabs { display: flex; background-color: var(--surface-bg); border-bottom: 1px solid var(--surface-border); position: sticky; top: 0; z-index: 10; }
    .discover-tab { flex: 1; text-align: center; padding: var(--space-3) var(--space-2); text-decoration: none; color: var(--text-tertiary); font-size: var(--text-sm); font-weight: var(--font-weight-semibold); border-bottom: 2px solid transparent; }
    .discover-tab.active { color: var(--primary-600); border-bottom-color: var(--primary-600); }

    .orgs-content { padding: var(--space-4); }
    .orgs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: var(--space-3); }

    .org-card { background-color: var(--surface-bg); border: 1px solid var(--surface-border); border-radius: var(--radius-lg); padding: var(--space-4); display: flex; flex-direction: column; align-items: center; text-align: center; gap: var(--space-2); }

    .org-card-avatar { width: 56px; height: 56px; border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); font-size: var(--text-xl); }
    .org-card-name { font-weight: var(--font-weight-semibold); font-size: var(--text-sm); color: var(--text-primary); word-break: break-word; }
    .org-card-stats { display: flex; justify-content: center; gap: var(--space-3); font-size: 11px; color: var(--text-tertiary); }
    .org-card-stat { display: flex; align-items: center; gap: 3px; }

    .org-card-actions { display: flex; gap: var(--space-2); width: 100%; margin-top: var(--space-1); }
    .org-card-actions a, .org-card-actions button {
        flex: 1; padding: var(--space-2); border-radius: var(--radius-md); font-size: var(--text-xs); font-weight: var(--font-weight-medium);
        text-decoration: none; text-align: center; cursor: pointer; border: none;
    }
    .org-card-actions .btn-enter { background-color: var(--primary-600); color: white; }
    .org-card-actions .btn-collect { background-color: var(--surface-hover); color: var(--primary-600); }

    .empty-state { text-align: center; padding: var(--space-10) var(--space-4); color: var(--text-secondary); grid-column: 1 / -1; }
@endsection

@section('content')
    @php
        $avatarPalette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777'];
        $colorFor = fn($seed) => $avatarPalette[crc32($seed) % count($avatarPalette)];
    @endphp

    <nav class="discover-tabs">
        <span class="discover-tab active"><i class="bi bi-building"></i> {{ __('Organizations') }}</span>
        <a href="{{ route('user.explore-channels') }}" class="discover-tab"><i class="bi bi-chat-dots"></i> {{ __('Channels') }}</a>
    </nav>

    <div class="orgs-content">
        @if($organizations->count() > 0)
            <div class="orgs-grid">
                @foreach($organizations as $org)
                    <div class="org-card">
                        <div class="org-card-avatar" style="background-color: {{ $colorFor($org->name) }};">
                            {{ substr($org->name, 0, 1) }}
                        </div>
                        <div class="org-card-name">{{ Str::limit($org->name, 22) }}</div>
                        <div class="org-card-stats">
                            <span class="org-card-stat"><i class="bi bi-people"></i> {{ $org->users_count ?? 0 }}</span>
                            <span class="org-card-stat"><i class="bi bi-chat-dots"></i> {{ $org->channels_count ?? 0 }}</span>
                            <span class="org-card-stat"><i class="bi bi-newspaper"></i> {{ $org->posts_count ?? 0 }}</span>
                        </div>
                        <div class="org-card-actions">
                            <a href="{{ route('guest.organization-detail', $org->id) }}" class="btn-enter">{{ __('Open') }}</a>
                            <button type="button" class="btn-collect" onclick="openAddToCollection('organization', {{ $org->id }}, '{{ $org->name }}')">
                                <i class="bi bi-folder-plus"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($organizations->hasPages())
                <div style="display: flex; justify-content: center; padding-top: var(--space-6);">
                    {{ $organizations->onEachSide(1)->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-building" style="font-size: 2.5rem; display: block; margin-bottom: var(--space-3); opacity: 0.5;"></i>
                <p>{{ __('No organizations available yet') }}</p>
            </div>
        @endif
    </div>
@endsection

@section('modals')
    @include('collections._add-to-collection-modal')
@endsection
