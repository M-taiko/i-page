{{-- Where the post appears and whether it is a highlight (ADR-014/016).
     Channel posts: expects $channel. Tenant posts: expects $tenant (Organization) and $channelTree (placement picker instead of cascade). Optional $post when editing. --}}
@php
    $post = $post ?? null;
    $tenant = $tenant ?? null;
    $children = $tenant ? collect() : $channel->childChannels()->pluck('name');
    $pinned = old('pinned', $post?->isPinned() ? 1 : 0);
    $pinUntil = old('pinned_until', $post && $post->isPinned() && $post->pinned_until->lt(now()->addYears(5)) ? $post->pinned_until->format('Y-m-d\TH:i') : '');
    $pinThumb = $post?->option('pin_thumb');
@endphp
<x-valex.card :title="__('Publishing')">
    @if ($tenant)
    @php
        $placement = old('placement', $post?->placement ?? 'selected');
        $chosen = collect(old('channel_ids', $post ? $post->targetChannels->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all();
        $placements = [
            'page' => ['home', __('Tenant page only'), __('Shown on the public page of :name.', ['name' => $tenant->name])],
            'all' => ['globe', __('Every channel'), __('Every channel and sub-channel of :name.', ['name' => $tenant->name])],
            'selected' => ['check-square', __('Choose channels'), __('Pick any channels or sub-channels below.')],
        ];
    @endphp
    <label class="d-block font-weight-bold">{{ __('Where does this post appear?') }}</label>
    <div class="row mb-2">
        @foreach ($placements as $key => [$icon, $label, $hint])
            <div class="col-md-4 mb-2">
                <label class="card h-100 mb-0 p-3" style="cursor:pointer">
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input js-placement" id="placement_{{ $key }}" name="placement" value="{{ $key }}" @checked($placement === $key)>
                        <label class="custom-control-label" for="placement_{{ $key }}"><i class="fe fe-{{ $icon }}"></i> <strong>{{ $label }}</strong></label>
                    </div>
                    <small class="text-muted mt-1">{{ $hint }}</small>
                </label>
            </div>
        @endforeach
    </div>
    @error('placement')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

    <div id="targetPicker" class="border rounded p-3 mb-3" style="{{ $placement === 'selected' ? '' : 'display:none' }}">
        <div class="d-flex flex-wrap align-items-center mb-2" style="gap: 8px;">
            <strong class="flex-grow-1">{{ __('Channels') }}</strong>
            <button type="button" class="btn btn-sm btn-light js-target-all">{{ __('Select all') }}</button>
            <button type="button" class="btn btn-sm btn-light js-target-none">{{ __('Clear') }}</button>
        </div>
        @if ($channelTree)
            <ul class="list-unstyled mb-0 target-tree">
                @foreach ($channelTree as $node)
                    @include('organizations.hub._target-node', ['node' => $node, 'chosen' => $chosen])
                @endforeach
            </ul>
        @else
            <p class="text-muted small mb-0">{{ __('No channels yet.') }}</p>
        @endif
        @error('channel_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
        <div class="custom-control custom-switch mt-3">
            <input type="hidden" name="cascade" value="0">
            <input type="checkbox" class="custom-control-input" id="cascade" name="cascade" value="1" @checked(old('cascade', $post?->cascade ?? false))>
            <label class="custom-control-label" for="cascade"><strong>{{ __('Also show in their sub-channels') }}</strong> <span class="text-muted small">— {{ __('public sub-channels never show posts placed in a private channel') }}</span></label>
        </div>
    </div>

    <div id="showOnPageBox" class="custom-control custom-switch mb-2" style="{{ $placement === 'page' ? 'display:none' : '' }}">
        <input type="hidden" name="show_on_page" value="0">
        <input type="checkbox" class="custom-control-input" id="show_on_page" name="show_on_page" value="1" @checked(old('show_on_page', $post?->show_on_page ?? true))>
        <label class="custom-control-label" for="show_on_page"><strong>{{ __('Also show on the public tenant page') }}</strong> <span class="text-muted small">— {{ __('turn off for staff-only posts') }}</span></label>
    </div>
    <p class="small text-muted mb-4">{{ __('Channel admins, moderators and every subscriber of the receiving channels are notified.') }}</p>
    @push('scripts')
    <script>
        jQuery(function ($) {
            $('.js-placement').on('change', function () {
                $('#targetPicker').toggle(this.value === 'selected');
                $('#showOnPageBox').toggle(this.value !== 'page');
            });
            $('.js-target-all').on('click', function () { $('#targetPicker input[name="channel_ids[]"]').prop('checked', true); });
            $('.js-target-none').on('click', function () { $('#targetPicker input[name="channel_ids[]"]').prop('checked', false); });
            // Ticking a channel ticks its sub-channels too (they can be unticked one by one).
            $('#targetPicker').on('change', 'input[name="channel_ids[]"]', function () {
                $(this).closest('li').find('ul input[name="channel_ids[]"]').prop('checked', this.checked);
            });
        });
    </script>
    @endpush
    @push('styles')
    <style>
        .target-tree ul { list-style: none; padding-inline-start: 22px; margin: 0; border-inline-start: 2px dashed rgba(100,116,139,.25); margin-inline-start: 9px; }
        .target-tree .custom-control { padding-top: 4px; padding-bottom: 4px; }
    </style>
    @endpush
    @else
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
    @endif

    {{-- Highlight --}}
    <div class="custom-control custom-switch mb-2">
        <input type="hidden" name="pinned" value="0">
        <input type="checkbox" class="custom-control-input" id="pinned" name="pinned" value="1" @checked($pinned)>
        <label class="custom-control-label" for="pinned"><strong>📌 {{ __('Pin as a highlight') }}</strong> <span class="text-muted small">— {{ __('shown at the top of the channel with a thumbnail') }}</span></label>
    </div>

    <div id="pinFields" class="border rounded p-3 mt-2" style="{{ $pinned ? '' : 'display:none' }}">
        @if ($tenant)
        <p class="small text-muted">{{ __('A tenant highlight appears at the top of the tenant page and of every channel that receives the post.') }}</p>
        @else
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
        @endif
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
