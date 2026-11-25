@extends('layouts.guest-valex',['primary'=>'#4f46e5','secondary'=>'#7c3aed','backUrl'=>route('guest.organization-detail',$channel->organization_id)])
@section('title',__('Book a room').' · '.$hotel->name)
@section('content')
<div class="card p-4" style="max-width:900px;margin:24px auto;border-radius:20px">
<span class="text-primary">{{ $channel->organization->name }}</span><h1>{{ __('Book your stay') }}</h1><h4>{{ $hotel->name }}</h4><p class="text-muted">{{ $hotel->city }} · {{ $hotel->country }}</p>
@include('hotel._search')
@include('hotel._reservation-form',['reservationAction'=>route('guest.bookings.store',$channel),'publicBooking'=>true])
</div>
@endsection
