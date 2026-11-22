{{-- Share + SEO metadata (docs/27 "Sharing & SEO"). Include inside <head>.
     Expects $seo = [title, description, url, image, image_alt, locale, type?, robots?, alternates?, schema?,
     published_time?, modified_time?, image_width?, image_height?, no_title?].
     schema may be one JSON-LD object or a list of them. --}}
@php
    $seo = $seo ?? [];
    $seoTitle = $seo['title'] ?? config('app.name', 'i-Page');
    $seoDesc = $seo['description'] ?? '';
    $seoUrl = $seo['url'] ?? url()->current();
    $seoDefaultImage = asset('images/og/ipage-share-v3.jpg');
    $seoImage = $seo['image'] ?? $seoDefaultImage;
    $seoLocale = $seo['locale'] ?? 'en_US';
    $seoType = $seo['type'] ?? 'website';
    $seoExt = strtolower(pathinfo(parse_url($seoImage, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
    $seoMime = ['png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'][$seoExt] ?? 'image/jpeg';
    $seoIsDefault = $seoImage === $seoDefaultImage;

    // Every page says who publishes it, with the logo (Google uses it for the knowledge panel and results).
    $seoPublisher = [
        '@type' => 'Organization',
        '@id' => rtrim(url('/'), '/') . '/#organization',
        'name' => 'i-page',
        'url' => rtrim(url('/'), '/') . '/',
        'logo' => ['@type' => 'ImageObject', 'url' => asset('images/pwa/icon-512.png'), 'width' => 512, 'height' => 512],
        'slogan' => 'The Digital Front Door for Every Organization',
    ];
    $seoRaw = $seo['schema'] ?? [];
    $seoSchemas = isset($seoRaw['@graph']) ? $seoRaw['@graph'] : (isset($seoRaw['@type']) ? [$seoRaw] : array_values($seoRaw));
    $seoSchemas = array_values(array_filter(array_map(function ($item) { unset($item['@context']); return $item; }, $seoSchemas),
        fn ($item) => is_array($item) && ($item['@id'] ?? null) !== $seoPublisher['@id']));
    array_unshift($seoSchemas, $seoPublisher);
@endphp
@empty($seo['no_title'])
<title>{{ $seoTitle }}</title>
@endempty
<meta name="description" content="{{ $seoDesc }}">
<meta name="robots" content="{{ $seo['robots'] ?? 'index,follow,max-image-preview:large,max-snippet:-1' }}">
<link rel="canonical" href="{{ $seoUrl }}">
@foreach (($seo['alternates'] ?? []) as $hreflang => $href)
    <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $href }}">
@endforeach

{{-- Open Graph (WhatsApp, Facebook, Telegram, LinkedIn) --}}
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:site_name" content="i-page">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDesc }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:locale" content="{{ $seoLocale }}">
<meta property="og:locale:alternate" content="{{ $seoLocale === 'ar_AR' ? 'en_US' : 'ar_AR' }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:secure_url" content="{{ str_replace('http://', 'https://', $seoImage) }}">
<meta property="og:image:type" content="{{ $seoMime }}">
@if ($seoIsDefault || isset($seo['image_width']))
<meta property="og:image:width" content="{{ $seo['image_width'] ?? 1200 }}">
<meta property="og:image:height" content="{{ $seo['image_height'] ?? 630 }}">
@endif
<meta property="og:image:alt" content="{{ $seo['image_alt'] ?? $seoTitle }}">
@if ($seoType === 'article')
    @if (! empty($seo['published_time']))<meta property="article:published_time" content="{{ $seo['published_time'] }}">@endif
    @if (! empty($seo['modified_time']))<meta property="article:modified_time" content="{{ $seo['modified_time'] }}">@endif
@endif

{{-- Twitter / X --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDesc }}">
<meta name="twitter:image" content="{{ $seoImage }}">
<meta name="twitter:image:alt" content="{{ $seo['image_alt'] ?? $seoTitle }}">

<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@graph' => $seoSchemas], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
