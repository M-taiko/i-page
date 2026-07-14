@extends('layouts.app-modern')

@section('content')
<div class="container-lg py-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h1 class="h3">{{ $ticket->title }}</h1>
            <p class="text-muted">
                <code>{{ $ticket->ticket_number }}</code> •
                Opened {{ $ticket->opened_at->diffForHumans() }}
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <span class="badge bg-{{ match($ticket->status) {
                'open' => 'primary',
                'in_progress' => 'info',
                'waiting' => 'warning',
                'resolved' => 'success',
                'closed' => 'secondary',
                default => 'secondary'
            } }} class="me-2">
                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
            </span>
            <span class="badge bg-{{ match($ticket->priority) {
                'urgent' => 'danger',
                'high' => 'warning',
                'medium' => 'info',
                'low' => 'secondary',
                default => 'secondary'
            } }}">
                {{ ucfirst($ticket->priority) }}
            </span>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title">Description</h5>
                    <p class="card-text">{{ $ticket->description }}</p>

                    <hr>

                    <div class="row text-sm">
                        <div class="col-md-6">
                            <p class="mb-1"><small class="text-muted">Type</small><br>{{ ucfirst($ticket->type) }}</p>
                            <p class="mb-0"><small class="text-muted">Created By</small><br>{{ $ticket->creator->full_name ?? 'Guest' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><small class="text-muted">Assigned To</small><br>{{ $ticket->assignee->full_name ?? 'Unassigned' }}</p>
                            <p class="mb-0"><small class="text-muted">Status</small><br>{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0">
                    <h5 class="mb-0">Messages ({{ $ticket->messages()->count() }})</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($ticket->messages as $message)
                        <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $message->author->full_name ?? 'System' }}</strong>
                                <small class="text-muted">{{ $message->created_at->diffForHumans() }}</small>
                            </div>
                            @if($message->message_type === 'system')
                                <small class="text-muted fst-italic">{{ $message->message }}</small>
                            @else
                                <p class="mb-0 mt-2">{{ $message->message }}</p>
                            @endif
                            @if($message->is_internal)
                                <span class="badge bg-warning-subtle text-warning-emphasis small mt-2">Internal Note</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">No messages yet.</p>
                    @endforelse
                </div>

                @if($ticket->isOpen())
                    <div class="card-footer bg-light border-top">
                        <form action="{{ route('tickets.add-message', $ticket) }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control" name="message" placeholder="Add a reply..." required>
                                <button class="btn btn-primary" type="submit">Send</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-light border-0">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    @if($ticket->status === 'open')
                        <form action="{{ route('tickets.update-status', $ticket) }}" method="POST" class="mb-2">
                            @csrf
                            <input type="hidden" name="status" value="in_progress">
                            <button class="btn btn-sm btn-outline-primary w-100" type="submit">
                                <i class="bi bi-play-circle"></i> Start Working
                            </button>
                        </form>
                    @endif

                    @if($ticket->isOpen())
                        <form action="{{ route('tickets.resolve', $ticket) }}" method="POST" class="mb-2">
                            @csrf
                            <button class="btn btn-sm btn-outline-success w-100" type="submit">
                                <i class="bi bi-check-circle"></i> Mark Resolved
                            </button>
                        </form>

                        <form action="{{ route('tickets.close', $ticket) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger w-100" type="submit">
                                <i class="bi bi-x-circle"></i> Close
                            </button>
                        </form>
                    @elseif($ticket->status === 'resolved')
                        <form action="{{ route('tickets.close', $ticket) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary w-100" type="submit">
                                <i class="bi bi-archive"></i> Close Ticket
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if($ticket->slaEvents->count())
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0">
                        <h5 class="mb-0">SLA Status</h5>
                    </div>
                    <div class="card-body">
                        @foreach($ticket->slaEvents as $event)
                            <div class="mb-2">
                                <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $event->event_type)) }}</small>
                                <div class="progress mt-1" style="height: 20px;">
                                    @php
                                        $minutes_left = $event->getTimeRemainingMinutes();
                                        $is_breached = $event->isBreached();
                                    @endphp
                                    <div class="progress-bar {{ $is_breached ? 'bg-danger' : 'bg-success' }}" style="width: {{ $is_breached ? '100%' : max(10, min(100, (100 - ($minutes_left / 60)) * 20)) }}%">
                                        {{ $is_breached ? 'Breached' : $event->deadline_at->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
