{{-- Shared by tenant/channels/create and edit. Expects $brands, $parentOptions, $selectedParentId, optional $channel. --}}
<form action="{{ isset($channel) ? route('tenant.channels.update', $channel->id) : route('tenant.channels.store') }}" method="POST">
    @csrf
    @if (isset($channel)) @method('PUT') @endif

    <div style="max-width: 700px;">
        <x-valex.card :title="__('Channel Information')">
            <x-valex.select name="brand_id" :label="__('Brand')" required :placeholder="__('Select a brand')"
                :options="$brands->pluck('name', 'id')->all()" :value="$channel->brand_id ?? null"
                :help="__('Channels belong to a brand within your organization')" />
            @if (! isset($channel) && $brands->isEmpty())
                <div class="text-danger small mb-3"><i class="fe fe-alert-circle"></i> {{ __('No active brands yet — create a brand in Settings first.') }}</div>
            @endif

            <x-valex.input name="name" :label="__('Channel Name')" :value="$channel->name ?? ''" required autocomplete="off"
                :placeholder="__('Example: Reception, Support, Announcements')"
                :help="__('The name that will appear for all members of this channel')" />

            <x-valex.select name="parent_channel_id" :label="__('Parent Channel (optional)')"
                :placeholder="__('None — this is a top-level channel')"
                :options="collect($parentOptions)->pluck('name', 'id')->all()" :value="$selectedParentId ?? null"
                :help="__('Make this a sub-channel nested under another channel. Visitors will see it inside a popup when they tap the parent channel.')" />
        </x-valex.card>

        <x-valex.card :title="__('Channel Type')">
            @php $currentType = old('type', $channel->type ?? 'public'); @endphp
            <div class="custom-control custom-radio mb-3">
                <input class="custom-control-input" type="radio" name="type" id="type-public" value="public" @checked($currentType === 'public')>
                <label class="custom-control-label" for="type-public">
                    <strong><i class="fe fe-globe"></i> {{ __('Public Channel') }}</strong>
                    <span class="d-block small text-muted">{{ __('Anyone in your organization can join and see all posts') }}</span>
                </label>
            </div>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" name="type" id="type-private" value="private" @checked($currentType === 'private')>
                <label class="custom-control-label" for="type-private">
                    <strong><i class="fe fe-lock"></i> {{ __('Private Channel') }}</strong>
                    <span class="d-block small text-muted">{{ __('Only invited members can join and see posts') }}</span>
                </label>
            </div>
            @error('type')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
        </x-valex.card>

        @include('channels._design-fields')

        <div class="alert alert-info">
            <strong><i class="fe fe-info"></i> {{ __('Tip') }}</strong>
            {{ __('Use public channels for organization-wide announcements and private channels for specific teams or departments.') }}
        </div>

        <button type="submit" class="btn btn-primary"><i class="fe fe-check-circle"></i> {{ isset($channel) ? __('Save Changes') : __('Create Channel') }}</button>
        <a href="{{ isset($channel) ? route('tenant.channels.show', $channel->id) : route('tenant.channels.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
    </div>
</form>
