@extends('layouts.valex')

@section('title', $channel->name)

@section('page-header')
    <x-valex.page-header :title="$channel->name" :subtitle="__('Channel')">
        <x-slot:actions>
            <a href="{{ route('tenant.channels.index') }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back to Channels') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<x-valex.hero :title="$channel->name">
    <x-valex.badge :variant="$channel->type === 'public' ? 'success' : 'warning'"><i class="fe fe-{{ $channel->type === 'public' ? 'globe' : 'lock' }}"></i> {{ $channel->type === 'public' ? __('Public') : __('Private') }}</x-valex.badge>
    <x-valex.badge variant="info">{{ ucfirst($channel->status) }}</x-valex.badge>
</x-valex.hero>

<div class="row row-sm">
    <div class="col-md-4"><x-valex.stat-card :title="__('Members')" :value="$stats['members']" icon="users" variant="primary" /></div>
    <div class="col-md-4"><x-valex.stat-card :title="__('Posts')" :value="$stats['posts']" icon="file-text" variant="success" /></div>
    <div class="col-md-4"><x-valex.stat-card :title="__('QR Codes')" :value="$stats['qr_codes']" icon="grid" variant="warning" /></div>
</div>

<div class="row">
    <div class="col-lg-8">
        <x-valex.card :title="__('Channel Information')">
            <div class="mb-3"><div class="small text-muted text-uppercase">{{ __('Channel Name') }}</div><strong>{{ $channel->name }}</strong></div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="small text-muted text-uppercase">{{ __('Channel Type') }}</div>
                    <x-valex.badge :variant="$channel->type === 'public' ? 'success' : 'warning'">{{ $channel->type === 'public' ? __('Public Channel') : __('Private Channel') }}</x-valex.badge>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="small text-muted text-uppercase">{{ __('Status') }}</div>
                    <x-valex.badge variant="info">{{ ucfirst($channel->status) }}</x-valex.badge>
                </div>
            </div>
            <div class="small text-muted text-uppercase">{{ __('Description') }}</div>
            <p class="mb-0">{{ $channel->description ?? __('No description provided') }}</p>
        </x-valex.card>

        <x-valex.card :title="__('Additional Information')">
            <div class="row">
                <div class="col-md-6 mb-3"><div class="small text-muted text-uppercase">{{ __('Created Date') }}</div>{{ $channel->created_at->format('M d, Y') }}</div>
                <div class="col-md-6 mb-3"><div class="small text-muted text-uppercase">{{ __('Created At') }}</div>{{ $channel->created_at->format('h:i A') }}</div>
                @if ($channel->updated_at->ne($channel->created_at))
                    <div class="col-md-6"><div class="small text-muted text-uppercase">{{ __('Last Updated') }}</div>{{ $channel->updated_at->format('M d, Y h:i A') }}</div>
                @endif
            </div>
        </x-valex.card>
    </div>

    <div class="col-lg-4">
        <x-valex.card :title="__('Actions')">
            <a href="{{ route('tenant.channels.posts.create', $channel->id) }}" class="btn btn-success btn-block mb-2"><i class="fe fe-plus-circle"></i> {{ __('Create Post') }}</a>
            <a href="{{ route('tenant.channels.edit', $channel->id) }}" class="btn btn-primary btn-block mb-2"><i class="fe fe-edit"></i> {{ __('Edit Channel') }}</a>
            <form action="{{ route('tenant.channels.destroy', $channel->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('{{ __('Are you sure you want to delete this channel? All associated data will be deleted!') }}')">
                    <i class="fe fe-trash-2"></i> {{ __('Delete Channel') }}
                </button>
            </form>
        </x-valex.card>
    </div>
</div>

