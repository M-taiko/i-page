@extends('layouts.valex')
@section('title', __('Create Post') . ' - ' . $channel->name)
@section('page-header')
<x-valex.page-header :title="__('Create Post')" :subtitle="$channel->name . ' · ' . __('Choose your audience. Make your message stand out.')" />
@endsection
@section('content')
@include('posts._composer', ['action' => route('tenant.channels.posts.store', $channel), 'channels' => collect([$channel]), 'backUrl' => route('tenant.channels.show', $channel)])
@endsection
