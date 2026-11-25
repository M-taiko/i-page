@if(request('check_in') && request('check_out'))
@if($rooms->isEmpty())<p class="alert alert-info">{{ __('No rooms are available for these dates and guest count. Try other dates.') }}</p>@else
<form method="POST" action="{{ $reservationAction }}">@csrf
<input type="hidden" name="submission_key" value="{{ old('submission_key',(string)\Illuminate\Support\Str::uuid()) }}">
@foreach(['check_in','check_out','guests'] as $field)<input type="hidden" name="{{ $field }}" value="{{ request($field,$field === 'guests' ? 1 : '') }}">@endforeach
<div class="row">
@foreach($rooms as $room)<div class="col-md-6 mb-3"><label class="card p-3 h-100" style="cursor:pointer;display:block;border:1px solid #dfe5ef;border-radius:16px">
<input type="radio" name="room_id" value="{{ $room->id }}" @checked((int)old('room_id') === $room->id) required> <strong>{{ $room->name }}</strong>
<p class="text-muted mt-2">{{ $room->description }}</p><p>{{ __('Up to :count guests',['count'=>$room->capacity]) }}</p>
<strong style="font-size:22px">{{ number_format(($quotes[$room->id] ?? 0)/100,2) }} {{ $room->currency }}</strong><small class="d-block">{{ __('Total for the selected stay') }}</small>
</label></div>@endforeach
</div>
<x-valex.input name="guest_name" :label="__('Guest name')" :value="auth()->user()->full_name" required maxlength="160" />
@if(empty($publicBooking))<x-valex.input name="guest_email" type="email" :label="__('Guest email')" />@else<p class="text-muted">{{ __('Booking email') }}: {{ auth()->user()->email }}</p>@endif
<x-valex.input name="guest_phone" :label="__('Phone number')" :value="auth()->user()->mobile" required maxlength="40" />
<label for="booking-notes">{{ __('Special requests') }}</label><textarea class="form-control mb-3" name="notes" id="booking-notes" maxlength="2000">{{ old('notes') }}</textarea>
<label class="d-block mb-3"><input type="checkbox" name="marketing_consent" value="1" @checked(old('marketing_consent'))> {{ __('I agree to receive hotel offers and promotional messages.') }}</label>
<input type="hidden" name="consent_source" value="Reservation form">
<p class="text-muted">{{ __('No online payment is taken. Payment arrangements are handled directly with the hotel.') }}</p>
<button class="btn btn-primary btn-lg">{{ __('Confirm reservation') }}</button>
</form>
@endif
@endif
