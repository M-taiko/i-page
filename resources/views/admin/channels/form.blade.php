@extends('layouts.valex')

@section('title', isset($channel) ? __('Edit Channel') : __('New Channel'))

@section('page-header')
    <x-valex.page-header :title="isset($channel) ? __('Edit Channel') : __('New Channel')" :subtitle="$organization->name">
        <x-slot:actions>
            <a href="{{ route('admin.organizations.show', $organization) }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ $organization->name }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <x-valex.card>
            <form method="POST" action="{{ isset($channel)
                    ? route('admin.organizations.channels.update', [$organization, $channel])
                    : route('admin.organizations.channels.store', $organization) }}">
                @csrf
                @if (isset($channel)) @method('PUT') @endif

                <x-valex.select name="brand_id" :label="__('Brand')" required :placeholder="__('Select a brand')"
                    :options="$brands->pluck('name', 'id')->all()" :value="$channel->brand_id ?? null"
                    :help="$brands->isEmpty() ? __('This organization has no active brands yet. Create a brand first.') : null" />

                <x-valex.input name="name" :label="__('Channel Name')" :value="$channel->name ?? ''" required />

                <x-valex.select name="parent_channel_id" :label="__('Parent Channel (optional)')"
                    :placeholder="__('None — this is a top-level channel')"
                    :options="collect($parentOptions ?? [])->pluck('name', 'id')->all()" :value="$currentParentId ?? null"
                    :help="__('Nest this channel under another one — visitors will see it in a popup when they tap the parent channel.')" />

                <x-valex.select name="type" :label="__('Visibility')" required :value="$channel->type ?? 'public'"
                    :options="['public' => __('Public — browsable without login'), 'private' => __('Private — requires login & subscription')]" />

                @include('channels._design-fields')

                <button type="submit" class="btn btn-primary">{{ isset($channel) ? __('Save Changes') : __('Create Channel') }}</button>
                <a href="{{ route('admin.organizations.show', $organization) }}" class="btn btn-light">{{ __('Cancel') }}</a>
            </form>
        </x-valex.card>
    </div>
</div>
@endsection
