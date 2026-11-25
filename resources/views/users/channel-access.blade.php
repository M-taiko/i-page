@extends('layouts.valex')
@section('title', __('Channel permissions'))
@section('page-header')<x-valex.page-header :title="__('Channel permissions')" :subtitle="$user->full_name . ' · ' . $organization->name"><x-slot:actions><a href="{{ route('dashboard.users.index', $organization) }}" class="btn btn-light">{{ __('Back to team') }}</a></x-slot:actions></x-valex.page-header>@endsection
@section('content')
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('dashboard.users.channel-access.update', [$organization,$user]) }}">@csrf @method('PUT')
<x-valex.card :title="__('Access by channel')" :subtitle="__('Changes apply to this organization only.')">@include('users._channel-grants')<button class="btn btn-primary" type="submit"><i class="fe fe-shield"></i> {{ __('Save permissions') }}</button></x-valex.card></form>
@endsection
