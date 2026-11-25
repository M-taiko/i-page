@extends('layouts.valex')
@section('title', __('Hotel group'))
@section('page-header')<x-valex.page-header :title="__('Hotel group')" :subtitle="$organization->name . ' · ' . __('Hotels and their channel communities')" illustration="workspace-organization" />@endsection
@section('content')
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="row"><div class="col-md-6"><x-valex.stat-card :title="__('Hotels in your scope')" :value="$hotels->count()" icon="home" /></div><div class="col-md-6"><x-valex.stat-card :title="__('Countries represented')" :value="$hotels->pluck('country')->unique()->count()" icon="globe" /></div></div>
<div class="row"><div class="col-xl-8"><x-valex.card :title="$organization->name" :subtitle="__('The organization contains hotel channels. Each hotel contains sub-channels, which can contain further sub-channels.')">
@forelse($hotels as $hotel) @include('hotel._property-row') @empty<p class="text-muted">{{ __('No hotels in your scope yet.') }}</p>@endforelse
</x-valex.card></div><div class="col-xl-4">
@if($manager)<x-valex.card :title="__('Add hotel channel')"><form method="POST" action="{{ route('tenant.hotel-hierarchy.store') }}">@csrf
<input type="hidden" name="kind" value="hotel">
<x-valex.input name="name" :label="__('Hotel name')" required maxlength="120" />
<x-valex.input name="country" :label="__('Country')" required maxlength="80" />
<x-valex.input name="city" :label="__('City')" required maxlength="120" />
<x-valex.select name="timezone" :label="__('Local timezone')" :options="array_combine(timezone_identifiers_list(),timezone_identifiers_list())" :value="'Africa/Cairo'" />
<x-valex.select name="currency" :label="__('Currency')" :options="['EGP'=>'EGP','AED'=>'AED','SAR'=>'SAR','USD'=>'USD','EUR'=>'EUR','GBP'=>'GBP']" />
<p class="small text-muted">{{ __('Creates a hotel channel directly under the organization. Add Guest, Staff, Front Desk and further sub-channels from Channels.') }}</p>
<button class="btn btn-primary btn-block">{{ __('Add hotel') }}</button>
</form></x-valex.card>@endif
<x-valex.card :title="__('Channel hierarchy')"><p>{{ __('Organization → Hotel channel → Sub-channel → Further sub-channels') }}</p><p class="small text-muted mb-0">{{ __('Administrators manage their channel and its descendants. Individual permissions remain specific to the assigned channels.') }}</p></x-valex.card>
</div></div>
@endsection
