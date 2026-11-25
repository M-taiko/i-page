<form method="GET" class="row" style="gap:12px;margin:0 0 24px">
<div><label>{{ __('Check-in') }}</label><input class="form-control" type="date" name="check_in" value="{{ request('check_in') }}" min="{{ today()->toDateString() }}" required></div>
<div><label>{{ __('Check-out') }}</label><input class="form-control" type="date" name="check_out" value="{{ request('check_out') }}" min="{{ today()->addDay()->toDateString() }}" required></div>
<div><label>{{ __('Guests') }}</label><input class="form-control" type="number" name="guests" value="{{ request('guests',1) }}" min="1" max="100" required></div>
<div style="align-self:end"><button class="btn btn-primary">{{ __('Find available rooms') }}</button></div>
</form>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
