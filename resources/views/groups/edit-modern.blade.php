@extends('layouts.valex')

@section('title', __('Edit Group'))

@section('page-header')
    <x-valex.page-header :title="__('Edit Group')" :subtitle="$group->name" />
@endsection

@section('content')
<div class="row">
    <div class="col-lg-7">
        <x-valex.card :title="__('Group Information')">
            <form action="{{ route('dashboard.groups.update', [session('current_organization_id'), $group]) }}" method="POST">
                @csrf
                @method('PUT')
                <x-valex.input name="name" :label="__('Group Name')" :value="$group->name" required :placeholder="__('Enter group name')" />
                <x-valex.textarea name="description" :label="__('Description')" :value="$group->description" :placeholder="__('Optional description')" />
                <x-valex.select name="branch_id" :label="__('Branch')" required :placeholder="__('— Select Branch —')" :options="$branches->pluck('name', 'id')->all()" :value="$group->branch_id" />

                <button type="submit" class="btn btn-primary">{{ __('Update Group') }}</button>
                <a href="{{ route('dashboard.groups.show', [session('current_organization_id'), $group]) }}" class="btn btn-light">{{ __('Cancel') }}</a>
            </form>
        </x-valex.card>
    </div>
</div>
@endsection
