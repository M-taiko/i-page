@extends('layouts.guest-valex',['primary'=>'#4f46e5','secondary'=>'#7c3aed','backUrl'=>route('guest.channel-detail',[$channel->organization_id,$channel->slug])])
@section('title',__('Book an appointment').' · '.$channel->name)
@section('content')
<style>
:root{--ap-primary:#5b4bdf;--ap-deep:#172554;--ap-ink:#172033;--ap-muted:#707891;--ap-line:#e7e9f3;--ap-soft:#f5f6ff}
.ap-wrap{max-width:1040px;margin:24px auto 110px;padding:0 16px;color:var(--ap-ink)}
.ap-hero{position:relative;overflow:hidden;padding:34px 36px 64px;border-radius:30px;color:#fff;background:linear-gradient(135deg,#101d46 0%,#5145dc 58%,#8b3cf3 100%);box-shadow:0 24px 60px rgba(54,45,175,.25)}
.ap-hero:before,.ap-hero:after{content:"";position:absolute;border-radius:50%;background:rgba(255,255,255,.09)}.ap-hero:before{width:260px;height:260px;right:-75px;top:-120px}.ap-hero:after{width:120px;height:120px;right:150px;bottom:-70px}
.ap-eyebrow{display:flex;align-items:center;gap:8px;font-size:.78rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;opacity:.82}.ap-hero h1{font-size:clamp(1.65rem,5vw,2.5rem);font-weight:900;letter-spacing:-.04em}.ap-hero p{max-width:620px;opacity:.86}
.ap-form{position:relative;margin:-34px 18px 0;border:1px solid rgba(225,227,241,.9);border-radius:28px;background:rgba(255,255,255,.98);padding:30px;box-shadow:0 18px 50px rgba(30,38,74,.11)}
.ap-section-title{display:flex;align-items:center;gap:12px;margin-bottom:16px}.ap-step{display:grid;place-items:center;width:34px;height:34px;flex:0 0 34px;border-radius:11px;background:var(--ap-soft);color:var(--ap-primary);font-weight:900}.ap-section-title h2{font-size:1.05rem;font-weight:900;margin:0}.ap-section-title p{font-size:.83rem;color:var(--ap-muted);margin:2px 0 0}
.ap-focus-note{display:flex;align-items:center;gap:9px;width:max-content;max-width:100%;padding:8px 12px;margin-bottom:14px;border-radius:999px;background:#ecfdf3;color:#147a49;font-size:.78rem;font-weight:800}
.service-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px}.service-grid.is-focused{grid-template-columns:1fr}
.service-option{position:relative;margin:0}.service-option input{position:absolute;opacity:0;pointer-events:none}.service-card{position:relative;display:flex;gap:16px;padding:19px;border:2px solid var(--ap-line);border-radius:20px;cursor:pointer;height:100%;transition:.22s ease;background:#fff}.service-option input:checked+.service-card{border-color:#7167ea;background:linear-gradient(135deg,#f8f7ff,#f2efff);box-shadow:0 8px 24px rgba(91,75,223,.12)}.service-icon{display:grid;place-items:center;width:48px;height:48px;flex:0 0 48px;border-radius:16px;color:#fff;background:linear-gradient(135deg,#6757e8,#9346ee);font-size:1.2rem;box-shadow:0 8px 18px rgba(91,75,223,.25)}.service-copy{min-width:0}.service-name{display:block;font-size:1rem;font-weight:900}.service-price{display:inline-block;color:var(--ap-primary);font-size:.82rem;font-weight:900;margin-top:3px}.service-meta{display:block;color:var(--ap-muted);font-size:.76rem;margin-top:5px}.service-description{display:block;color:#515a70;font-size:.82rem;line-height:1.55;margin-top:7px}.service-check{position:absolute;top:14px;right:14px;color:var(--ap-primary);opacity:0}.service-option input:checked+.service-card .service-check{opacity:1}
.ap-divider{height:1px;background:var(--ap-line);margin:28px 0}.ap-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}.ap-field-wide{grid-column:1/-1}.ap-label{display:block;margin-bottom:7px;font-size:.8rem;font-weight:850;color:#3b4358}.ap-form .form-control{min-height:49px;border:1px solid #dfe2ee;border-radius:14px;padding:11px 14px;background:#fbfcff;box-shadow:none}.ap-form textarea.form-control{min-height:96px}.ap-form .form-control:focus{border-color:#766ce8;box-shadow:0 0 0 4px rgba(91,75,223,.09);background:#fff}
.ap-consent{display:flex;align-items:flex-start;gap:10px;margin-top:18px;padding:14px;border-radius:14px;background:#fafaff;color:#5a6175;font-size:.83rem}.ap-submit{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;min-height:54px;margin-top:20px;border:0;border-radius:16px!important;font-weight:900!important;background:linear-gradient(135deg,#5848dd,#843fe9)!important;box-shadow:0 12px 24px rgba(91,75,223,.25);transition:.2s ease}.ap-submit:hover{transform:translateY(-2px);box-shadow:0 16px 30px rgba(91,75,223,.32)}
@media(max-width:650px){.ap-wrap{padding:0 10px;margin-top:12px}.ap-hero{padding:25px 21px 56px;border-radius:24px}.ap-form{margin:-30px 8px 0;padding:20px 16px;border-radius:23px}.ap-fields{grid-template-columns:1fr}.ap-field-wide{grid-column:auto}.service-grid{grid-template-columns:1fr}.service-card{padding:16px}.ap-hero h1{line-height:1.15}}
</style>
<main class="ap-wrap">
 <section class="ap-hero">
  <div class="ap-eyebrow"><i class="bi bi-stars"></i> {{ $channel->organization->name }} · {{ $channel->name }}</div>
  <h1 class="mt-3 mb-2">{{ $focusedService ? __('Complete your booking') : __('Book your next experience') }}</h1>
  <p class="mb-0">{{ $focusedService ? __('You chose this experience from an offer. Pick a suitable appointment and send your request.') : __('Explore the available services, choose what suits you, and we will keep you updated at every step.') }}</p>
 </section>
 <form class="ap-form" method="POST" action="{{ route('guest.appointments.store',$channel) }}">@csrf
  <input type="hidden" name="submission_key" value="{{ old('submission_key',(string) Str::uuid()) }}">
  @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
  <div class="ap-section-title"><span class="ap-step">1</span><div><h2>{{ $focusedService ? __('Your selected service') : __('Choose a service') }}</h2><p>{{ $focusedService ? __('This is the service attached to the offer you opened.') : __('All services currently available at this property.') }}</p></div></div>
  @if($focusedService)<div class="ap-focus-note"><i class="bi bi-check-circle-fill"></i> {{ __('Offer service selected automatically') }}</div>@endif
  <div class="service-grid {{ $focusedService ? 'is-focused' : '' }}">
   @forelse($services as $service)<label class="service-option"><input type="radio" name="service_id" value="{{ $service->id }}" @checked($focusedService || old('service_id')==$service->id) required><span class="service-card"><span class="service-icon"><i class="bi bi-calendar2-heart"></i></span><span class="service-copy"><strong class="service-name">{{ $service->name }}</strong>@if($service->price_from)<b class="service-price">{{ __('from') }} {{ number_format($service->price_from,2) }} {{ $service->currency }}</b>@endif<small class="service-meta">{{ $service->duration_minutes }} {{ __('min') }} · {{ __('up to :count guests',['count'=>$service->slot_capacity]) }}</small>@if($service->description)<small class="service-description">{{ $service->description }}</small>@endif</span><i class="bi bi-check-circle-fill service-check"></i></span></label>
   @empty<div class="alert alert-info">{{ __('No services are available yet.') }}</div>@endforelse
  </div>
  <div class="ap-divider"></div>
  <div class="ap-section-title"><span class="ap-step">2</span><div><h2>{{ __('Appointment details') }}</h2><p>{{ __('Tell us when you would like to visit and how we can contact you.') }}</p></div></div>
  <div class="ap-fields">
   <div><label class="ap-label">{{ __('Date') }}</label><input class="form-control" type="date" name="date" min="{{ now()->toDateString() }}" value="{{ old('date') }}" required></div>
   <div><label class="ap-label">{{ __('Time') }}</label><input class="form-control" type="time" name="time" value="{{ old('time') }}" required></div>
   <div><label class="ap-label">{{ __('Guests') }}</label><input class="form-control" type="number" name="guests" min="1" max="100" value="{{ old('guests',1) }}" required></div>
   <div><label class="ap-label">{{ __('Name') }}</label><input class="form-control" name="customer_name" value="{{ old('customer_name',auth()->user()->full_name) }}" required></div>
   <div><label class="ap-label">{{ __('Phone') }}</label><input class="form-control" name="customer_phone" value="{{ old('customer_phone',auth()->user()->mobile) }}"></div>
   <div class="ap-field-wide"><label class="ap-label">{{ __('Notes or special request') }}</label><textarea class="form-control" name="customer_notes" rows="3" placeholder="{{ __('Anything that will help us prepare for your visit?') }}">{{ old('customer_notes') }}</textarea></div>
  </div>
  <label class="ap-consent"><input type="checkbox" name="marketing_consent" value="1" @checked(old('marketing_consent'))><span>{{ __('I agree to receive relevant offers and reminders from this hotel.') }}</span></label>
  <button class="btn btn-primary btn-lg ap-submit" @disabled($services->isEmpty())><i class="bi bi-send-fill"></i> {{ __('Send booking request') }}</button>
 </form>
</main>
@endsection
