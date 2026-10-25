{{-- Design picker for channel forms (tenant + admin). Optional: $channel. Channels and sub-channels share it. --}}
@php
    $channelDesigns = \App\Support\Designs::channelDesigns();
    $selected = old('design_key', $channel->design_key ?? '');
    $color = old('design_primary_color', $channel->design_options['primary_color'] ?? '');
@endphp
<x-valex.card :title="__('Public Page Design')">
    <p class="text-muted small">{{ __('How visitors and subscribers see this channel. Leave on "Inherit" to use the parent channel\'s design (or the classic page).') }}</p>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="card h-100 mb-0" style="cursor:pointer">
                <div class="card-body">
                    <input type="radio" name="design_key" value="" @checked($selected === '')> <strong>{{ __('Inherit') }}</strong>
                    <p class="small text-muted mb-0 mt-1">{{ __('Parent channel\'s design, otherwise the classic page.') }}</p>
                </div>
            </label>
        </div>
        @foreach ($channelDesigns as $key => $design)
            <div class="col-md-6 mb-3">
                <label class="card h-100 mb-0" style="cursor:pointer; border-top: 4px solid {{ $design['accent'] }}">
                    <div class="card-body">
                        <input type="radio" name="design_key" value="{{ $key }}" @checked($selected === $key)>
                        <i class="fe fe-{{ $design['icon'] }}" style="color: {{ $design['accent'] }}"></i> <strong>{{ __($design['name']) }}</strong>
                        <p class="small text-muted mb-0 mt-1">{{ __($design['description']) }}</p>
                    </div>
                </label>
            </div>
        @endforeach
    </div>
    @error('design_key')<div class="text-danger mb-2">{{ $message }}</div>@enderror

    <div class="form-group mb-0" style="max-width: 220px;">
        <label for="design_primary_color">{{ __('Accent color (optional)') }}</label>
        <input type="color" id="design_primary_color" name="design_primary_color" class="form-control" value="{{ $color ?: '#6259ca' }}">
        <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="design_color_clear" onchange="document.getElementById('design_primary_color').disabled = this.checked" @checked($color === '')>
            <label class="custom-control-label" for="design_color_clear">{{ __('Use the organization\'s color') }}</label>
        </div>
    </div>
</x-valex.card>
@push('scripts')
<script>
    (function () { var cb = document.getElementById('design_color_clear'); if (cb && cb.checked) document.getElementById('design_primary_color').disabled = true; })();
</script>
@endpush
