@extends('layouts.app-modern')

@section('content')
<div class="container-lg py-4" style="max-width: 640px;">
    <div class="mb-4">
        <a href="{{ route('admin.organizations.show', $organization) }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left"></i> {{ $organization->name }}
        </a>
        <h1 class="h3 mt-2">{{ isset($channel) ? __('Edit Channel') : __('New Channel') }}</h1>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST"
                  action="{{ isset($channel)
                        ? route('admin.organizations.channels.update', [$organization, $channel])
                        : route('admin.organizations.channels.store', $organization) }}">
                @csrf
                @if (isset($channel))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="brand_id" class="form-label">{{ __('Brand') }}</label>
                    <select name="brand_id" id="brand_id" class="form-select" required>
                        <option value="">{{ __('Select a brand') }}</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}"
                                @selected(old('brand_id', $channel->brand_id ?? null) == $brand->id)>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                    @if ($brands->isEmpty())
                        <small class="text-danger">{{ __('This organization has no active brands yet. Create a brand first.') }}</small>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('Channel Name') }}</label>
                    <input type="text" name="name" id="name" class="form-control"
                           value="{{ old('name', $channel->name ?? '') }}" required>
                </div>

                <div class="mb-4">
                    <label for="type" class="form-label">{{ __('Visibility') }}</label>
                    <select name="type" id="type" class="form-select" required>
                        <option value="public" @selected(old('type', $channel->type ?? 'public') === 'public')>
                            {{ __('Public — browsable without login') }}
                        </option>
                        <option value="private" @selected(old('type', $channel->type ?? '') === 'private')>
                            {{ __('Private — requires login & subscription') }}
                        </option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        {{ isset($channel) ? __('Save Changes') : __('Create Channel') }}
                    </button>
                    <a href="{{ route('admin.organizations.show', $organization) }}" class="btn btn-outline-secondary">
                        {{ __('Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
