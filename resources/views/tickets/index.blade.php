@extends('layouts.app-modern')

@section('content')
<style>
    .tickets-header {
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

    .tickets-header h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
    .tickets-header p { margin: 0; opacity: 0.9; font-size: 0.875rem; }

    .btn-new-ticket {
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

    .btn-new-ticket:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); color: var(--primary-700); }

    .status-filters {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .status-filter-pill {
        padding: 0.5rem 1.1rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        border: 1px solid var(--surface-border);
        color: var(--text-secondary);
        background-color: var(--surface-bg);
        transition: all 0.15s ease;
    }

    .status-filter-pill:hover { background-color: var(--surface-hover); color: var(--text-primary); }

    .status-filter-pill.active {
        background-color: var(--primary-600);
        border-color: var(--primary-600);
        color: white;
    }

    .tickets-table-card {
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: 14px;
        overflow: hidden;
    }

    .tickets-table { width: 100%; border-collapse: collapse; }

    .tickets-table thead th {
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

    .tickets-table tbody td {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--surface-border);
        font-size: 0.875rem;
        vertical-align: middle;
    }

    .tickets-table tbody tr:last-child td { border-bottom: none; }
    .tickets-table tbody tr:hover { background-color: var(--surface-hover); }

    .ticket-number { font-family: monospace; font-size: 0.8rem; color: var(--text-tertiary); }
    .ticket-title-link { color: var(--text-primary); font-weight: 600; text-decoration: none; }
    .ticket-title-link:hover { color: var(--primary-600); }

    .badge-pill {
        padding: 0.3rem 0.7rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        white-space: nowrap;
    }

    .badge-pill.status-open { background-color: var(--primary-50); color: var(--primary-700); }
    .badge-pill.status-in_progress { background-color: var(--info-50); color: var(--info-700); }
    .badge-pill.status-waiting { background-color: var(--warning-50); color: var(--warning-700); }
    .badge-pill.status-resolved { background-color: var(--success-50); color: var(--success-700); }
    .badge-pill.status-closed { background-color: var(--neutral-100); color: var(--neutral-600); }
    .badge-pill.status-reopened { background-color: var(--danger-50); color: var(--danger-700); }

    .badge-pill.priority-urgent { background-color: var(--danger-50); color: var(--danger-700); }
    .badge-pill.priority-high { background-color: var(--warning-50); color: var(--warning-700); }
    .badge-pill.priority-medium { background-color: var(--info-50); color: var(--info-700); }
    .badge-pill.priority-low { background-color: var(--neutral-100); color: var(--neutral-600); }

    .ticket-assignee { font-size: 0.8rem; color: var(--text-tertiary); }
    .ticket-assignee.unassigned { font-style: italic; }

    .row-action-btn {
        width: 32px; height: 32px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 8px;
        border: 1px solid var(--surface-border);
        color: var(--primary-600);
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .row-action-btn:hover { background-color: var(--primary-50); }

    .empty-tickets {
        text-align: center;
        padding: 4rem 1.5rem;
        background-color: var(--surface-bg);
        border: 1px dashed var(--surface-border);
        border-radius: 14px;
        color: var(--text-secondary);
    }

    @media (max-width: 768px) {
        .tickets-table thead { display: none; }
        .tickets-table, .tickets-table tbody, .tickets-table tr, .tickets-table td { display: block; width: 100%; }
        .tickets-table tbody tr { padding: 1rem 1.25rem; border-bottom: 1px solid var(--surface-border); }
        .tickets-table tbody td { padding: 0.25rem 0; border: none; }
    }
</style>

<div class="container-lg py-4">
    <div class="tickets-header">
        <div>
            <h1>Support Tickets</h1>
            <p>Track and resolve complaints, feedback, and requests</p>
        </div>
        @can('create', App\Models\Ticket::class)
            <a href="{{ route('tickets.create') }}" class="btn-new-ticket">
                <i class="bi bi-plus-lg"></i> New Ticket
            </a>
        @endcan
    </div>

    <div class="status-filters">
        <a href="{{ route('tickets.index') }}" class="status-filter-pill {{ !request('status') ? 'active' : '' }}">All</a>
        <a href="{{ route('tickets.index', ['status' => 'open']) }}" class="status-filter-pill {{ request('status') === 'open' ? 'active' : '' }}">Open</a>
        <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}" class="status-filter-pill {{ request('status') === 'in_progress' ? 'active' : '' }}">In Progress</a>
        <a href="{{ route('tickets.index', ['status' => 'waiting']) }}" class="status-filter-pill {{ request('status') === 'waiting' ? 'active' : '' }}">Waiting</a>
        <a href="{{ route('tickets.index', ['status' => 'resolved']) }}" class="status-filter-pill {{ request('status') === 'resolved' ? 'active' : '' }}">Resolved</a>
        <a href="{{ route('tickets.index', ['status' => 'closed']) }}" class="status-filter-pill {{ request('status') === 'closed' ? 'active' : '' }}">Closed</a>
    </div>

    @if($tickets->isEmpty())
        <div class="empty-tickets">
            <i class="bi bi-ticket-perforated" style="font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 1rem;"></i>
            <p class="mb-0">No tickets found.</p>
        </div>
    @else
        <div class="tickets-table-card">
            <table class="tickets-table">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                        <tr>
                            <td><span class="ticket-number">{{ $ticket->ticket_number }}</span></td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="ticket-title-link">
                                    {{ $ticket->title }}
                                </a>
                            </td>
                            <td>
                                <span class="badge-pill status-{{ $ticket->status }}">{{ str_replace('_', ' ', $ticket->status) }}</span>
                            </td>
                            <td>
                                <span class="badge-pill priority-{{ $ticket->priority }}">{{ $ticket->priority }}</span>
                            </td>
                            <td>
                                @if($ticket->assignee)
                                    <span class="ticket-assignee">{{ $ticket->assignee->full_name }}</span>
                                @else
                                    <span class="ticket-assignee unassigned">Unassigned</span>
                                @endif
                            </td>
                            <td><span class="ticket-assignee">{{ $ticket->opened_at->format('M d, Y') }}</span></td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="row-action-btn">
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection
