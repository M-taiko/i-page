@extends('layouts.valex')

@section('title', __('People'))

@section('page-header')
    <x-valex.page-header :title="__('People')" :subtitle="__('Every employee and subscriber in the channels of :name.', ['name' => $organization->name])" />
@endsection

@section('content')
{{-- Everyone across the tenant's channels (ADR-016). Organization admins/managers only. --}}
@php
    $roleLabels = ['admin' => __('Admin'), 'moderator' => __('Moderator'), 'member' => __('Member')];
@endphp
<x-valex.card>
    <form method="GET" class="d-flex flex-wrap align-items-center mb-3" style="gap: 8px;">
        <input type="search" name="q" value="{{ $search }}" class="form-control" style="max-width: 280px;" placeholder="{{ __('Search name, email or mobile') }}">
        <div class="btn-group">
            @foreach ([null => __('Everyone'), 'staff' => __('Staff'), 'visitors' => __('Visitors')] as $k => $label)
                <a href="{{ request()->fullUrlWithQuery(['kind' => $k, 'page' => null]) }}" class="btn btn-sm {{ $kind === ($k ?: null) ? 'btn-primary' : 'btn-outline-primary' }}">{{ $label }}</a>
            @endforeach
        </div>
        <button class="btn btn-sm btn-light" type="submit"><i class="fe fe-search"></i> {{ __('Search') }}</button>
        <span class="small text-muted mx-2">{{ trans_choice(':count person|:count people', $people->total()) }}</span>
    </form>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>{{ __('Name') }}</th><th>{{ __('Type') }}</th><th>{{ __('Channels') }}</th><th>{{ __('Contact') }}</th></tr>
            </thead>
            <tbody>
                @forelse ($people as $person)
                    @php $orgRole = $staffIds[$person->id] ?? null; @endphp
                    <tr>
                        <td>
                            <div class="font-weight-bold">{{ $person->full_name }}</div>
                            @if ($person->job_title)<div class="small text-muted">{{ $person->job_title }}</div>@endif
                        </td>
                        <td>
                            @if ($orgRole)
                                <x-valex.badge variant="info">{{ __('Staff') }} · {{ str_replace('_', ' ', $orgRole) }}</x-valex.badge>
                            @else
                                <x-valex.badge variant="light">{{ __('Visitor') }}</x-valex.badge>
                            @endif
                        </td>
                        <td style="white-space: normal; max-width: 360px;">
                            @foreach ($memberships[$person->id] ?? [] as $m)
                                <a href="{{ route('tenant.channels.show', $m->id) }}" class="badge badge-light border mb-1">{{ $m->name }}@if ($m->role && $m->role !== 'member') · {{ $roleLabels[$m->role] ?? $m->role }}@endif</a>
                            @endforeach
                        </td>
                        <td class="small">
                            <div>{{ $person->email }}</div>
                            @if ($person->mobile)<div class="text-muted" dir="ltr">{{ $person->mobile }}</div>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">{{ __('Nobody found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $people->links() }}</div>
</x-valex.card>
@endsection
