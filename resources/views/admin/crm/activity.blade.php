@extends('layouts.valex')

@section('title', __('Activity Log'))

@section('page-header')
    <x-valex.page-header :title="__('Platform CRM')" :subtitle="__('Activity Log')">
        <x-slot:actions>
            <a href="{{ route('admin.crm.index') }}" class="btn btn-outline-secondary mx-1"><i class="fe fe-briefcase"></i> {{ __('Organizations 360') }}</a>
            <a href="{{ route('admin.crm.people') }}" class="btn btn-outline-primary mx-1"><i class="fe fe-users"></i> {{ __('People') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<x-valex.card>
    <form method="GET" class="form-row mb-3">
        <div class="col-md-3 mb-2">
            <select name="organization" class="form-control">
                <option value="">{{ __('All organizations') }}</option>
                @foreach ($organizations as $o)<option value="{{ $o->id }}" @selected((string) request('organization') === (string) $o->id)>{{ $o->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <select name="event" class="form-control">
                <option value="">{{ __('All events') }}</option>
                @foreach ($events as $e)<option value="{{ $e }}" @selected(request('event') === $e)>{{ ucfirst($e) }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2 mb-2"><input type="number" name="user" value="{{ request('user') }}" class="form-control" placeholder="{{ __('User ID') }}"></div>
        <div class="col-md-2 mb-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control" title="{{ __('From') }}"></div>
        <div class="col-md-2 mb-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control" title="{{ __('To') }}"></div>
        <div class="col-md-1 mb-2"><button class="btn btn-primary btn-block"><i class="fe fe-search"></i></button></div>
    </form>

    @include('crm._activity-table', ['logs' => $logs, 'showOrganization' => true])
    <div class="d-flex justify-content-center mt-3">{{ $logs->links('pagination::bootstrap-4') }}</div>
</x-valex.card>
@endsection
