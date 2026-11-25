@extends('layouts.valex')
@section('title',__('Reservations'))
@section('page-header')<x-valex.page-header :title="__('Reservations')" :subtitle="$channel->name"><x-slot:actions><a class="btn btn-primary" href="{{ route('tenant.bookings.create',$channel) }}">{{ __('New reservation') }}</a>
@if(app(\App\Services\HotelPropertyService::class)->require($channel)->channel->canAdminister(auth()->user()))<a class="btn btn-outline-primary" href="{{ route('tenant.bookings.rooms',$channel) }}">{{ __('Rooms, rates & availability') }}</a>@endif
</x-slot:actions></x-valex.page-header>@endsection
@section('content')
<div class="row">@foreach($summary as $label=>$value)<div class="col-md-4"><x-valex.stat-card :title="__(str_replace('_',' ',$label))" :value="$value" icon="calendar" /></div>@endforeach</div>
<x-valex.card :title="__('Guest reservations')"><form method="GET" class="d-flex mb-3" style="gap:12px"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('Search guest or reference') }}"><select name="status" class="form-control"><option value="">{{ __('All statuses') }}</option>@foreach(['confirmed','checked_in','checked_out','cancelled'] as $status)<option @selected(request('status')===$status)>{{ $status }}</option>@endforeach</select><button class="btn btn-primary">{{ __('Filter') }}</button></form>
<div class="table-responsive"><table class="table"><thead><tr><th>{{ __('Guest') }}</th><th>{{ __('Room') }}</th><th>{{ __('Stay') }}</th><th>{{ __('Status') }}</th><th>{{ __('Payment') }}</th></tr></thead><tbody>
@forelse($bookings as $booking)<tr><td><a href="{{ route('tenant.bookings.show',[$channel,$booking]) }}">{{ $booking->guest_name }}</a></td><td>{{ $booking->room->number }} · {{ $booking->room->name }}</td><td>{{ $booking->check_in->format('d M') }} → {{ $booking->check_out->format('d M Y') }}</td><td>{{ __($booking->status) }}</td><td>{{ __($booking->payment_status) }} · {{ number_format($booking->total_minor/100,2) }} {{ $booking->currency }}</td></tr>@empty<tr><td colspan="5">{{ __('No reservations yet.') }}</td></tr>@endforelse
</tbody></table></div>{{ $bookings->links('pagination::bootstrap-4') }}</x-valex.card>
@endsection
