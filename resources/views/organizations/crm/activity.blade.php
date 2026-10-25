@extends('layouts.valex')

@section('title', __('Activity'))

@section('page-header')
    <x-valex.page-header :title="$organization->name" :subtitle="__('CRM · Activity')">
        <x-slot:actions>
            <a href="{{ route('organizations.crm.contacts') }}" class="btn btn-outline-secondary"><i class="fe fe-users"></i> {{ __('Contacts') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<x-valex.card>
    <form method="GET" class="form-row mb-3">
        <div class="col-md-3 mb-2">
            <select name="event" class="form-control">
                <option value="">{{ __('All events') }}</option>
                @foreach ($events as $e)<option value="{{ $e }}" @selected(request('event') === $e)>{{ ucfirst($e) }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3 mb-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control" title="{{ __('From') }}"></div>
        <div class="col-md-3 mb-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control" title="{{ __('To') }}"></div>
        <div class="col-md-3 mb-2"><button class="btn btn-primary btn-block"><i class="fe fe-search"></i> {{ __('Filter') }}</button></div>
    </form>

    @include('crm._activity-table', ['logs' => $logs])
    <div class="d-flex justify-content-center mt-3">{{ $logs->links('pagination::bootstrap-4') }}</div>
</x-valex.card>
@endsection
