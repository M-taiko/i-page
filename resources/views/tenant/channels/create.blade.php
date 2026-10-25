@extends('layouts.valex')

@section('title', __('Create New Channel'))

@section('page-header')
    <x-valex.page-header :title="__('Create New Channel')" :subtitle="__('Set up a new channel for your organization')">
        <x-slot:actions>
            <a href="{{ route('tenant.channels.index') }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back to Channels') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
    @include('tenant.channels._form', ['selectedParentId' => $preselectedParentId ?? null])
@endsection
