@extends('layouts.valex')

@section('title', isset($category) ? __('Edit Ticket Category') : __('Create Ticket Category'))

@section('page-header')
    <x-valex.page-header :title="isset($category) ? __('Edit Ticket Category') : __('Create Ticket Category')" :subtitle="$organization->name" />
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <form action="{{ isset($category) ? route('organizations.ticket-categories.update', [$organization, $category]) : route('organizations.ticket-categories.store', $organization) }}" method="POST">
            @csrf
            @if (isset($category)) @method('PUT') @endif

            <x-valex.card>
                <x-valex.input name="name" :label="__('Category Name')" :value="$category->name ?? ''" required />
                <x-valex.textarea name="description" :label="__('Description')" rows="3" :value="$category->description ?? ''" />
                <x-valex.input name="display_order" type="number" min="0" :label="__('Display Order')" :value="$category->display_order ?? 0" :help="__('Lower numbers appear first in lists.')" />
                <x-valex.switch name="is_active" :label="__('Active')" :checked="$category->is_active ?? true" />
            </x-valex.card>

            <button type="submit" class="btn btn-primary"><i class="fe fe-check"></i> {{ isset($category) ? __('Update Category') : __('Create Category') }}</button>
            <a href="{{ route('organizations.settings', $organization) }}" class="btn btn-light">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
