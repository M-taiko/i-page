@extends('layouts.valex')

@section('title', __('Create Post') . ' - ' . $channel->name)

@section('page-header')
    <x-valex.page-header :title="__('Create Post')" :subtitle="__('Add a new post to :name', ['name' => $channel->name])">
        <x-slot:actions>
            <a href="{{ route('tenant.channels.show', $channel->id) }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ $channel->name }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $formats = config('posts.formats');
    $styles = config('posts.card_styles');
    $badges = config('posts.badges');
    $reactions = config('posts.reactions');
    $currentFormat = old('post_format', 'standard');
    $oldReactions = old('reactions', ['like']);
    $oldPoll = old('poll_options', ['', '']);
@endphp
<div class="row justify-content-center">
    <div class="col-lg-9">
        <form method="POST" action="{{ route('tenant.channels.posts.store', $channel->id) }}" enctype="multipart/form-data" id="postForm">
            @csrf

            {{-- 1. Format --}}
            <x-valex.card :title="__('Post type')">
                <div class="row">
                    @foreach ($formats as $key => $f)
                        <div class="col-6 col-md-4 col-xl mb-2">
                            <label class="card h-100 mb-0 text-center" style="cursor:pointer">
                                <div class="card-body py-3 px-2">
                                    <input type="radio" name="post_format" value="{{ $key }}" class="d-none format-radio" @checked($currentFormat === $key)>
                                    <i class="fe fe-{{ $f['icon'] }} tx-24 d-block mb-1"></i>
                                    <strong class="d-block">{{ __($f['name']) }}</strong>
                                    <small class="text-muted">{{ __($f['description']) }}</small>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('post_format')<div class="text-danger small">{{ $message }}</div>@enderror
            </x-valex.card>

            {{-- 2. Content --}}
            <x-valex.card :title="__('Content')">
                <div class="d-flex align-items-center mb-3 p-3 bg-primary-transparent rounded">
                    <span class="avatar avatar-md brround bg-primary mx-2"><i class="fe fe-message-circle"></i></span>
                    <div><strong>{{ $channel->name }}</strong><div class="small text-muted">{{ $channel->description ?? __('No description') }}</div></div>
                </div>

                <x-valex.input name="title" :label="__('Title')" maxlength="150" :placeholder="__('Optional headline')" />
                <x-valex.textarea name="body" rows="6" required maxlength="5000" id="bodyField"
                    :label="__('Post Content')" :placeholder="__('Write your post content here (minimum 10 characters)...')" />
                <div class="small text-muted mb-3"><span id="charCount">0</span> / 5000 {{ __('characters') }}</div>

                {{-- Standard: single image --}}
                <div class="form-group fmt fmt-standard">
                    <label for="image">{{ __('Post Image (Optional)') }}</label>
                    <input type="file" id="image" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
                    <small class="form-text text-muted">{{ __('PNG, JPG, GIF or WebP up to 2MB') }}</small>
                    @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Gallery --}}
                <div class="form-group fmt fmt-gallery" style="display:none">
                    <label for="gallery">{{ __('Photos') }} <span class="tx-danger">*</span></label>
                    <input type="file" id="gallery" name="gallery[]" accept="image/*" multiple class="form-control @error('gallery') is-invalid @enderror @error('gallery.*') is-invalid @enderror">
                    <small class="form-text text-muted">{{ __('Up to :n photos, 4MB each. Two or more photos are shown as a carousel.', ['n' => config('posts.limits.gallery_images')]) }}</small>
                    @error('gallery')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('gallery.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Poll --}}
                <div class="fmt fmt-poll" style="display:none">
                    <label>{{ __('Poll options') }} <span class="tx-danger">*</span></label>
                    <div id="pollOptions">
                        @foreach ($oldPoll as $opt)
                            <div class="input-group mb-2 poll-row">
                                <input type="text" name="poll_options[]" class="form-control" maxlength="150" value="{{ $opt }}" placeholder="{{ __('Option') }}">
                                <div class="input-group-append"><button type="button" class="btn btn-outline-danger poll-remove" title="{{ __('Remove') }}"><i class="fe fe-x"></i></button></div>
                            </div>
                        @endforeach
                    </div>
                    @error('poll_options')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addPollOption"><i class="fe fe-plus"></i> {{ __('Add option') }}</button>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="custom-control custom-switch mt-4">
                                <input type="checkbox" class="custom-control-input" id="poll_multiple" name="poll_multiple" value="1" @checked(old('poll_multiple'))>
                                <label class="custom-control-label" for="poll_multiple">{{ __('Allow multiple answers') }}</label>
                            </div>
                        </div>
                        <div class="col-md-4"><x-valex.input name="poll_ends_at" type="datetime-local" :label="__('Ends at (optional)')" /></div>
                        <div class="col-md-4">
                            <x-valex.select name="poll_show_results" :label="__('Show results')" :value="'always'"
                                :options="['always' => __('Always'), 'after_vote' => __('After voting')]" />
                        </div>
                    </div>
                </div>

                {{-- Event --}}
                <div class="fmt fmt-event" style="display:none">
                    <div class="row">
                        <div class="col-md-6"><x-valex.input name="event_starts_at" type="datetime-local" :label="__('Starts at')" /></div>
                        <div class="col-md-6"><x-valex.input name="event_ends_at" type="datetime-local" :label="__('Ends at (optional)')" /></div>
                        <div class="col-md-8"><x-valex.input name="event_location" :label="__('Location')" maxlength="200" /></div>
                        <div class="col-md-4"><x-valex.input name="event_capacity" type="number" min="1" :label="__('Capacity (optional)')" /></div>
                    </div>
                </div>

                {{-- Rating --}}
                <div class="fmt fmt-rating alert alert-info mb-0" style="display:none">
                    <i class="fe fe-star"></i> {{ __('People will rate this post from 1 to 5 stars. Use the content above to say what they are rating.') }}
                </div>
            </x-valex.card>

            {{-- 3. Presentation --}}
            <x-valex.card :title="__('Appearance & priority')">
                <div class="form-group">
                    <label>{{ __('Card design') }}</label>
                    <div class="row">
                        @foreach ($styles as $key => $s)
                            <div class="col-6 col-md-3 mb-2">
                                <label class="card h-100 mb-0" style="cursor:pointer">
                                    <div class="card-body py-3">
                                        <input type="radio" name="card_style" value="{{ $key }}" @checked(old('card_style', 'classic') === $key)>
                                        <strong>{{ __($s['name']) }}</strong>
                                        <div class="small text-muted">{{ __($s['description']) }}</div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <x-valex.select name="priority" :label="__('Priority')" :value="'medium'"
                            :options="collect(config('posts.priorities'))->map(fn ($l) => __($l))->all()" />
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="badge_key">{{ __('Badge') }}</label>
                            <select name="badge_key" id="badge_key" class="form-control">
                                <option value="">{{ __('No badge') }}</option>
                                @foreach ($badges as $key => [$label, $color])
                                    <option value="{{ $key }}" data-color="{{ $color }}" @selected(old('badge_key') === $key)>{{ __($label) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4"><x-valex.input name="badge_label" :label="__('Custom badge text (optional)')" maxlength="40" /></div>
                </div>

                <div class="form-group mb-0">
                    <label>{{ __('Reactions people can use') }}</label>
                    <div class="d-flex flex-wrap">
                        @foreach ($reactions as $key => [$label, $emoji])
                            <div class="custom-control custom-checkbox mx-3">
                                <input type="checkbox" class="custom-control-input" id="react_{{ $key }}" name="reactions[]" value="{{ $key }}" @checked(in_array($key, $oldReactions, true))>
                                <label class="custom-control-label" for="react_{{ $key }}">{{ $emoji }} {{ __($label) }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-valex.card>

            <button type="submit" class="btn btn-primary"><i class="fe fe-check-circle"></i> {{ __('Publish Post') }}</button>
            <a href="{{ route('tenant.channels.show', $channel->id) }}" class="btn btn-light">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    jQuery(function ($) {
        var maxOptions = {{ config('posts.limits.poll_options_max') }};

        function showFormat() {
            var f = $('input[name="post_format"]:checked').val() || 'standard';
            $('.fmt').hide(); $('.fmt-' + f).show();
            $('.format-radio').each(function () { $(this).closest('.card').toggleClass('border-primary', this.checked); });
            $('#bodyField').attr('placeholder', f === 'poll' ? @json(__('Ask your question...')) : f === 'event' ? @json(__('Describe the event...')) : f === 'rating' ? @json(__('What are people rating?')) : @json(__('Write your post content here (minimum 10 characters)...')));
        }
        $('.format-radio').on('change', showFormat);
        showFormat();

        $('#bodyField').on('input', function () { $('#charCount').text(this.value.length); }).trigger('input');

        $('#addPollOption').on('click', function () {
            if ($('#pollOptions .poll-row').length >= maxOptions) return;
            $('#pollOptions').append('<div class="input-group mb-2 poll-row"><input type="text" name="poll_options[]" class="form-control" maxlength="150" placeholder="' + @json(__('Option')) + '"><div class="input-group-append"><button type="button" class="btn btn-outline-danger poll-remove"><i class="fe fe-x"></i></button></div></div>');
        });
        $('#pollOptions').on('click', '.poll-remove', function () {
            if ($('#pollOptions .poll-row').length > 2) $(this).closest('.poll-row').remove();
        });
    });
</script>
@endpush
