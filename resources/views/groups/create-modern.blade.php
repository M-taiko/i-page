@extends('layouts.valex')

@section('title', __('Create Group'))

@section('page-header')
    <x-valex.page-header :title="__('Create Group')" :subtitle="__('Add a new group to organize your team')" />
@endsection

@section('content')
<div class="row">
    <div class="col-lg-7">
        <x-valex.card :title="__('New Group')">
            <form action="{{ route('dashboard.groups.store', session('current_organization_id')) }}" method="POST">
                @csrf
                <x-valex.input name="name" :label="__('Group Name')" required :placeholder="__('Enter group name')" />
                <x-valex.textarea name="description" :label="__('Description')" :placeholder="__('Optional description')" />
                <x-valex.select name="branch_id" :label="__('Branch')" required :placeholder="__('— Select Branch —')" :options="$branches->pluck('name', 'id')->all()" />

                <button type="submit" class="btn btn-primary">{{ __('Create Group') }}</button>
                <a href="{{ route('dashboard.groups.index', session('current_organization_id')) }}" class="btn btn-light">{{ __('Cancel') }}</a>
            </form>
        </x-valex.card>
    </div>
</div>
@endsection
