@php
$bookingService = app(\App\Services\IndustryModuleService::class);
$bookingChannels = isset($bookingChannel) ? collect([$bookingChannel]) : $organization->channels()->where('type','public')->where('status','active')->get();
$bookingChannels = $bookingChannels->filter(fn($item)=>$item->type === 'public' && $item->status === 'active' && $bookingService->enabled($item,'bookings') && $item->appointmentServices()->where('is_active',true)->exists());
@endphp
@if($bookingChannels->isNotEmpty())
<section aria-label="{{ __('Bookings and appointments') }}" style="margin:20px 16px;padding:22px;border:1px solid #dfe3f4;border-radius:20px;background:var(--card-bg,#fff);color:var(--text-primary,#25314b)">
<h2 style="font-size:22px;margin-bottom:8px">{{ __('Book an experience') }}</h2><p>{{ __('Choose a service and request a convenient appointment.') }}</p>
<div style="display:flex;gap:12px;flex-wrap:wrap">
@foreach($bookingChannels as $item)
<a href="{{ route('guest.appointments.create',$item) }}" style="display:block;padding:14px 20px;border-radius:12px;background:linear-gradient(135deg,#5146e5,#7c3aed);color:white;text-decoration:none"><strong>{{ $item->name }}</strong><small style="display:block">{{ $item->appointmentServices()->where('is_active',true)->pluck('name')->take(3)->join(' · ') }}</small></a>
@endforeach
</div></section>
@endif
