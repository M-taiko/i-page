@extends('layouts.valex')

@section('title', __('Edit Channel'))

@section('page-header')
    <x-valex.page-header :title="__('Edit Channel')" :subtitle="$channel->name" />
@endsection

@section('content')
<div class="row">
    <div class="col-lg-7">
        <x-valex.card :title="__('Channel Information')">
            <form action="{{ route('dashboard.channels.update', [session('current_organization_id'), $channel], $channel) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <x-valex.input name="name" :label="__('Channel Name')" :value="$channel->name" required :placeholder="__('Enter channel name')" />
                <x-valex.select name="type" :label="__('Type')" required :placeholder="__('— Select Type —')" :value="$channel->type"
                    :options="['public' => __('Public'), 'private' => __('Private')]" />
                <x-valex.select name="audience_profile" :label="__('Audience Profile')" required :placeholder="__('— Select Profile —')" :value="$channel->audience_profile"
                    :options="['business' => __('Business'), 'public' => __('Public'), 'private' => __('Private')]" />
                <x-valex.input name="audience_count" type="number" :label="__('Expected Audience Count')" :value="$channel->audience_count" placeholder="1000" />

                <div class="form-group">
                    <label for="logo">{{ __('Channel Logo') }}</label>
                    @if ($channel->logo_path)
                        <div class="mb-2"><img src="{{ Storage::url($channel->logo_path) }}" alt="{{ $channel->name }}" class="rounded" style="width: 80px; height: 80px; object-fit: cover;"></div>
                    @endif
                    <input type="file" id="logo" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/png,image/jpeg,image/svg+xml">
                    <small class="form-text text-muted">{{ __('PNG, JPG, SVG (max 2MB)') }}</small>
                    @error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary">{{ __('Update Channel') }}</button>
                <a href="{{ route('dashboard.channels.show', [session('current_organization_id'), $channel], $channel) }}" class="btn btn-light">{{ __('Cancel') }}</a>
            </form>
        </x-valex.card>
    </div>
</div>
@endsection
