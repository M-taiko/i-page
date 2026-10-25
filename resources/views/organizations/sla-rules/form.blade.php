@extends('layouts.valex')

@section('title', isset($rule) ? __('Edit SLA Rule') : __('Create SLA Rule'))

@section('page-header')
    <x-valex.page-header :title="isset($rule) ? __('Edit SLA Rule') : __('Create SLA Rule')" :subtitle="$organization->name">
        <x-slot:actions>
            <a href="{{ route('organizations.settings', $organization) }}#sla" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <form action="{{ isset($rule) ? route('organizations.sla-rules.update', [$organization, $rule]) : route('organizations.sla-rules.store', $organization) }}" method="POST">
            @csrf
            @if (isset($rule)) @method('PUT') @endif

            <x-valex.card :title="__('Basic Information')">
                <x-valex.input name="name" :label="__('Rule Name')" :value="$rule->name ?? ''" :placeholder="__('e.g., Standard Support')" required />
                <x-valex.textarea name="description" :label="__('Description')" rows="2" :value="$rule->description ?? ''" />
            </x-valex.card>

            <x-valex.card :title="__('Scope & Conditions')">
                <p class="small text-muted">{{ __('Leave blank to match all tickets. Specify values to narrow the scope.') }}</p>
                <x-valex.select name="brand_id" :label="__('Brand (Optional)')" :placeholder="__('-- Match All Brands --')" :options="$organization->brands->pluck('name', 'id')->all()" :value="$rule->brand_id ?? null" />
                <x-valex.select name="location_id" :label="__('Location (Optional)')" :placeholder="__('-- Match All Locations --')" :options="$organization->locations->pluck('name', 'id')->all()" :value="$rule->location_id ?? null" />
                <x-valex.select name="category_id" :label="__('Category (Optional)')" :placeholder="__('-- Match All Categories --')" :options="$organization->ticketCategories->pluck('name', 'id')->all()" :value="$rule->category_id ?? null" />
                <x-valex.select name="priority" :label="__('Priority (Optional)')" :placeholder="__('-- Match All Priorities --')" :value="$rule->priority ?? ''"
                    :options="['low' => __('Low'), 'medium' => __('Medium'), 'high' => __('High'), 'urgent' => __('Urgent')]" />
            </x-valex.card>

            <x-valex.card :title="__('Response Times (Minutes)')">
                <div class="alert alert-info small"><i class="fe fe-info"></i> {{ __('Set response/resolution time targets. Leave blank to not track this metric.') }}</div>
                <x-valex.input name="first_response_time" type="number" min="1" :label="__('First Response Time (Minutes)')" :value="$rule->first_response_time ?? ''" placeholder="120" :help="__('Time allowed before first staff response.')" />
                <x-valex.input name="resolution_time" type="number" min="1" :label="__('Resolution Time (Minutes)')" :value="$rule->resolution_time ?? ''" placeholder="1440" :help="__('Time allowed to resolve the ticket.')" />
                <x-valex.input name="re_open_response_time" type="number" min="1" :label="__('Re-Open Response Time (Minutes)')" :value="$rule->re_open_response_time ?? ''" placeholder="60" :help="__('Time allowed to respond if a resolved ticket is reopened.')" />
            </x-valex.card>

            <x-valex.card :title="__('Status')">
                <x-valex.switch name="is_active" :label="__('Active')" :checked="$rule->is_active ?? true" />
            </x-valex.card>

            <button type="submit" class="btn btn-primary"><i class="fe fe-check"></i> {{ isset($rule) ? __('Update SLA Rule') : __('Create SLA Rule') }}</button>
            <a href="{{ route('organizations.settings', $organization) }}#sla" class="btn btn-light">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
