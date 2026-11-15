{{-- Where the post appears and whether it is a highlight (ADR-014). Expects $channel; optional $post when editing. --}}
@php
    $post = $post ?? null;
    $children = $channel->childChannels()->pluck('name');
    $pinned = old('pinned', $post?->isPinned() ? 1 : 0);
    $pinUntil = old('pinned_until', $post && $post->isPinned() && $post->pinned_until->lt(now()->addYears(5)) ? $post->pinned_until->format('Y-m-d\TH:i') : '');
    $pinThumb = $post?->option('pin_thumb');
@endphp
<x-valex.card :title="__('Publishing')">
    {{-- Cascade to sub-channels --}}
    <div class="d-flex align-items-start p-3 rounded bg-primary-transparent mb-3">
        <div class="custom-control custom-switch mt-1">
            <input type="hidden" name="cascade" value="0">
            <input type="checkbox" class="custom-control-input" id="cascade" name="cascade" value="1" @checked(old('cascade', $post?->cascade ?? true))>
            <label class="custom-control-label" for="cascade"><strong>{{ __('Show in all sub-channels') }}</strong></label>
        </div>
    </div>
    <p class="small text-muted mt-n2 mb-4">
        @if ($children->isNotEmpty())
            {{ __('Also appears in: :list (and their sub-channels).', ['list' => $children->implode('، ')]) }}
        @else
            {{ __('This channel has no sub-channels yet; the post will reach new ones automatically.') }}
        @endif
        @if ($channel->type === 'private')
            {{ __('Public sub-channels never show posts from a private channel.') }}
        @endif
    </p>

    {{-- Highlight --}}
    <div class="custom-control custom-switch mb-2">
        <input type="hidden" name="pinned" value="0">
        <input type="checkbox" class="custom-control-input" id="pinned" name="pinned" value="1" @checked($pinned)>
        <label class="custom-control-label" for="pinned"><strong>📌 {{ __('Pin as a highlight') }}</strong> <span class="text-muted small">— {{ __('shown at the top of the channel with a thumbnail') }}</span></label>
    </div>

    <div id="pinFields" class="border rounded p-3 mt-2" style="{{ $pinned ? '' : 'display:none' }}">
        @php $scope = old('pin_scope', $post?->pinScope() ?? 'all'); @endphp
        <label class="d-block font-weight-bold">{{ __('Where should it be pinned?') }}</label>
        <div class="row mb-3">
            <div class="col-md-6 mb-2">
                <label class="card h-100 mb-0 p-3" style="cursor:pointer">
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="pin_scope_channel" name="pin_scope" value="channel" @checked($scope === 'channel')>
                        <label class="custom-control-label" for="pin_scope_channel"><strong>{{ __('This channel only') }}</strong></label>
                    </div>
                    <small class="text-muted mt-1">{{ __('The highlight appears only at the top of :name.', ['name' => $channel->name]) }}</small>
                </label>
            </div>
            <div class="col-md-6 mb-2">
                <label class="card h-100 mb-0 p-3" style="cursor:pointer">
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="pin_scope_all" name="pin_scope" value="all" @checked($scope === 'all')>
                        <label class="custom-control-label" for="pin_scope_all"><strong>{{ __('This channel and all sub-channels') }}</strong></label>
                    </div>
                    <small class="text-muted mt-1">{{ __('The highlight appears at the top of every sub-channel too, at any depth.') }}</small>
                </label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <x-valex.input name="pinned_until" type="datetime-local" :label="__('Highlight until (optional)')" :value="$pinUntil" :help="__('Leave empty to keep it until you unpin it.')" />
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="pin_thumb">{{ __('Highlight thumbnail (optional)') }}</label>
                    <input type="file" id="pin_thumb" name="pin_thumb" accept="image/*" class="form-control @error('pin_thumb') is-invalid @enderror">
                    <small class="form-text text-muted">{{ __('Portrait images look best. Without one, the post\'s first image is used.') }}</small>
                    @error('pin_thumb')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @if ($pinThumb)
                        <div class="d-flex align-items-center mt-2">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($pinThumb) }}" class="rounded mx-2" style="width: 48px; height: 64px; object-fit: cover;" alt="">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="remove_pin_thumb" name="remove_pin_thumb" value="1">
                                <label class="custom-control-label" for="remove_pin_thumb">{{ __('Remove') }}</label>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <x-valex.textarea name="summary" rows="2" maxlength="500" :value="$post?->summary" :label="__('Highlight summary')"
            :help="__('The short text people see when they tap the highlight. Defaults to the start of the post.')" />
    </div>
</x-valex.card>

@push('scripts')
<script>
    jQuery(function ($) {
        $('#pinned').on('change', function () { $('#pinFields').toggle(this.checked); });
    });
</script>
@endpush
