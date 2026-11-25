@extends('layouts.valex')
@section('title', __('Create Post'))
@section('page-header')
<x-valex.page-header :title="__('Create Post')" :subtitle="__('Choose your audience. Make your message stand out.')" />
@endsection
@section('content')
@include('posts._composer', ['action' => route('posts.store'), 'channel' => null, 'backUrl' => route('posts.index')])
@endsection
