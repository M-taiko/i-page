@extends('layouts.valex')
@section('title', __('Channels'))
@section('page-header')
<x-valex.page-header :title="__('Channels')" :subtitle="$organization->name . ' · ' . __('Explore your channel hierarchy')">
<x-slot:actions>
@if(auth()->user()->hasRole('super_admin'))
<form action="{{ route('tenant.switch-organization') }}" method="POST">@csrf
<select name="organization_id" class="form-control" aria-label="{{ __('Organization') }}" onchange="this.form.submit()">
@foreach(\App\Models\Organization::orderBy('name')->get() as $choice)<option value="{{ $choice->id }}" @selected($choice->id === $organization->id)>{{ $choice->name }}</option>@endforeach
</select></form>
@endif
@if(\App\Models\Channel::isOrgManager(auth()->user(),$organization->id))
<a href="{{ route('tenant.channels.create') }}" class="btn btn-primary"><i class="fe fe-plus"></i> {{ __('Create main channel') }}</a>
@endif
</x-slot:actions></x-valex.page-header>
@endsection
@section('content')
<x-valex.card :title="$organization->name" :subtitle="__('Expand any channel to explore its sub-channels. Permissions apply at every level.')">
<ul class="ip-channel-tree">
@forelse($treeChildren[0] ?? [] as $node)
@include('tenant.channels._tree-node',['seen'=>[]])
@empty<li class="text-muted p-4">{{ __('No channels available in your scope.') }}</li>@endforelse
</ul>
</x-valex.card>
@endsection
@push('styles')
<style>
.ip-channel-tree,.ip-channel-tree ul{list-style:none;margin:0;padding:0}.ip-channel-tree ul{margin-inline-start:18px;border-inline-start:2px solid #e1e7f3;padding-inline-start:18px}.ip-channel-node{padding:12px 0}.ip-channel-node summary{cursor:pointer;padding:12px;border-radius:10px;background:var(--ip-bg,#f6f8fc);font-size:15px}.ip-channel-node summary>*{margin-inline:6px}.ip-channel-node-row{display:flex;gap:12px;align-items:center;flex-wrap:wrap;padding:12px}.ip-channel-node-row>a:first-child{margin-inline-end:auto;font-weight:600}@media(max-width:575px){.ip-channel-tree ul{margin-inline-start:6px;padding-inline-start:8px}.ip-channel-node-row{gap:8px}}
</style>
@endpush
