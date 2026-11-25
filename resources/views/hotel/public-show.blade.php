@extends('layouts.guest-valex',['primary'=>'#4f46e5','secondary'=>'#7c3aed','backUrl'=>route('guest.organization-detail',$channel->organization_id)])
@section('title',__('Your reservation'))
@section('content')<div class="card p-4" style="max-width:700px;margin:24px auto;border-radius:20px">
<h1>{{ __('Your reservation') }}</h1><p class="badge badge-primary">{{ __($booking->status) }}</p>
<p>{{ __('Reference') }}: <strong>{{ $booking->reference }}</strong></p>
<h3>{{ $booking->room->name }}</h3><p>{{ $booking->guest_name }}</p><p>{{ $booking->check_in->format('d M Y') }} → {{ $booking->check_out->format('d M Y') }} · {{ $booking->guests }} {{ __('guests') }}</p>
<h3>{{ number_format($booking->total_minor/100,2) }} {{ $booking->currency }}</h3>
<p>{{ __('Payment status') }}: {{ __($booking->payment_status) }}</p><p class="text-muted">{{ __('No online payment was collected. Contact the hotel for payment arrangements or changes to your reservation.') }}</p>
<a class="btn btn-primary" href="{{ route('guest.organization-detail',$channel->organization_id) }}">{{ __('Back to hotel page') }}</a>
</div>@endsection
