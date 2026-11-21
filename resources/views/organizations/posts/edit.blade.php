@extends('layouts.valex')

@section('title', __('Edit Post') . ' - ' . $organization->name)

@section('page-header')
    <x-valex.page-header :title="__('Edit Post')" :subtitle="$organization->name">
        <x-slot:actions>
            <a href="{{ route('guest.post-detail', $post->id) }}" class="btn btn-outline-secondary" target="_blank"><i class="fe fe-eye"></i> {{ __('View post') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $formats = config('posts.formats');
    $format = $post->post_format ?: 'standard';
@endphp
<div class="row justify-content-center">
    <div class="col-lg-9">
        <form method="POST" action="{{ route('organizations.posts.update', $post->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <x-valex.card :title="__('Content')">
                <div class="alert alert-light border small">
                    <i class="fe fe-{{ $formats[$format]['icon'] }}"></i> {{ __('Post type') }}: <strong>{{ __($formats[$format]['name']) }}</strong>
                    — {{ __('the type, options and photos of a post are fixed once it is published.') }}
                </div>
                <x-valex.input name="title" :label="__('Title')" maxlength="150" :value="$post->title" />
                <x-valex.textarea name="body" rows="7" required maxlength="5000" :label="__('Post Content')" :value="$post->body" />
            </x-valex.card>

            <x-valex.card :title="__('Appearance & priority')">
                <div class="row">
                    <div class="col-md-4">
                        <x-valex.select name="card_style" :label="__('Card design')" :value="$post->card_style"
                            :options="collect(config('posts.card_styles'))->map(fn ($s) => __($s['name']))->all()" />
                    </div>
                    <div class="col-md-4">
                        <x-valex.select name="priority" :label="__('Priority')" :value="$post->priority"
                            :options="collect(config('posts.priorities'))->map(fn ($l) => __($l))->all()" />
                    </div>
                    <div class="col-md-4">
                        <x-valex.select name="badge_key" :label="__('Badge')" :value="$post->badge_key" :placeholder="__('No badge')"
                            :options="collect(config('posts.badges'))->map(fn ($b) => __($b[0]))->all()" />
                    </div>
                    <div class="col-md-6"><x-valex.input name="badge_label" :label="__('Custom badge text (optional)')" maxlength="40" :value="$post->badge_label" /></div>
                </div>
            </x-valex.card>

            @include('tenant.channels.posts._publish-options', ['post' => $post, 'tenant' => $organization, 'channelTree' => $channelTree])

            <button type="submit" class="btn btn-primary"><i class="fe fe-check"></i> {{ __('Save changes') }}</button>
            <a href="{{ route('organizations.posts.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
