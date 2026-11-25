@extends('layouts.valex')
@section('title',__('Rooms, rates & availability'))
@section('page-header')<x-valex.page-header :title="__('Rooms, rates & availability')" :subtitle="app(\App\Services\HotelPropertyService::class)->require($channel)->name" />@endsection
@section('content')
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<x-valex.card :title="__('Add room')">@include('hotel._room-form',['room'=>null])</x-valex.card>
@foreach($rooms as $room)<x-valex.card :title="$room->number . ' · ' . $room->name">
<details><summary>{{ __('Edit room details') }}</summary>@include('hotel._room-form')</details>
<form method="POST" action="{{ route('tenant.bookings.rooms.calendar',[$channel,$room]) }}" class="row mt-3">@csrf
<div class="col-md-3"><label>{{ __('From (inclusive)') }}</label><input type="date" name="from" class="form-control" min="{{ today()->toDateString() }}" required></div>
<div class="col-md-3"><label>{{ __('Until (exclusive)') }}</label><input type="date" name="until" class="form-control" min="{{ today()->addDay()->toDateString() }}" required></div>
<div class="col-md-3"><label>{{ __('Action') }}</label><select name="action" class="form-control"><option value="rate">{{ __('Set nightly rate') }}</option><option value="reset_rate">{{ __('Reset rate') }}</option><option value="block">{{ __('Block availability') }}</option><option value="unblock">{{ __('Unblock availability') }}</option></select></div>
<div class="col-md-3"><label>{{ __('Nightly rate') }}</label><input type="number" name="rate" step="0.01" min="0" class="form-control"></div>
<div class="col-md-9 mt-3"><input name="reason" class="form-control" placeholder="{{ __('Reason for blocking dates') }}" maxlength="255"></div><div class="col-md-3 mt-3"><button class="btn btn-primary">{{ __('Apply') }}</button></div>
</form>
<div class="mt-3"><strong>{{ __('Upcoming price overrides') }}</strong>@forelse($room->rates as $rate)<span class="badge badge-light m-1">{{ $rate->date->format('d M Y') }} · {{ number_format($rate->rate_minor/100,2) }} {{ $room->currency }}</span>@empty<p class="text-muted">{{ __('Base rate applies.') }}</p>@endforelse</div>
<div class="mt-3"><strong>{{ __('Blocked dates') }}</strong>@forelse($room->nights as $night)<span class="badge badge-warning m-1">{{ $night->date->format('d M Y') }} · {{ $night->reason }}</span>@empty<p class="text-muted">{{ __('No maintenance blocks.') }}</p>@endforelse</div>
</x-valex.card>@endforeach
{{ $rooms->links('pagination::bootstrap-4') }}
@endsection
