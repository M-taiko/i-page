@extends('layouts.mobile-shell')

@section('title', __('Discover People') . ' - i-Page')

@section('app-bar')
    <a href="{{ route('user.feed') }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-bar-title">{{ __('Discover') }}</div>
@endsection

@section('extra-styles')
    .discover-tabs { display: flex; background-color: var(--surface-bg); border-bottom: 1px solid var(--surface-border); position: sticky; top: 0; z-index: 10; }
    .discover-tab { flex: 1; text-align: center; padding: var(--space-3) var(--space-2); text-decoration: none; color: var(--text-tertiary); font-size: var(--text-sm); font-weight: var(--font-weight-semibold); border-bottom: 2px solid transparent; }
    .discover-tab.active { color: var(--primary-600); border-bottom-color: var(--primary-600); }

    .people-search { padding: var(--space-4); }
    .people-search-input {
        width: 100%; padding: var(--space-3) var(--space-4); border-radius: var(--radius-full);
        border: 1px solid var(--surface-border); background-color: var(--surface-bg); color: var(--text-primary); font-size: var(--text-sm);
    }

    .people-results { padding: 0 var(--space-4) var(--space-4); display: flex; flex-direction: column; gap: var(--space-2); }
    .person-row { display: flex; align-items: center; gap: var(--space-3); background-color: var(--surface-bg); border: 1px solid var(--surface-border); border-radius: var(--radius-lg); padding: var(--space-3); }
    .person-avatar { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); font-size: var(--text-sm); flex-shrink: 0; overflow: hidden; background: linear-gradient(135deg, var(--primary-500), var(--secondary-500)); }
    .person-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .person-info { flex: 1; min-width: 0; }
    .person-name { font-weight: var(--font-weight-semibold); font-size: var(--text-sm); color: var(--text-primary); }
    .person-handle { font-size: var(--text-xs); color: var(--text-tertiary); }
    .person-badge { font-size: 10px; padding: 2px 8px; border-radius: var(--radius-full); background-color: var(--primary-50); color: var(--primary-600); font-weight: var(--font-weight-semibold); }

    .empty-state { text-align: center; padding: var(--space-10) var(--space-4); color: var(--text-secondary); }
@endsection

@section('content')
    <nav class="discover-tabs">
        <a href="{{ route('user.explore-organizations') }}" class="discover-tab"><i class="bi bi-building"></i> {{ __('Organizations') }}</a>
        <a href="{{ route('user.explore-channels') }}" class="discover-tab"><i class="bi bi-chat-dots"></i> {{ __('Channels') }}</a>
        <span class="discover-tab active"><i class="bi bi-people"></i> {{ __('People') }}</span>
    </nav>

    <div class="people-search">
        <input type="text" id="peopleSearchInput" autocomplete="off" class="people-search-input" placeholder="{{ __('Search by name or @username…') }}">
    </div>

    <div id="peopleResults" class="people-results">
        <div class="empty-state">
            <i class="bi bi-people" style="font-size: 2.5rem; display: block; margin-bottom: var(--space-3); opacity: 0.5;"></i>
            <p>{{ __('Start typing a name or username to find people.') }}</p>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    (function () {
        const input = document.getElementById('peopleSearchInput');
        const resultsBox = document.getElementById('peopleResults');
        let debounceTimer = null;

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str || '';
            return div.innerHTML;
        }

        function emptyState(message) {
            return `<div class="empty-state"><i class="bi bi-people" style="font-size: 2.5rem; display: block; margin-bottom: var(--space-3); opacity: 0.5;"></i><p>${message}</p></div>`;
        }

        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const query = input.value.trim();

            if (query.length < 1) {
                resultsBox.innerHTML = emptyState('{{ __('Start typing a name or username to find people.') }}');
                return;
            }

            debounceTimer = setTimeout(() => runSearch(query), 300);
        });

        function runSearch(query) {
            fetch('{{ route('api.people.search') }}?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => renderResults(data.users || []))
                .catch(() => renderResults([]));
        }

        function renderResults(users) {
            if (users.length === 0) {
                resultsBox.innerHTML = emptyState('{{ __('No people found.') }}');
                return;
            }

            resultsBox.innerHTML = users.map(u => `
                <div class="person-row">
                    <div class="person-avatar">
                        ${u.avatar_path ? `<img src="${u.avatar_path}" alt="">` : escapeHtml(u.initials)}
                    </div>
                    <div class="person-info">
                        <div class="person-name">${escapeHtml(u.full_name)}</div>
                        <div class="person-handle">${u.username ? '@' + escapeHtml(u.username) : '{{ __('No username set') }}'}</div>
                    </div>
                    ${u.profile_level === 'business' ? `<span class="person-badge">{{ __('Business') }}</span>` : ''}
                </div>
            `).join('');
        }
    })();
</script>
@endsection
