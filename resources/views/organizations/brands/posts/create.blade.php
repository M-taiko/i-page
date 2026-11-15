@extends('layouts.valex')

@section('title', __('New brand post') . ' - ' . $brand->name)

@section('page-header')
    <x-valex.page-header :title="__('New brand post')" :subtitle="__('One post for every channel of :name', ['name' => $brand->name])">
        <x-slot:actions>
            <a href="{{ route('organizations.brands.show', [$organization, $brand]) }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ $brand->name }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <form method="POST" action="{{ route('organizations.brands.posts.store', [$organization, $brand]) }}" enctype="multipart/form-data" id="postForm">
            @csrf

            @include('tenant.channels.posts._post-fields', ['targetName' => $brand->name, 'targetDescription' => $brand->description, 'targetIcon' => 'award'])

            @include('tenant.channels.posts._publish-options', ['brand' => $brand])

            <button type="submit" class="btn btn-primary"><i class="fe fe-send"></i> {{ __('Publish to brand') }}</button>
            <a href="{{ route('organizations.brands.show', [$organization, $brand]) }}" class="btn btn-light">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
