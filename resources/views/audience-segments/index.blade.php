@extends('layouts.app-modern')

@section('content')
<style>
    .segments-header {
        background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
        color: white;
        padding: 2rem 1.5rem;
        border-radius: 16px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .segments-header h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
    .segments-header p { margin: 0; opacity: 0.9; font-size: 0.875rem; }

    .btn-new-segment {
        background-color: white;
        color: var(--primary-700);
        border: none;
        padding: 0.6rem 1.25rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }

    .btn-new-segment:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); color: var(--primary-700); }

    .segments-table-card {
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: 14px;
        overflow: hidden;
    }

    .segments-table { width: 100%; border-collapse: collapse; }

    .segments-table thead th {
        background-color: var(--surface-bg-secondary);
        padding: 0.9rem 1.25rem;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-tertiary);
        text-align: left;
        border-bottom: 1px solid var(--surface-border);
    }

    .segments-table tbody td {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--surface-border);
        font-size: 0.875rem;
        vertical-align: middle;
    }

    .segments-table tbody tr:last-child td { border-bottom: none; }
    .segments-table tbody tr:hover { background-color: var(--surface-hover); }

    .segment-name { font-weight: 600; color: var(--text-primary); }
    .segment-desc { color: var(--text-tertiary); font-size: 0.8rem; }

    .rule-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.25rem 0.6rem;
        background-color: var(--primary-50);
        color: var(--primary-700);
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-pill {
        padding: 0.3rem 0.7rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .status-pill.active { background-color: var(--success-50); color: var(--success-700); }
    .status-pill.inactive { background-color: var(--neutral-100); color: var(--neutral-600); }

    .row-actions { display: flex; gap: 0.5rem; justify-content: flex-end; }

    .row-action-btn {
        width: 32px; height: 32px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 8px;
        border: 1px solid var(--surface-border);
        background: none;
        color: var(--text-secondary);
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .row-action-btn:hover { background-color: var(--surface-hover); }
    .row-action-btn.danger:hover { background-color: var(--danger-50); color: var(--danger-600); border-color: var(--danger-200); }

    .empty-segments {
        text-align: center;
        padding: 4rem 1.5rem;
        background-color: var(--surface-bg);
        border: 1px dashed var(--surface-border);
        border-radius: 14px;
        color: var(--text-secondary);
    }

    @media (max-width: 768px) {
        .segments-table thead { display: none; }
        .segments-table, .segments-table tbody, .segments-table tr, .segments-table td { display: block; width: 100%; }
        .segments-table tbody tr { padding: 1rem 1.25rem; border-bottom: 1px solid var(--surface-border); }
        .segments-table tbody td { padding: 0.25rem 0; border: none; }
        .row-actions { justify-content: flex-start; margin-top: 0.5rem; }
    }
</style>

<div class="container-lg py-4">
    <div class="segments-header">
        <div>
            <h1>Audience Segments</h1>
            <p>Precisely target who receives your posts and messages</p>
        </div>
        @can('create', App\Models\AudienceSegment::class)
            <a href="{{ route('audience-segments.create') }}" class="btn-new-segment">
                <i class="bi bi-plus-lg"></i> New Segment
            </a>
        @endcan
    </div>

    @if($segments->isEmpty())
        <div class="empty-segments">
            <i class="bi bi-people" style="font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 1rem;"></i>
            <p class="mb-0">No audience segments yet. Create one to start targeting specific groups of users.</p>
        </div>
    @else
        <div class="segments-table-card">
            <table class="segments-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Rules</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($segments as $segment)
                        <tr>
                            <td class="segment-name">{{ $segment->name }}</td>
                            <td class="segment-desc">{{ $segment->description ?? '—' }}</td>
                            <td>
                                @php
                                    $rules = is_array($segment->rules) ? $segment->rules : (json_decode($segment->rules, true) ?? []);
                                    $ruleCount = count(array_filter($rules, fn($r) => !empty($r['value'])));
                                @endphp
                                <span class="rule-count-badge"><i class="bi bi-funnel"></i> {{ $ruleCount }} rule{{ $ruleCount !== 1 ? 's' : '' }}</span>
                            </td>
                            <td>
                                <span class="status-pill {{ $segment->is_active ? 'active' : 'inactive' }}">
                                    {{ $segment->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    @can('update', $segment)
                                        <a href="{{ route('audience-segments.edit', $segment) }}" class="row-action-btn" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $segment)
                                        <form action="{{ route('audience-segments.destroy', $segment) }}" method="POST" onsubmit="return confirm('Delete this segment?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="row-action-btn danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $segments->links() }}
        </div>
    @endif
</div>
@endsection
