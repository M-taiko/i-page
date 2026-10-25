@extends('layouts.valex')

@section('title', isset($location) ? __('Edit Location') : __('Create Location'))

@section('page-header')
    <x-valex.page-header :title="isset($location) ? __('Edit Location') : __('Create Location')" :subtitle="$organization->name">
        <x-slot:actions>
            <a href="{{ route('organizations.settings', $organization) }}#locations" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <form action="{{ isset($location) ? route('organizations.locations.update', [$organization, $location]) : route('organizations.locations.store', $organization) }}" method="POST">
            @csrf
            @if (isset($location)) @method('PUT') @endif

            <x-valex.card :title="__('Basic Information')">
                <x-valex.input name="name" :label="__('Location Name')" :value="$location->name ?? ''" required />
                <x-valex.select name="brand_id" :label="__('Brand')" :placeholder="__('-- None --')" :options="$organization->brands->pluck('name', 'id')->all()" :value="$location->brand_id ?? null" />
                <x-valex.textarea name="description" :label="__('Description')" rows="3" :value="$location->description ?? ''" />
            </x-valex.card>

            <x-valex.card :title="__('Address')">
                <x-valex.input name="address" :label="__('Street Address')" :value="$location->address ?? ''" />
                <div class="row">
                    <div class="col-md-6"><x-valex.input name="city" :label="__('City')" :value="$location->city ?? ''" /></div>
                    <div class="col-md-6"><x-valex.input name="state" :label="__('State/Region')" :value="$location->state ?? ''" /></div>
                    <div class="col-md-6"><x-valex.input name="postal_code" :label="__('Postal Code')" :value="$location->postal_code ?? ''" /></div>
                    <div class="col-md-6"><x-valex.input name="country" :label="__('Country')" :value="$location->country ?? ''" /></div>
                </div>
            </x-valex.card>

            <x-valex.card :title="__('Contact Information')">
                <x-valex.input name="phone" type="tel" :label="__('Phone Number')" :value="$location->phone ?? ''" />
                <x-valex.input name="email" type="email" :label="__('Email Address')" :value="$location->email ?? ''" />
            </x-valex.card>

            <x-valex.card :title="__('Details')">
                <div class="row">
                    <div class="col-md-6">
                        <x-valex.select name="timezone" :label="__('Timezone')" :placeholder="__('-- Select Timezone --')"
                            :options="array_combine(timezone_identifiers_list(), timezone_identifiers_list())" :value="$location->timezone ?? 'UTC'" />
                    </div>
                    <div class="col-md-6">
                        <x-valex.select name="location_type" :label="__('Location Type')" :value="$location->location_type ?? 'branch'"
                            :options="['branch' => __('Branch'), 'headquarters' => __('Headquarters'), 'warehouse' => __('Warehouse'), 'kiosk' => __('Kiosk')]" />
                    </div>
                </div>
                <x-valex.switch name="is_active" :label="__('Active')" :checked="$location->is_active ?? true" />
            </x-valex.card>

            <button type="submit" class="btn btn-primary"><i class="fe fe-check"></i> {{ isset($location) ? __('Update Location') : __('Create Location') }}</button>
            <a href="{{ route('organizations.settings', $organization) }}#locations" class="btn btn-light">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
