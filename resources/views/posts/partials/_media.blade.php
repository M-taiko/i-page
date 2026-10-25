{{-- Post images: gallery carousel (2+ images), single image, or the legacy image_path. Expects $post. --}}
@php
    $post->loadMissing('media');
    $gallery = $post->galleryImages();
    $urls = $gallery->map(fn ($m) => \Illuminate\Support\Facades\Storage::disk($m->disk)->url($m->path))->all();
    if (! $urls && $post->image_path) {
        $urls = [\Illuminate\Support\Facades\Storage::url($post->image_path)];
    }
@endphp
@if (count($urls) === 1)
    <div class="pf-media"><img class="pf-single" src="{{ $urls[0] }}" alt=""></div>
@elseif (count($urls) > 1)
    <div class="pf-media">
        <div class="pf-carousel" data-autoplay="{{ $post->option('gallery.autoplay', true) ? 1 : 0 }}" data-interval="{{ $post->option('gallery.interval', 4) }}">
            <div class="pf-track">
                @foreach ($urls as $u)<div class="pf-slide"><img src="{{ $u }}" alt="" loading="lazy"></div>@endforeach
            </div>
            <button type="button" class="pf-nav pf-prev" aria-label="{{ __('Previous') }}">&#8249;</button>
            <button type="button" class="pf-nav pf-next" aria-label="{{ __('Next') }}">&#8250;</button>
            <div class="pf-dots">@foreach ($urls as $i => $u)<span class="{{ $i === 0 ? 'on' : '' }}"></span>@endforeach</div>
        </div>
    </div>
@endif
