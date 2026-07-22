@extends('layouts.mobile-shell')

@section('title', $channel->name . ' - i-Page')

@section('app-bar')
    <a href="{{ route('dashboard.channels.index', $organization) }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-bar-title" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $channel->name }}</div>
@endsection

@section('extra-styles')
    .join-request-btn {
        border: none;
        border-radius: var(--radius-full);
        padding: var(--space-3) var(--space-8);
        font-weight: var(--font-weight-semibold);
        font-size: var(--text-sm);
        cursor: pointer;
        background-color: var(--primary-600);
        color: white;
    }
@endsection

@section('content')
    @if($membership && $membership->pivot->status === 'pending')
        <x-empty-state-modern
            title="{{ __('Request Pending Approval') }}"
            message="{{ __('Your request to join :name is awaiting approval from an organization admin.', ['name' => $channel->name]) }}"
            icon="hourglass-split"
        />
    @else
        <x-empty-state-modern
            title="{{ __('Private Channel') }}"
            message="{{ __(':name is a private channel. Request to join and an organization admin will review it.', ['name' => $channel->name]) }}"
            icon="lock"
        >
            <x-slot:action>
                <form action="{{ route('dashboard.channels.subscribe', [$organization->id, $channel->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="join-request-btn">
                        <i class="bi bi-person-plus"></i> {{ __('Request to Join') }}
                    </button>
                </form>
            </x-slot:action>
        </x-empty-state-modern>
    @endif
@endsection