{{-- Pending join requests --}}
@if ($pendingMembers->count() > 0)
    <x-valex.card :title="__('Pending Join Requests') . ' (' . $pendingMembers->count() . ')'">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th></th></tr></thead>
                <tbody>
                    @foreach ($pendingMembers as $pending)
                        <tr>
                            <td>{{ $pending->full_name }}</td>
                            <td class="text-muted small">{{ $pending->email }}</td>
                            <td class="text-right text-nowrap">
                                <form action="{{ route('tenant.channels.join-requests.approve', [$channel, $pending]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"><i class="fe fe-check"></i> {{ __('Approve') }}</button>
                                </form>
                                <form action="{{ route('tenant.channels.join-requests.reject', [$channel, $pending]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Reject this join request?') }}')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fe fe-x"></i> {{ __('Reject') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-valex.card>
@endif

{{-- Members --}}
<x-valex.card :title="__('Members') . ' (' . $members->count() . ')'">
    <x-slot:actions>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#inviteMemberModal"><i class="fe fe-user-plus"></i> {{ __('Invite Member') }}</button>
    </x-slot:actions>

    @if ($members->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th>{{ __('Role') }}</th><th>{{ __('Joined') }}</th><th></th></tr></thead>
                <tbody>
                    @foreach ($members as $member)
                        <tr>
                            <td>{{ $member->full_name }}</td>
                            <td class="text-muted small">{{ $member->email }}</td>
                            <td>
                                <form action="{{ route('tenant.channels.members.update', [$channel, $member]) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" class="form-control form-control-sm w-auto" onchange="this.form.submit()">
                                        <option value="member" @selected($member->pivot->role === 'member')>{{ __('Staff') }}</option>
                                        <option value="moderator" @selected($member->pivot->role === 'moderator')>{{ __('Moderator') }}</option>
                                        <option value="admin" @selected($member->pivot->role === 'admin')>{{ __('Manager') }}</option>
                                    </select>
                                </form>
                            </td>
                            <td class="text-muted small">{{ $member->pivot->joined_at ? \Illuminate\Support\Carbon::parse($member->pivot->joined_at)->format('M d, Y') : '—' }}</td>
                            <td class="text-right">
                                <form action="{{ route('tenant.channels.members.destroy', [$channel, $member]) }}" method="POST" onsubmit="return confirm('{{ __('Remove this member from the channel?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fe fe-x"></i> {{ __('Remove') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted text-center mb-0">{{ __('No members yet. Invite someone to get started.') }}</p>
    @endif
</x-valex.card>

{{-- QR codes --}}
<x-valex.card :title="__('QR Codes') . ' (' . $qrCodes->count() . ')'">
    <x-slot:actions>
        <form action="{{ route('tenant.channels.qr-codes.store', $channel) }}" method="POST" class="form-inline">
            @csrf
            <input type="text" name="label" class="form-control form-control-sm mx-2" placeholder="{{ __('Label (optional)') }}">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fe fe-plus"></i> {{ __('Generate QR') }}</button>
        </form>
    </x-slot:actions>

    @if ($qrCodes->count() > 0)
        <div class="row">
            @foreach ($qrCodes as $qrCode)
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="card h-100 text-center p-3 mb-0">
                        <img src="data:image/svg+xml;base64,{{ $qrCode->preview_image }}" alt="QR code" class="mx-auto mb-1" style="width: 160px; height: 160px;">
                        <div class="text-muted small text-break mb-1">{{ $qrCode->url }}</div>
                        <div class="font-weight-bold small text-truncate">{{ $qrCode->label ?? __('Untitled QR') }}</div>
                        <div class="text-muted small mb-2">
                            {{ $qrCode->scan_count }} {{ __('scans') }} &middot;
                            <x-valex.badge :variant="$qrCode->is_active ? 'success' : 'secondary'">{{ $qrCode->is_active ? __('Active') : __('Inactive') }}</x-valex.badge>
                        </div>
                        <div class="d-flex justify-content-center">
                            <a href="{{ route('tenant.channels.qr-codes.download', [$channel, $qrCode]) }}" class="btn btn-sm btn-outline-primary mx-1"><i class="fe fe-download"></i></a>
                            <form action="{{ route('tenant.channels.qr-codes.toggle', [$channel, $qrCode]) }}" method="POST" class="mx-1">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fe fe-{{ $qrCode->is_active ? 'pause' : 'play' }}"></i></button>
                            </form>
                            <form action="{{ route('tenant.channels.qr-codes.destroy', [$channel, $qrCode]) }}" method="POST" class="mx-1" onsubmit="return confirm('{{ __('Delete this QR code?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fe fe-trash-2"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-muted text-center mb-0">{{ __('No QR codes yet. Generate one to let guests scan their way into this channel.') }}</p>
    @endif
</x-valex.card>

{{-- Posts --}}
<h5 class="mb-3"><i class="fe fe-file-text"></i> {{ __('Posts in this Channel') }}</h5>
@php $channelPosts = $channel->posts()->latest('published_at')->get(); @endphp
@if ($channelPosts->count() > 0)
    <div class="row">
        @foreach ($channelPosts as $post)
            <div class="col-md-6 col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="avatar avatar-md brround bg-primary mx-2">{{ $post->author->initials }}</span>
                            <div>
                                <div class="font-weight-bold">{{ $post->author->full_name }}</div>
                                <small class="text-muted">{{ $post->published_at?->diffForHumans() ?? __('Not published') }}</small>
                            </div>
                        </div>
                        <p class="text-muted mb-0">{{ Str::limit($post->body, 200) }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <x-valex.empty-state :title="__('No Posts Yet')" :message="__('Start by creating the first post in this channel')" icon="inbox">
        <a href="{{ route('tenant.channels.posts.create', $channel->id) }}" class="btn btn-success mt-3"><i class="fe fe-plus-circle"></i> {{ __('Create First Post') }}</a>
    </x-valex.empty-state>
@endif

{{-- Invite member modal --}}
<div class="modal fade" id="inviteMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tenant.channels.members.store', $channel) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fe fe-user-plus"></i> {{ __('Invite Member') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">{{ __('Enter the email of any user — if they don\'t have an account yet, one will be created for them automatically.') }}</p>
                    <div class="form-group">
                        <label>{{ __('Email') }} <span class="tx-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required placeholder="name@example.com">
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label>{{ __('First Name') }}</label><input type="text" name="first_name" class="form-control" placeholder="{{ __('Optional, if new user') }}"></div>
                        <div class="col-md-6 form-group"><label>{{ __('Last Name') }}</label><input type="text" name="last_name" class="form-control" placeholder="{{ __('Optional, if new user') }}"></div>
                    </div>
                    <div class="form-group mb-0">
                        <label>{{ __('Role') }}</label>
                        <select name="role" class="form-control" required>
                            <option value="member">{{ __('Staff — can post') }}</option>
                            <option value="moderator">{{ __('Moderator — moderates content') }}</option>
                            <option value="admin">{{ __('Manager — full channel control') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="fe fe-check"></i> {{ __('Add to Channel') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
