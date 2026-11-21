@extends('layouts.valex')

@section('title', __('New tenant post'))

@section('page-header')
    <x-valex.page-header :title="__('New tenant post')" :subtitle="__('Publish as :name — on its page, in every channel, or in the channels you choose', ['name' => $organization->name])">
        <x-slot:actions>
            <a href="{{ route('organizations.posts.index') }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Tenant posts') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <form method="POST" action="{{ route('organizations.posts.store') }}" enctype="multipart/form-data" id="postForm">
            @csrf

            @include('tenant.channels.posts._post-fields', ['targetName' => $organization->name, 'targetDescription' => $organization->description, 'targetIcon' => 'home'])

            @include('tenant.channels.posts._publish-options', ['tenant' => $organization, 'channelTree' => $channelTree])

            <button type="submit" class="btn btn-primary"><i class="fe fe-send"></i> {{ __('Publish') }}</button>
            <a href="{{ route('organizations.posts.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
