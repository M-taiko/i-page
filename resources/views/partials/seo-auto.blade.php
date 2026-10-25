{{-- Share card for pages that use the classic shell or the Valex public layout.
     Pages may set: @section('title'), @section('meta_description'), @section('meta_image') (absolute URL).
     Everything else falls back to the site defaults; non-public pages are marked noindex. --}}
@php
    $autoLang = app()->getLocale() === 'ar' ? 'ar' : 'en';
    $autoDefaults = config("landing.$autoLang.seo");
    $autoTitle = trim(strip_tags($__env->yieldContent('title'))) ?: 'i-Page';
    $autoDesc = trim(strip_tags($__env->yieldContent('meta_description'))) ?: $autoDefaults['description'];
    $autoImage = trim($__env->yieldContent('meta_image')) ?: asset('images/og/ipage-share.jpg');
    $isPublicPage = request()->is('browse', 'browse/*');
    $seo = [
        'no_title' => true,
        'title' => $autoTitle,
        'description' => \Illuminate\Support\Str::limit($autoDesc, 200),
        'url' => url()->current() . (request()->query('page') ? '?page=' . (int) request()->query('page') : ''),
        'image' => $autoImage,
        'image_alt' => $autoTitle,
        'locale' => $autoDefaults['locale'],
        'robots' => $isPublicPage && ! auth()->check() ? 'index,follow,max-image-preview:large' : 'noindex,follow',
    ];
@endphp
@include('partials.seo')
