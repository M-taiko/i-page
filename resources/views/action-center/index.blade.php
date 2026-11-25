@extends('layouts.valex')
@section('title', __('Action center'))
@section('page-header')
<x-valex.page-header :title="__('Action center')" :subtitle="$organization->name . ' · ' . __('Only requests you can act on appear here.')">
<x-slot:actions><a class="btn btn-outline-primary" href="{{ route('organizations.dashboard') }}">{{ __('Dashboard') }}</a></x-slot:actions>
</x-valex.page-header>
@endsection
@section('content')
@php $types = ['posts' => [__('Posts awaiting approval'), 'file-text'], 'organization' => [__('Organization join requests'), 'user-plus'], 'channels' => [__('Channel join requests'), 'message-circle']]; @endphp
<div class="row row-sm">
@foreach ($types as $key => [$label, $icon])
<div class="col-md-4"><a class="ip-task-summary" href="{{ route('tenant.actions.index', ['type' => $key]) }}" @if(request('type') === $key) aria-current="page" @endif><x-valex.stat-card :title="$label" :value="$counts[$key]" :icon="$icon" /></a></div>
@endforeach
</div>
<x-valex.card :title="__('Your pending tasks')" :subtitle="__('Oldest first · Decisions are checked again before saving.')">
<x-slot:actions><a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.actions.index') }}">{{ __('All') }} ({{ array_sum($counts) }})</a></x-slot:actions>
<div class="ip-task-list">
@forelse ($items as $item)
@php $person = $people->get($item->subject_user_id); $channel = $channels->get($item->channel_id); @endphp
<article class="ip-task-row">
<span class="ip-task-icon"><i class="fe fe-{{ $types[$item->kind][1] }}" aria-hidden="true"></i></span>
<div class="ip-task-copy"><span class="ip-task-type">{{ $types[$item->kind][0] }}</span><h3>{{ $item->kind === 'posts' ? ($item->title ?: __('Untitled post')) : ($person?->full_name ?? __('User')) }}</h3>
<p>{{ $organization->name }} @if($channel) · {{ $channel->name }} @endif · {{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</p>
@if($item->kind === 'posts')<a href="{{ route('posts.show', $item->id) }}">{{ __('Review content') }} <i class="fe fe-arrow-right"></i></a>@endif
</div>
<form method="POST" action="{{ route('tenant.actions.decide', [$item->kind, $item->id]) }}" class="ip-task-decisions">@csrf
@if($item->kind === 'channels')<input type="hidden" name="user_id" value="{{ $item->subject_user_id }}">@endif
<button class="btn btn-primary" name="decision" value="approve"><i class="fe fe-check"></i> {{ __('Approve') }}</button>
<button class="btn btn-outline-danger" name="decision" value="reject" onclick="return confirm(@js(__('Reject this request?')))" >{{ __('Reject') }}</button>
</form>
</article>
@empty
<div class="text-center py-5"><span class="ip-task-icon mx-auto mb-3"><i class="fe fe-check-circle"></i></span><h3>{{ __('You are all caught up') }}</h3><p class="text-muted">{{ __('No pending requests match your access and current filter.') }}</p></div>
@endforelse
</div>
<div class="mt-3">{{ $items->links('pagination::bootstrap-4') }}</div>
</x-valex.card>
@endsection
