@extends('layouts.valex')

@section('title', __('Tenant posts'))

@section('page-header')
    <x-valex.page-header :title="__('Tenant posts')" :subtitle="__('Posts published by :name on its page and in its channels', ['name' => $organization->name])">
        <x-slot:actions>
            <a href="{{ route('organizations.posts.create') }}" class="btn btn-primary"><i class="fe fe-send"></i> {{ __('New tenant post') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<x-valex.card>
    @include('organizations.hub._post-list', ['posts' => $posts])
    <div class="mt-3">{{ $posts->links() }}</div>
</x-valex.card>
@endsection
