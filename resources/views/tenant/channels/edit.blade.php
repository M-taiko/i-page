@extends('layouts.valex')

@section('title', __('Edit Channel'))

@section('page-header')
    <x-valex.page-header :title="__('Edit Channel')" :subtitle="__('Update channel information and settings')">
        <x-slot:actions>
            <a href="{{ route('tenant.channels.show', $channel->id) }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back to Channel') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
    @include('tenant.channels._form', ['selectedParentId' => $currentParentId ?? null])
@endsection
