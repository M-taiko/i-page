@extends('layouts.valex')
@section('title',__('Service catalog'))
@section('page-header')
<x-valex.page-header :title="__('Service catalog')" :subtitle="$organization->name.' · '.__('Shared across all booking-enabled channels')" />
@endsection
@section('content')
<style>.svc-card{border:1px solid #e6e8f5;border-radius:18px;background:#fff;padding:20px;height:100%;transition:.2s}.svc-card:hover{transform:translateY(-2px);box-shadow:0 14px 35px rgba(31,41,55,.08)}.svc-icon{width:46px;height:46px;border-radius:14px;background:#eeecff;color:#635bff;display:flex;align-items:center;justify-content:center;font-size:21px}.svc-price{font-size:18px;font-weight:800;color:#172554}.svc-edit summary{cursor:pointer;color:#635bff;font-weight:700;list-style:none}.svc-edit summary::-webkit-details-marker{display:none}</style>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="row">@if($abilities['add'])<div class="col-lg-4">
<x-valex.card :title="__('Define a new service')" :subtitle="__('Create it once, then use it in booking channels and promotional posts.')">
<form method="POST" action="{{ route('tenant.services.store') }}">@csrf
@include('appointments._service-form',['prefix'=>'new','service'=>null])
<button class="btn btn-primary btn-block"><i class="fe fe-plus"></i> {{ __('Add service') }}</button></form>
</x-valex.card></div>@endif
<div class="{{ $abilities['add']?'col-lg-8':'col-12' }}"><div class="d-flex justify-content-between align-items-center mb-3"><div><h4 class="mb-1">{{ __('Tenant services') }}</h4><p class="text-muted mb-0">{{ __('Post creators can use active services, but catalog actions follow each user permission.') }}</p></div><span class="badge badge-primary">{{ $services->count() }}</span></div>
<div class="row">@forelse($services as $service)<div class="col-md-6 mb-3"><article class="svc-card">
<div class="d-flex align-items-start justify-content-between"><span class="svc-icon"><i class="fe fe-{{ ['room'=>'home','restaurant'=>'coffee','pool'=>'droplet','spa'=>'heart','gym'=>'activity','playground'=>'smile'][$service->kind] ?? 'grid' }}"></i></span><span class="badge badge-{{ $service->is_active?'success':'secondary' }}">{{ $service->is_active?__('Active'):__('Disabled') }}</span></div>
<h5 class="mt-3 mb-1">{{ $service->name }}</h5><p class="text-muted" style="min-height:42px">{{ $service->description ?: __('No description') }}</p>
<div class="d-flex justify-content-between align-items-end mb-3"><small>{{ $service->duration_minutes }} {{ __('min') }} · {{ __('capacity :count',['count'=>$service->slot_capacity]) }}</small>@if($service->price_from)<span class="svc-price"><small>{{ __('from') }}</small> {{ number_format($service->price_from,2) }} {{ $service->currency }}</span>@endif</div>
@if($abilities['edit'])<details class="svc-edit"><summary><i class="fe fe-edit-2"></i> {{ __('Edit service') }}</summary><form class="mt-3" method="POST" action="{{ route('tenant.services.update',$service) }}">@csrf @method('PUT')@include('appointments._service-form',['prefix'=>'service_'.$service->id,'service'=>$service])<button class="btn btn-primary btn-sm">{{ __('Save changes') }}</button></form></details>@endif
@if($abilities['delete'])<form class="mt-3" method="POST" action="{{ route('tenant.services.destroy',$service) }}" onsubmit="return confirm('{{ __('Delete this service?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fe fe-trash-2"></i> {{ __('Delete') }}</button></form>@endif
</article></div>@empty<div class="col-12"><div class="alert alert-info">{{ __('No services yet. Add the first service from the form.') }}</div></div>@endforelse</div></div></div>
@endsection
