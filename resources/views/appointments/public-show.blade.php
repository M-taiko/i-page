@extends('layouts.guest-valex',['primary'=>'#4f46e5','secondary'=>'#7c3aed','backUrl'=>route('guest.channel-detail',[$channel->organization_id,$channel->slug])])
@section('title',__('Booking status'))
@section('content')
<style>.track{max-width:820px;margin:24px auto;padding:0 16px}.track-head{border-radius:24px;padding:26px;color:#fff;background:linear-gradient(135deg,#172554,#635bff)}.track-card{background:#fff;border:1px solid #e6e8f5;border-radius:22px;padding:24px;margin-top:16px}.track-line{position:relative;padding-left:34px;padding-bottom:24px}.track-line:before{content:'';position:absolute;left:9px;top:20px;bottom:0;width:2px;background:#dddff0}.track-line:last-child:before{display:none}.track-dot{position:absolute;left:0;top:3px;width:20px;height:20px;border-radius:50%;background:#635bff;border:5px solid #ecebff}</style>
<main class="track"><section class="track-head"><small>{{ __('Reference') }} · {{ Str::upper(Str::substr($booking->reference,0,8)) }}</small><h1 class="mt-2">{{ $booking->service->name }}</h1><p class="mb-0">{{ $booking->starts_at->format('D, d M Y · h:i A') }} · {{ $booking->guests }} {{ __('guests') }}</p></section>
<section class="track-card"><div class="d-flex justify-content-between align-items-center mb-4"><h3 class="mb-0">{{ __('Activity timeline') }}</h3><span class="badge badge-primary">{{ __(str_replace('_',' ',$booking->status)) }}</span></div>
@foreach($booking->events as $event)<article class="track-line"><span class="track-dot"></span><strong>{{ __(str_replace('_',' ',$event->to_status ?: $event->event)) }}</strong><p class="mb-1">{{ $event->message }}</p><small class="text-muted">{{ $event->created_at->diffForHumans() }}</small></article>@endforeach
</section></main>
@endsection
