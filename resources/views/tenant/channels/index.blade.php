@extends('layouts.valex')

@section('title', __('Channels'))

@section('page-header')
    <x-valex.page-header :title="__('Channels')" :subtitle="$organization->name">
        <x-slot:actions>
            @if (auth()->user()->hasRole('super_admin'))
                <form action="{{ route('tenant.switch-organization') }}" method="POST" id="orgSwitcherForm" class="form-inline mx-2">
                    @csrf
                    <select name="organization_id" class="form-control" onchange="document.getElementById('orgSwitcherForm').submit()" title="{{ __('Viewing') }}">
                        @foreach (\App\Models\Organization::orderBy('name')->get() as $org)
                            <option value="{{ $org->id }}" @selected($org->id === $organization->id)>{{ $org->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            @if ($channels->total() < $organization->max_channels)
                <a href="{{ route('tenant.channels.create') }}" class="btn btn-primary"><i class="fe fe-plus-circle"></i> {{ __('Create New Channel') }}</a>
            @else
                <button class="btn btn-secondary" disabled title="{{ __('Maximum channels reached') }}">{{ __('Maximum Reached') }}</button>
            @endif
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row row-sm">
    <div class="col-md-4"><x-valex.stat-card :title="__('Channels Created')" :value="$channels->total() . '/' . $organization->max_channels" icon="message-circle" variant="primary" /></div>
    <div class="col-md-4"><x-valex.stat-card :title="__('Total Members')" :value="$channels->sum(fn ($c) => $c->users()->count())" icon="users" variant="success" /></div>
    <div class="col-md-4"><x-valex.stat-card :title="__('Total Posts')" :value="$channels->sum(fn ($c) => $c->posts()->count())" icon="file-text" variant="warning" /></div>
</div>

@forelse ($channels as $channel)
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex">
                    <button type="button" class="btn p-0 border-0 mx-2" data-toggle="modal" data-target="#qrModal{{ $channel->id }}" title="{{ __('View QR Code') }}">
                        <img src="data:image/svg+xml;base64,{{ $channel->primaryQrCode->preview_image }}" alt="QR" class="border rounded" style="width: 64px; height: 64px;">
                    </button>
                    <div>
                        <h5 class="mb-1">{{ $channel->name }}</h5>
                        <p class="text-muted mb-0">{{ $channel->description ?? __('No description') }}</p>
                    </div>
                </div>
                <div>
                    <x-valex.badge :variant="$channel->type === 'public' ? 'success' : 'pink'">
                        <i class="fe fe-{{ $channel->type === 'public' ? 'globe' : 'lock' }}"></i> {{ $channel->type === 'public' ? __('Public') : __('Private') }}
                    </x-valex.badge>
                    <x-valex.badge :variant="$channel->status === 'active' ? 'primary' : 'secondary'">{{ $channel->status === 'active' ? __('Active') : __('Paused') }}</x-valex.badge>
                </div>
            </div>

            <div class="row text-center border-top border-bottom my-3 py-3">
                <div class="col-4"><h4 class="text-primary mb-0">{{ $channel->users()->count() }}</h4><small class="text-muted text-uppercase">{{ __('Members') }}</small></div>
                <div class="col-4"><h4 class="text-primary mb-0">{{ $channel->posts()->count() }}</h4><small class="text-muted text-uppercase">{{ __('Posts') }}</small></div>
                <div class="col-4"><h4 class="text-primary mb-0">{{ $channel->created_at->diffForHumans(parts: 1, syntax: 'short') }}</h4><small class="text-muted text-uppercase">{{ __('Created') }}</small></div>
            </div>

            @if ($channel->childChannels->count() > 0)
                <div class="bg-light rounded p-3 mb-3">
                    <div class="small text-muted text-uppercase font-weight-bold mb-2"><i class="fe fe-share-2"></i> {{ __('Sub-Channels') }} ({{ $channel->childChannels->count() }})</div>
                    @foreach ($channel->childChannels as $sub)
                        <a href="{{ route('tenant.channels.show', $sub->id) }}" class="badge badge-light border p-2 m-1"><i class="fe fe-message-circle"></i> {{ $sub->name }}</a>
                    @endforeach
                </div>
            @endif

            <div class="d-flex flex-wrap justify-content-end">
                <a href="{{ route('tenant.channels.create', ['parent' => $channel->id]) }}" class="btn btn-sm btn-outline-success m-1"><i class="fe fe-share-2"></i> {{ __('Add Sub-Channel') }}</a>
                <a href="{{ route('tenant.channels.show', $channel->id) }}" class="btn btn-sm btn-outline-primary m-1"><i class="fe fe-eye"></i> {{ __('View') }}</a>
                <a href="{{ route('tenant.channels.edit', $channel->id) }}" class="btn btn-sm btn-outline-warning m-1"><i class="fe fe-edit"></i> {{ __('Edit') }}</a>
                <form action="{{ route('tenant.channels.toggle-status', $channel->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary m-1">
                        <i class="fe fe-{{ $channel->status === 'active' ? 'pause-circle' : 'play-circle' }}"></i> {{ $channel->status === 'active' ? __('Pause') : __('Resume') }}
                    </button>
                </form>
                <form action="{{ route('tenant.channels.destroy', $channel->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger m-1" onclick="return confirm('{{ __('Are you sure you want to delete this channel?') }}')"><i class="fe fe-trash-2"></i> {{ __('Delete') }}</button>
                </form>
            </div>
        </div>
    </div>

    {{-- QR code modal --}}
    <div class="modal fade" id="qrModal{{ $channel->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('QR Code') }} — {{ $channel->name }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    <img src="data:image/svg+xml;base64,{{ $channel->primaryQrCode->preview_image }}" alt="QR" style="width: 200px; height: 200px;" class="mb-2">
                    <p class="text-muted small text-break mb-3">{{ $channel->primaryQrCode->url }}</p>

                    <form action="{{ route('tenant.channels.qr-codes.welcome-message', [$channel, $channel->primaryQrCode]) }}" method="POST" class="text-left mb-3">
                        @csrf
                        @method('PUT')
                        <label class="small font-weight-bold">{{ __('Welcome message (printed under the QR)') }}</label>
                        <textarea name="welcome_message" rows="3" class="form-control mb-2" placeholder="{{ __('e.g. Scan to join our updates channel!') }}">{{ $channel->primaryQrCode->metadata['welcome_message'] ?? '' }}</textarea>
                        <button type="submit" class="btn btn-sm btn-outline-primary btn-block"><i class="fe fe-save"></i> {{ __('Save Message') }}</button>
                    </form>

                    <div class="d-flex mb-3">
                        <a href="{{ route('tenant.channels.qr-codes.download', [$channel, $channel->primaryQrCode]) }}" class="btn btn-sm btn-primary flex-fill mx-1"><i class="fe fe-download"></i> {{ __('Download') }}</a>
                        <a href="{{ route('tenant.channels.qr-codes.print', [$channel, $channel->primaryQrCode]) }}" target="_blank" class="btn btn-sm btn-outline-secondary flex-fill mx-1"><i class="fe fe-printer"></i> {{ __('Print') }}</a>
                    </div>

                    <hr>

                    <form action="{{ route('tenant.channels.qr-codes.store', $channel) }}" method="POST" class="text-left">
                        @csrf
                        <label class="small font-weight-bold">{{ __('Create a custom QR for this channel') }}</label>
                        <div class="input-group">
                            <input type="text" name="label" class="form-control" placeholder="{{ __('e.g. Front Desk Poster') }}">
                            <div class="input-group-append"><button type="submit" class="btn btn-primary text-nowrap"><i class="fe fe-grid"></i> {{ __('Generate') }}</button></div>
                        </div>
                        <p class="text-muted small mt-1 mb-0">{{ __('Creates a new QR (becomes the one shown here). Manage all QR codes for this channel from its detail page.') }}</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
@empty
    <x-valex.empty-state :title="__('No channels yet')" :message="__('Create your first channel now')" icon="inbox">
        <a href="{{ route('tenant.channels.create') }}" class="btn btn-primary mt-3"><i class="fe fe-plus-circle"></i> {{ __('Create First Channel') }}</a>
    </x-valex.empty-state>
@endforelse

@if ($channels->hasPages())
    <div class="d-flex justify-content-center mt-4">{{ $channels->links('pagination::bootstrap-4') }}</div>
@endif
@endsection
