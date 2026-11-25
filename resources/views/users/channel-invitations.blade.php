@extends('layouts.valex')
@section('title', __('QR Invitations'))
@section('page-header')<x-valex.page-header :title="__('QR Invitations')" :subtitle="__('Prepare channel permissions before someone joins.')"><x-slot:actions><a href="{{ route('dashboard.users.index', $organization) }}" class="btn btn-light">{{ __('Back to team') }}</a></x-slot:actions></x-valex.page-header>@endsection
@section('content')
@if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('dashboard.channel-invitations.store', $organization) }}">@csrf
<x-valex.card :title="__('Create an invitation')" :subtitle="__('Single use. Existing users sign in; new users register and receive these permissions automatically.')">
<div class="row"><div class="col-md-5"><x-valex.input name="label" :label="__('Invitation name')" required maxlength="120" /></div><div class="col-md-5"><x-valex.input name="email" type="email" :label="__('Restrict to email (optional)')" :help="__('Leave empty to allow the first person who accepts the link.')" /></div><div class="col-md-2"><x-valex.input name="days" type="number" :label="__('Valid for (days)')" :value="7" min="1" max="30" required /></div></div>
@include('users._channel-grants')
<button type="submit" class="btn btn-primary"><i class="fe fe-grid"></i> {{ __('Generate invitation QR') }}</button>
</x-valex.card></form>
<x-valex.card :title="__('Invitation history')"><div class="table-responsive"><table class="table"><thead><tr><th>{{ __('Invitation') }}</th><th>{{ __('Expires') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr></thead><tbody>
@forelse($invitations as $invite)<tr><td>{{ $invite->label }}<small class="d-block text-muted">{{ $invite->metadata['email'] ?? __('Anyone with the link') }}</small></td><td>{{ $invite->expires_at?->format('Y-m-d H:i') }}</td><td><span class="badge badge-{{ $invite->canBeScanned() ? 'success' : 'secondary' }}">{{ !empty($invite->metadata['redeemed_by']) ? __('Accepted') : ($invite->canBeScanned() ? __('Active') : __('Expired / cancelled')) }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('dashboard.channel-invitations.preview', [$organization,$invite]) }}">{{ __('View QR') }}</a>@if($invite->canBeScanned())<form class="d-inline" method="POST" action="{{ route('dashboard.channel-invitations.revoke', [$organization,$invite]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-light">{{ __('Cancel invitation') }}</button></form>@endif</td></tr>@empty<tr><td colspan="4">{{ __('No invitations yet.') }}</td></tr>@endforelse
</tbody></table></div>{{ $invitations->links('pagination::bootstrap-4') }}</x-valex.card>
@endsection
