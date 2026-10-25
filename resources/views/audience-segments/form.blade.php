@extends('layouts.valex')

@section('title', isset($segment) ? __('Edit Audience Segment') : __('Create Audience Segment'))

@section('page-header')
    <x-valex.page-header :title="isset($segment) ? __('Edit Audience Segment') : __('Create Audience Segment')"
        :subtitle="__('Define who should receive posts and messages targeting this segment')">
        <x-slot:actions>
            <a href="{{ route('audience-segments.index') }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    // Pre-fill fallback: read the segment's saved rule values (by type) when
    // there's no `old()` input, so editing an existing segment shows its
    // previously chosen targeting values instead of blank selects.
    $savedRules = [];
    if (isset($segment)) {
        $decoded = is_array($segment->rules) ? $segment->rules : (json_decode($segment->rules, true) ?? []);
        foreach ($decoded as $rule) {
            if (! empty($rule['type'])) {
                $savedRules[$rule['type']] = $rule['value'];
            }
        }
    }
    $ruleValue = fn ($index, $type) => old("rules.$index.value", $savedRules[$type] ?? '');
    $org = auth()->user()->currentOrganization;

    // [index, type, label, "all" placeholder, options]
    $ruleFields = [
        [0, 'language', __('Language'), __('— All Languages —'), ['en' => __('English'), 'ar' => __('Arabic')]],
        [1, 'role', __('User Role'), __('— All Roles —'), ['organization_admin' => __('Organization Admin'), 'manager' => __('Manager'), 'moderator' => __('Moderator'), 'staff' => __('Staff')]],
        [2, 'brand_id', __('Brand'), __('— All Brands —'), collect($org?->brands ?? [])->pluck('name', 'id')->all()],
        [3, 'location_id', __('Location'), __('— All Locations —'), collect($org?->locations ?? [])->pluck('name', 'id')->all()],
        [4, 'department_id', __('Department'), __('— All Departments —'), collect($org?->departments ?? [])->pluck('name', 'id')->all()],
    ];
@endphp

<div class="row justify-content-center">
    <div class="col-lg-8">
        <form action="{{ isset($segment) ? route('audience-segments.update', $segment) : route('audience-segments.store') }}" method="POST">
            @csrf
            @if (isset($segment)) @method('PUT') @endif

            <x-valex.card :title="__('Basic Information')">
                <x-valex.input name="name" :label="__('Segment Name')" :value="$segment->name ?? ''" required :placeholder="__('e.g., VIP Members, Support Staff')" />
                <x-valex.textarea name="description" :label="__('Description') . ' (' . __('optional') . ')'" rows="2" :value="$segment->description ?? ''" />
            </x-valex.card>

            <x-valex.card :title="__('Targeting Rules')" :subtitle="__('Leave a rule blank to match everyone on that dimension.')">
                @foreach ($ruleFields as [$index, $type, $label, $allLabel, $options])
                    <div class="form-group">
                        <label for="rule-{{ $type }}">{{ $label }}</label>
                        <select class="form-control" id="rule-{{ $type }}" name="rules[{{ $index }}][value]">
                            <option value="">{{ $allLabel }}</option>
                            @foreach ($options as $optValue => $optLabel)
                                <option value="{{ $optValue }}" @selected((string) $ruleValue($index, $type) === (string) $optValue)>{{ $optLabel }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="rules[{{ $index }}][type]" value="{{ $type }}">
                    </div>
                @endforeach
            </x-valex.card>

            <x-valex.card>
                <x-valex.switch name="is_active" :label="__('Active (available for targeting)')" :checked="$segment->is_active ?? true" />
            </x-valex.card>

            <div class="alert alert-info">
                <strong>{{ __('Note:') }}</strong> {{ __('When multiple rules are specified, a user must match ALL rules to be included in this segment (AND logic).') }}
            </div>

            <button type="submit" class="btn btn-primary"><i class="fe fe-check"></i> {{ isset($segment) ? __('Update Segment') : __('Create Segment') }}</button>
            <a href="{{ route('audience-segments.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
