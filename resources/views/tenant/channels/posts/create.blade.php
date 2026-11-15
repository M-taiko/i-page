@extends('layouts.valex')

@section('title', __('Create Post') . ' - ' . $channel->name)

@section('page-header')
    <x-valex.page-header :title="__('Create Post')" :subtitle="__('Add a new post to :name', ['name' => $channel->name])">
        <x-slot:actions>
            <a href="{{ route('tenant.channels.show', $channel->id) }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ $channel->name }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <form method="POST" action="{{ route('tenant.channels.posts.store', $channel->id) }}" enctype="multipart/form-data" id="postForm">
            @csrf

            @include('tenant.channels.posts._post-fields', ['targetName' => $channel->name, 'targetDescription' => $channel->description])

            @include('tenant.channels.posts._publish-options')

            <button type="submit" class="btn btn-primary"><i class="fe fe-check-circle"></i> {{ __('Publish Post') }}</button>
            <a href="{{ route('tenant.channels.show', $channel->id) }}" class="btn btn-light">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
