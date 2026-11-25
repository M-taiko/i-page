@php $moduleService = app(\App\Services\IndustryModuleService::class); $industryModules = $moduleService->available($organization); @endphp
@if($channel->canAdminister(auth()->user()) && count($industryModules))
<x-valex.card :title="__('Channel modules')" :subtitle="__('Available modules follow the organization template. Enable them separately for each channel.')">
<form method="POST" action="{{ route('tenant.channels.modules.update', $channel) }}">@csrf @method('PUT')
@php $hotelChoices = app(\App\Services\HotelPropertyService::class)->options(auth()->user(),$organization); $hotel = app(\App\Services\HotelPropertyService::class)->forChannel($channel); if($hotel) $hotelChoices[$hotel->id]=$hotel->name; @endphp
@if(isset($industryModules['bookings']))<x-valex.select name="booking_property_id" :label="__('Hotel inventory')" :options="$hotelChoices" :value="$hotel?->id" :placeholder="__('Select hotel')" />@endif
<div class="row">
@foreach($industryModules as $key => $module)
<div class="col-md-6 mb-3"><label class="ip-module-card"><span class="ip-task-icon"><i class="fe fe-{{ $module['icon'] }}"></i></span><span><strong>{{ __($module['name']) }}</strong><small>{{ __($module['description']) }}</small>@unless($module['ready'])<span class="badge badge-light">{{ __('Planned · not available yet') }}</span>@endunless</span>
<input type="checkbox" name="modules[]" value="{{ $key }}" @checked($moduleService->enabled($channel, $key)) @disabled(!$module['ready']) aria-label="{{ __($module['name']) }}"></label></div>
@endforeach
</div><button class="btn btn-primary">{{ __('Save modules') }}</button>
@if($moduleService->enabled($channel, 'bookings'))<a class="btn btn-outline-primary" href="{{ route('tenant.appointments.index', $channel) }}">{{ __('Open bookings & appointments') }}</a>@endif
<p class="small text-muted mt-3 mb-0">{{ __('Disabling a module hides its workspace; it does not delete existing records.') }}</p>
</form></x-valex.card>
@endif
