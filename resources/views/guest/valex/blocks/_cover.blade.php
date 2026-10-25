{{-- Background layer for cover heroes: static image, or a carousel when 2+ images (Valex/Bootstrap 4 carousel).
     Must sit inside a position-relative element. Expects $options['cover_paths'] and $options['cover']. --}}
@php
    $paths = $options['cover_paths'] ?? [];
    $cover = $options['cover'];
    $urls = array_map(fn ($p) => \Illuminate\Support\Facades\Storage::url($p), $paths);
    $isRtl = app()->getLocale() === 'ar';
@endphp
@if (count($urls) === 1)
    <div class="position-absolute" style="inset: 0; background: url('{{ $urls[0] }}') center/cover;"></div>
@elseif (count($urls) > 1)
    <div id="coverCarousel" class="carousel slide {{ $cover['effect'] === 'fade' ? 'carousel-fade' : '' }} position-absolute" style="inset: 0;"
         data-ride="carousel" data-interval="{{ $cover['autoplay'] ? $cover['interval'] * 1000 : 'false' }}" data-pause="hover">
        @if ($cover['indicators'])
            <ol class="carousel-indicators" style="top: 10px; bottom: auto; z-index: 3;">
                @foreach ($urls as $i => $u)
                    <li data-target="#coverCarousel" data-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}"></li>
                @endforeach
            </ol>
        @endif
        <div class="carousel-inner" style="height: 100%;">
            @foreach ($urls as $i => $u)
                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}" style="height: 100%; background: url('{{ $u }}') center/cover;"></div>
            @endforeach
        </div>
        @if ($cover['arrows'])
            <a class="carousel-control-prev" href="#coverCarousel" data-slide="prev" style="width: 8%; z-index: 3;" aria-label="{{ __('Previous') }}"><i class="fe fe-chevron-{{ $isRtl ? 'right' : 'left' }} tx-30 text-white"></i></a>
            <a class="carousel-control-next" href="#coverCarousel" data-slide="next" style="width: 8%; z-index: 3;" aria-label="{{ __('Next') }}"><i class="fe fe-chevron-{{ $isRtl ? 'left' : 'right' }} tx-30 text-white"></i></a>
        @endif
    </div>
@endif
