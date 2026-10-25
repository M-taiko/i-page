@extends('layouts.valex')

@section('title', __('Create Channel'))

@section('page-header')
    <x-valex.page-header :title="__('Create Channel')" />
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6">
        <x-valex.card>
            <form action="{{ route('dashboard.channels.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <x-valex.input name="name" :label="__('Channel Name')" required />
                <x-valex.select name="type" :label="__('Type')" required :placeholder="__('— Select Type —')"
                    :options="['public' => __('Public'), 'private' => __('Private')]" />
                <x-valex.select name="audience_profile" :label="__('Audience Profile')" required :placeholder="__('— Select Profile —')"
                    :options="['business' => __('Business'), 'public' => __('Public'), 'private' => __('Private')]" />
                <x-valex.input name="audience_count" type="number" min="1" :label="__('Expected Audience Count')" />
                <x-valex.input name="logo" type="file" :label="__('Channel Logo')" accept="image/png,image/jpeg,image/svg+xml" :help="__('PNG, JPG, SVG (max 2MB)')" />

                <button type="submit" class="btn btn-primary">{{ __('Create Channel') }}</button>
                <a href="{{ route('dashboard.home') }}" class="btn btn-light">{{ __('Cancel') }}</a>
            </form>
        </x-valex.card>
    </div>
</div>
@endsection
