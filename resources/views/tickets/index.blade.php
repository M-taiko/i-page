@extends('layouts.valex')

@section('title', __('Support Tickets'))

@section('page-header')
    <x-valex.page-header :title="__('Support Tickets')" :subtitle="__('Track and resolve complaints, feedback, and requests')">
        <x-slot:actions>
            @can('create', App\Models\Ticket::class)
                <a href="{{ route('tickets.create') }}" class="btn btn-primary"><i class="fe fe-plus"></i> {{ __('New Ticket') }}</a>
            @endcan
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $statusVariant = fn ($s) => match ($s) {
        'open' => 'primary', 'in_progress' => 'info', 'waiting' => 'warning', 'resolved' => 'success', 'reopened' => 'danger', default => 'secondary',
    };
    $priorityVariant = fn ($p) => match ($p) { 'urgent' => 'danger', 'high' => 'warning', 'medium' => 'info', default => 'secondary' };
    $filters = ['' => __('All'), 'open' => __('Open'), 'in_progress' => __('In Progress'), 'waiting' => __('Waiting'), 'resolved' => __('Resolved'), 'closed' => __('Closed')];
@endphp

<ul class="nav nav-pills mb-3 flex-nowrap overflow-auto">
    @foreach ($filters as $value => $label)
        <li class="nav-item">
            <a class="nav-link {{ (request('status') ?? '') === (string) $value ? 'active' : '' }}"
               href="{{ $value === '' ? route('tickets.index') : route('tickets.index', ['status' => $value]) }}">{{ $label }}</a>
        </li>
    @endforeach
</ul>

@if ($tickets->isEmpty())
    <x-valex.empty-state :title="__('No tickets found.')" icon="tag" />
@else
    <x-valex.card>
        <div class="table-responsive">
            <table class="table table-hover text-md-nowrap mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Ticket #') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Priority') }}</th>
                        <th>{{ __('Assigned To') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $ticket)
                        <tr>
                            <td><code>{{ $ticket->ticket_number }}</code></td>
                            <td><a href="{{ route('tickets.show', $ticket) }}" class="font-weight-bold">{{ $ticket->title }}</a></td>
                            <td><x-valex.badge :variant="$statusVariant($ticket->status)">{{ str_replace('_', ' ', $ticket->status) }}</x-valex.badge></td>
                            <td><x-valex.badge :variant="$priorityVariant($ticket->priority)">{{ $ticket->priority }}</x-valex.badge></td>
                            <td class="text-muted">{{ $ticket->assignee?->full_name ?? __('Unassigned') }}</td>
                            <td class="text-muted">{{ $ticket->opened_at->format('M d, Y') }}</td>
                            <td><a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary"><i class="fe fe-arrow-right"></i></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-valex.card>

    <div class="d-flex justify-content-center mt-4">{{ $tickets->links('pagination::bootstrap-4') }}</div>
@endif
@endsection
