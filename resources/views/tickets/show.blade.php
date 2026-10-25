@extends('layouts.valex')

@section('title', $ticket->title)

@section('page-header')
    <x-valex.page-header :title="$ticket->title" :subtitle="$ticket->ticket_number">
        <x-slot:actions>
            <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $statusVariant = match ($ticket->status) { 'open' => 'primary', 'in_progress' => 'info', 'waiting' => 'warning', 'resolved' => 'success', default => 'secondary' };
    $priorityVariant = match ($ticket->priority) { 'urgent' => 'danger', 'high' => 'warning', 'medium' => 'info', default => 'secondary' };
@endphp

<div class="mb-3">
    <x-valex.badge :variant="$statusVariant">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</x-valex.badge>
    <x-valex.badge :variant="$priorityVariant">{{ ucfirst($ticket->priority) }}</x-valex.badge>
    <span class="text-muted small mx-2">{{ __('Opened') }} {{ $ticket->opened_at->diffForHumans() }}</span>
</div>

<div class="row">
    <div class="col-lg-8">
        <x-valex.card :title="__('Description')">
            <p>{{ $ticket->description }}</p>
            <hr>
            <div class="row small">
                <div class="col-md-6 mb-2"><span class="text-muted d-block">{{ __('Type') }}</span>{{ ucfirst($ticket->type) }}</div>
                <div class="col-md-6 mb-2"><span class="text-muted d-block">{{ __('Assigned To') }}</span>{{ $ticket->assignee->full_name ?? __('Unassigned') }}</div>
                <div class="col-md-6"><span class="text-muted d-block">{{ __('Created By') }}</span>{{ $ticket->creator->full_name ?? __('Guest') }}</div>
                <div class="col-md-6"><span class="text-muted d-block">{{ __('Status') }}</span>{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</div>
            </div>
        </x-valex.card>

        <div class="card">
            <div class="card-header pb-0"><h4 class="card-title mb-0">{{ __('Messages') }} ({{ $ticket->messages()->count() }})</h4></div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                @forelse ($ticket->messages as $message)
                    <div class="pb-3 mb-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $message->author->full_name ?? __('System') }}</strong>
                            <small class="text-muted">{{ $message->created_at->diffForHumans() }}</small>
                        </div>
                        @if ($message->message_type === 'system')
                            <small class="text-muted font-italic">{{ $message->message }}</small>
                        @else
                            <p class="mb-0 mt-2">{{ $message->message }}</p>
                        @endif
                        @if ($message->is_internal)
                            <x-valex.badge variant="warning" class="mt-2">{{ __('Internal Note') }}</x-valex.badge>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('No messages yet.') }}</p>
                @endforelse
            </div>

            @if ($ticket->isOpen())
                <div class="card-footer">
                    <form action="{{ route('tickets.add-message', $ticket) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <input type="text" class="form-control" name="message" placeholder="{{ __('Add a reply...') }}" required>
                            <div class="input-group-append"><button class="btn btn-primary" type="submit">{{ __('Send') }}</button></div>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <x-valex.card :title="__('Actions')">
            @if ($ticket->status === 'open')
                <form action="{{ route('tickets.update-status', $ticket) }}" method="POST" class="mb-2">
                    @csrf
                    <input type="hidden" name="status" value="in_progress">
                    <button class="btn btn-outline-primary btn-block" type="submit"><i class="fe fe-play-circle"></i> {{ __('Start Working') }}</button>
                </form>
            @endif

            @if ($ticket->isOpen())
                <form action="{{ route('tickets.resolve', $ticket) }}" method="POST" class="mb-2">
                    @csrf
                    <button class="btn btn-outline-success btn-block" type="submit"><i class="fe fe-check-circle"></i> {{ __('Mark Resolved') }}</button>
                </form>
                <form action="{{ route('tickets.close', $ticket) }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-danger btn-block" type="submit"><i class="fe fe-x-circle"></i> {{ __('Close') }}</button>
                </form>
            @elseif ($ticket->status === 'resolved')
                <form action="{{ route('tickets.close', $ticket) }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-secondary btn-block" type="submit"><i class="fe fe-archive"></i> {{ __('Close Ticket') }}</button>
                </form>
            @else
                <p class="text-muted text-center mb-0">{{ __('No actions available') }}</p>
            @endif
        </x-valex.card>

        @if ($ticket->slaEvents->count())
            <x-valex.card :title="__('SLA Status')">
                @foreach ($ticket->slaEvents as $event)
                    @php
                        $minutesLeft = $event->getTimeRemainingMinutes();
                        $isBreached = $event->isBreached();
                    @endphp
                    <div class="mb-3">
                        <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $event->event_type)) }}</small>
                        <div class="progress mt-1" style="height: 20px;">
                            <div class="progress-bar {{ $isBreached ? 'bg-danger' : 'bg-success' }}"
                                 style="width: {{ $isBreached ? '100%' : max(10, min(100, (100 - ($minutesLeft / 60)) * 20)) }}%">
                                {{ $isBreached ? __('Breached') : $event->deadline_at->format('H:i') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </x-valex.card>
        @endif
    </div>
</div>
@endsection
