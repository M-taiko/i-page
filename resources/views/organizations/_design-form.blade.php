{{-- Design template picker for an existing organization (ADR-012). Expects $organization. --}}
@php
    $industry = $organization->template?->industry_key;
    $available = \App\Support\Designs::forIndustry($industry);
    $current = $organization->design_key;
    $saved = $organization->design_options ?? [];
    $fallbackAccent = $available[$current]['accent'] ?? '#6259ca';
@endphp

@if (! $organization->template)
    <div class="alert alert-info mb-0">{{ __('Design templates are available for organizations created from an organization template.') }}</div>
@else
    <form action="{{ route('organizations.design.update', $organization) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <p class="text-muted">{{ __('Choose how this organization appears to visitors and subscribers.') }}
            <a href="{{ route('guest.organization-detail', $organization) }}" target="_blank">{{ __('Open public page') }} <i class="fe fe-external-link"></i></a></p>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="card h-100 mb-0" style="cursor:pointer">
                    <div class="card-body">
                        <input type="radio" name="design_key" value="" @checked(! $current)> <strong>{{ __('Classic page') }}</strong>
                        <p class="small text-muted mb-0 mt-1">{{ __('The standard i-Page layout.') }}</p>
                    </div>
                </label>
            </div>
            @foreach ($available as $key => $design)
                <div class="col-md-6 mb-3">
                    <label class="card h-100 mb-0" style="cursor:pointer; border-top: 4px solid {{ $design['accent'] }}">
                        <div class="card-body">
                            <input type="radio" name="design_key" value="{{ $key }}" @checked($current === $key)>
                            <i class="fe fe-{{ $design['icon'] }}" style="color: {{ $design['accent'] }}"></i> <strong>{{ __($design['name']) }}</strong>
                            <p class="small text-muted my-1">{{ __($design['description']) }}</p>
                            <a href="{{ route('guest.organization-detail', [$organization, 'preview_design' => $key]) }}" target="_blank" class="small">{{ __('Preview') }} <i class="fe fe-eye"></i></a>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
        @error('design_key')<div class="text-danger mb-3">{{ $message }}</div>@enderror

        <h6 class="text-uppercase text-muted mt-2">{{ __('Appearance') }}</h6>
        <div class="row">
            <div class="col-md-3">
                <x-valex.input name="primary_color" type="color" :label="__('Primary color')" :value="$saved['primary_color'] ?? $fallbackAccent" />
            </div>
            <div class="col-md-3">
                <x-valex.input name="secondary_color" type="color" :label="__('Secondary color')" :value="$saved['secondary_color'] ?? ($saved['primary_color'] ?? $fallbackAccent)" />
            </div>
        </div>

        {{-- Cover images: 2+ images become a carousel (ADR-012). --}}
        @php
            $coverPaths = \App\Support\Designs::coverPaths($saved);
            $cover = \App\Support\Designs::coverSettings($saved);
            $maxCovers = \App\Support\Designs::COVER_MAX_IMAGES;
        @endphp
        <h6 class="text-uppercase text-muted mt-2">{{ __('Cover images') }}</h6>
        <p class="small text-muted">{{ __('Add up to :n images. With two or more images the cover becomes a carousel.', ['n' => $maxCovers]) }}</p>

        <ul class="list-group mb-3" id="coverList">
            @foreach ($coverPaths as $path)
                <li class="list-group-item d-flex align-items-center cover-item">
                    <input type="hidden" name="existing_covers[]" value="{{ $path }}">
                    <img src="{{ Storage::url($path) }}" alt="" class="rounded mx-2" style="height: 52px; width: 88px; object-fit: cover;">
                    <span class="flex-grow-1 small text-muted text-truncate">{{ basename($path) }}</span>
                    <button type="button" class="btn btn-sm btn-light mx-1 cover-up" title="{{ __('Move up') }}"><i class="fe fe-arrow-up"></i></button>
                    <button type="button" class="btn btn-sm btn-light mx-1 cover-down" title="{{ __('Move down') }}"><i class="fe fe-arrow-down"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger mx-1 cover-remove" title="{{ __('Remove') }}"><i class="fe fe-trash-2"></i></button>
                </li>
            @endforeach
        </ul>

        <div class="form-group">
            <label for="covers">{{ __('Add images') }}</label>
            <input type="file" id="covers" name="covers[]" accept="image/*" multiple class="form-control @error('covers') is-invalid @enderror @error('covers.*') is-invalid @enderror">
            @error('covers')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('covers.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            <small class="form-text text-muted">{{ __('JPG, PNG or WebP, up to 4 MB each.') }}</small>
        </div>

        <div id="coverControls" class="border rounded p-3 mb-4">
            <h6 class="mb-3">{{ __('Carousel display') }} <small class="text-muted">({{ __('used when there are 2+ images') }})</small></h6>
            <div class="row">
                <div class="col-md-3">
                    <label class="d-block">{{ __('Transition') }}</label>
                    <select name="cover_effect" class="form-control">
                        <option value="slide" @selected($cover['effect'] === 'slide')>{{ __('Slide') }}</option>
                        <option value="fade" @selected($cover['effect'] === 'fade')>{{ __('Fade') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="d-block">{{ __('Cover height') }}</label>
                    <select name="cover_height" class="form-control">
                        @foreach (['compact' => __('Compact'), 'standard' => __('Standard'), 'tall' => __('Tall')] as $v => $l)
                            <option value="{{ $v }}" @selected($cover['height'] === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="cover_interval" class="d-block">{{ __('Seconds per image') }}</label>
                    <input type="number" id="cover_interval" name="cover_interval" min="2" max="15" value="{{ $cover['interval'] }}" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" id="cover_autoplay" name="cover_autoplay" value="1" @checked($cover['autoplay'])>
                        <label class="custom-control-label" for="cover_autoplay">{{ __('Autoplay') }}</label>
                    </div>
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" id="cover_arrows" name="cover_arrows" value="1" @checked($cover['arrows'])>
                        <label class="custom-control-label" for="cover_arrows">{{ __('Arrows') }}</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="cover_indicators" name="cover_indicators" value="1" @checked($cover['indicators'])>
                        <label class="custom-control-label" for="cover_indicators">{{ __('Dots') }}</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap">
            <div class="custom-control custom-switch mx-3 mb-3">
                <input type="checkbox" class="custom-control-input" id="show_stats" name="show_stats" value="1" @checked($saved['show_stats'] ?? true)>
                <label class="custom-control-label" for="show_stats">{{ __('Show statistics') }}</label>
            </div>
            <div class="custom-control custom-switch mx-3 mb-3">
                <input type="checkbox" class="custom-control-input" id="show_posts" name="show_posts" value="1" @checked($saved['show_posts'] ?? true)>
                <label class="custom-control-label" for="show_posts">{{ __('Show latest posts') }}</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fe fe-check"></i> {{ __('Save design') }}</button>
    </form>

    @push('scripts')
    <script>
        // Reorder / remove existing cover images; the submitted order of existing_covers[] is the display order.
        jQuery(function ($) {
            var $list = $('#coverList');
            $list.on('click', '.cover-up', function () { var $i = $(this).closest('.cover-item'); $i.prev('.cover-item').before($i); });
            $list.on('click', '.cover-down', function () { var $i = $(this).closest('.cover-item'); $i.next('.cover-item').after($i); });
            $list.on('click', '.cover-remove', function () { $(this).closest('.cover-item').remove(); });
        });
    </script>
    @endpush
@endif
