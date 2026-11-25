@php
$bookingOrg = auth()->user()->currentOrganization;
$propertyService = app(\App\Services\HotelPropertyService::class);
$bookingOptions = $propertyService->options(auth()->user(),$bookingOrg);
$linkedProperty = isset($channel) ? $propertyService->forChannel($channel) : null;
if($linkedProperty) $bookingOptions[$linkedProperty->id]=$linkedProperty->name;
@endphp
@if($bookingOrg->template?->key === 'hotel')
<x-valex.card :title="__('Bookings & appointments')" :subtitle="__('Let customers request appointments for rooms, dining, spa, pool, gym and other services.')">
<input type="hidden" name="booking_enabled" value="0">
<label class="d-flex align-items-center" style="gap:12px"><input type="checkbox" name="booking_enabled" value="1" @checked(old('booking_enabled',isset($channel) && app(\App\Services\IndustryModuleService::class)->enabled($channel,'bookings')))> <strong>{{ __('Enable bookings and appointments in this channel') }}</strong></label>
<x-valex.select name="booking_property_id" :label="__('Hotel inventory')" :options="$bookingOptions" :value="$linkedProperty?->id" :placeholder="__('Select hotel')" />
<p class="small text-muted">{{ __('Public channels show an appointment button to customers. The hotel link sets timezone and property context; no money is collected here.') }}</p>
@error('booking_enabled')<p class="text-danger">{{ $message }}</p>@enderror
</x-valex.card>
@endif
