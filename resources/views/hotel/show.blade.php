@extends('layouts.valex')
@section('title',__('Reservation details'))
@section('page-header')<x-valex.page-header :title="$booking->guest_name" :subtitle="$booking->reference" />@endsection
@section('content')
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<x-valex.card :title="$booking->room->name"><p>{{ $booking->guest_email }} · {{ $booking->guest_phone }}</p><p>{{ $booking->check_in->format('d M Y') }} → {{ $booking->check_out->format('d M Y') }} · {{ $booking->guests }} {{ __('guests') }}</p><p>{{ __($booking->status) }} · {{ __('Payment status') }}: {{ __($booking->payment_status) }}</p><h3>{{ number_format($booking->paid_minor/100,2) }} / {{ number_format($booking->total_minor/100,2) }} {{ $booking->currency }}</h3><p>{{ $booking->notes }}</p>
<form method="POST" action="{{ route('tenant.bookings.status',[$channel,$booking]) }}">@csrf
@if($booking->status === 'confirmed')<button class="btn btn-primary" name="status" value="checked_in">{{ __('Check in') }}</button><button class="btn btn-outline-danger" name="status" value="cancelled">{{ __('Cancel reservation') }}</button>@elseif($booking->status === 'checked_in')<button class="btn btn-primary" name="status" value="checked_out">{{ __('Check out') }}</button>@endif</form>
</x-valex.card>
<x-valex.card :title="__('Record an external payment or refund')" :subtitle="__('This records money handled outside the app. It does not charge or refund money.')">
<form method="POST" action="{{ route('tenant.bookings.payments.store',[$channel,$booking]) }}">@csrf
<input type="hidden" name="submission_key" value="{{ (string)\Illuminate\Support\Str::uuid() }}">
<div class="row"><div class="col-md-4"><x-valex.select name="type" :label="__('Record type')" :options="['payment'=>__('Payment received'),'refund'=>__('Refund paid')]" /></div><div class="col-md-4"><x-valex.input name="amount" type="number" step="0.01" min="0.01" :label="__('Amount')" required /></div><div class="col-md-4"><x-valex.select name="method" :label="__('Method')" :options="['cash'=>__('Cash'),'card'=>__('Card outside app'),'bank'=>__('Bank transfer'),'external'=>__('Other external method')]" /></div></div>
<x-valex.input name="reference" :label="__('External reference')" maxlength="120" /><button class="btn btn-primary">{{ __('Save payment record') }}</button></form>
<div class="mt-4">@forelse($booking->payments as $payment)<p>{{ $payment->created_at }} · {{ __($payment->type) }} · {{ number_format($payment->amount_minor/100,2) }} {{ $booking->currency }} · {{ $payment->method }} · {{ $payment->recorder?->full_name }}</p>@empty<p class="text-muted">{{ __('No payment records yet.') }}</p>@endforelse</div>
</x-valex.card>
@endsection
