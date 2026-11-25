@extends('layouts.valex')
@section('title',__('New reservation'))
@section('page-header')<x-valex.page-header :title="__('New reservation')" :subtitle="$channel->name" />@endsection
@section('content')<x-valex.card :title="__('Availability and guest details')">
@include('hotel._search')
@include('hotel._reservation-form',['reservationAction'=>route('tenant.bookings.store',$channel)])
</x-valex.card>@endsection
