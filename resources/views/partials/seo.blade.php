{{-- Share + SEO metadata (docs/27 "Sharing & SEO"). Include inside <head>.
     Expects $seo = [title, description, url, image, image_alt, locale, type?, robots?, alternates?, schema?]. --}}
@php
    $seo = $seo ?? [];
    $seoTitle = $seo['title'] ?? config('app.name', 'i-Page');
    $seoDesc = $seo['description'] ?? '';
    $seoUrl = $seo['url'] ?? url()->current();
    $seoImage = $seo['image'] ?? asset('images/og/ipage-share.jpg');
    $seoLocale = $seo['locale'] ?? 'en_US';
@endphp
@empty($seo['no_title'])
<title>{{ $seoTitle }}</title>
@endempty
<meta name="description" content="{{ $seoDesc }}">
<meta name="robots" content="{{ $seo['robots'] ?? 'index,follow,max-image-preview:large' }}">
<link rel="canonical" href="{{ $seoUrl }}">
@foreach (($seo['alternates'] ?? []) as $hreflang => $href)
    <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $href }}">
@endforeach

{{-- Open Graph (WhatsApp, Facebook, Telegram, LinkedIn) --}}
<meta property="og:type" content="{{ $seo['type'] ?? 'website' }}">
<meta property="og:site_name" content="i-Page">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDesc }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:locale" content="{{ $seoLocale }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:secure_url" content="{{ str_replace('http://', 'https://', $seoImage) }}">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="{{ $seo['image_width'] ?? 1200 }}">
<meta property="og:image:height" content="{{ $seo['image_height'] ?? 630 }}">
<meta property="og:image:alt" content="{{ $seo['image_alt'] ?? $seoTitle }}">

{{-- Twitter / X --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDesc }}">
<meta name="twitter:image" content="{{ $seoImage }}">
<meta name="twitter:image:alt" content="{{ $seo['image_alt'] ?? $seoTitle }}">

@if (! empty($seo['schema']))
    <script type="application/ld+json">{!! json_encode($seo['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endif
