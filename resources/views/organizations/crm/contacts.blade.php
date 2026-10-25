@extends('layouts.valex')

@section('title', __('Contacts'))

@section('page-header')
    <x-valex.page-header :title="$organization->name" :subtitle="__('CRM · Contacts')">
        <x-slot:actions>
            <a href="{{ route('organizations.crm.activity') }}" class="btn btn-outline-primary mx-1"><i class="fe fe-activity"></i> {{ __('Activity') }}</a>
            <a href="{{ route('dashboard.users.create', $organization) }}" class="btn btn-primary mx-1"><i class="fe fe-user-plus"></i> {{ __('Add Member') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $relVariant = ['member' => 'primary', 'follower' => 'info', 'subscriber' => 'purple'];
    $relLabel = ['member' => __('Member'), 'follower' => __('Follower'), 'subscriber' => __('Subscriber')];
@endphp

<div class="row row-sm">
    <div class="col-sm-6 col-xl-2"><x-valex.stat-card :title="__('All contacts')" :value="$summary['total']" icon="users" variant="primary" /></div>
    <div class="col-sm-6 col-xl-2"><x-valex.stat-card :title="__('Active members')" :value="$summary['activeMembers']" icon="user-check" variant="success" /></div>
    <div class="col-sm-6 col-xl-2"><x-valex.stat-card :title="__('Pending / invited')" :value="$summary['pendingMembers']" icon="clock" variant="warning" /></div>
    <div class="col-sm-6 col-xl-2"><x-valex.stat-card :title="__('Followers')" :value="$summary['followers']" icon="heart" variant="info" /></div>
    <div class="col-sm-6 col-xl-2"><x-valex.stat-card :title="__('Channel subscribers')" :value="$summary['subscribers']" icon="message-circle" variant="purple" /></div>
    <div class="col-sm-6 col-xl-2"><x-valex.stat-card :title="__('Total members')" :value="$summary['members']" icon="briefcase" variant="danger" /></div>
</div>

<x-valex.card>
    <form method="GET" class="form-row mb-3">
        <div class="col-md-6 mb-2"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Search name, email, username...') }}"></div>
        <div class="col-md-4 mb-2">
            <select name="relationship" class="form-control">
                <option value="">{{ __('All relationships') }}</option>
                @foreach ($relLabel as $v => $l)<option value="{{ $v }}" @selected(request('relationship') === $v)>{{ $l }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block"><i class="fe fe-search"></i> {{ __('Filter') }}</button></div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover text-md-nowrap mb-0">
            <thead><tr><th>{{ __('Contact') }}</th><th>{{ __('Relationship') }}</th><th>{{ __('Role') }}</th><th class="text-center">{{ __('Channels') }}</th><th>{{ __('Last seen') }}</th><th>{{ __('Joined platform') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($contacts as $c)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md brround bg-primary mx-2">{{ $c->initials }}</span>
                                <div><a href="{{ route('organizations.crm.contact', $c) }}" class="font-weight-bold">{{ $c->full_name }}</a><div class="small text-muted">{{ $c->email }}</div></div>
                            </div>
                        </td>
                        <td>@foreach ($c->relationships as $r)<x-valex.badge :variant="$relVariant[$r]">{{ $relLabel[$r] }}</x-valex.badge> @endforeach</td>
                        <td>
                            @if ($c->membership)
                                {{ ucfirst(str_replace('_', ' ', $c->membership->role)) }}
                                <x-valex.badge :variant="$c->membership->status === 'active' ? 'success' : 'warning'">{{ $c->membership->status }}</x-valex.badge>
                            @else — @endif
                        </td>
                        <td class="text-center">{{ $c->org_channels_count }}</td>
                        <td class="text-muted">{{ $c->last_seen_at?->diffForHumans() ?? __('Never') }}</td>
                        <td class="text-muted">{{ $c->created_at->format('M d, Y') }}</td>
                        <td><a href="{{ route('organizations.crm.contact', $c) }}" class="btn btn-sm btn-outline-primary">{{ __('360') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">{{ __('No contacts match your filters') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center mt-3">{{ $contacts->links('pagination::bootstrap-4') }}</div>
</x-valex.card>
@endsection
