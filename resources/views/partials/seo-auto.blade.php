{{-- Share card + SEO for pages on the classic shell or the Valex public layout (docs/27 "Sharing & SEO").
     Pages may set: @section('title'), @section('meta_description'), @section('meta_image') (absolute URL),
     @section('meta_type') (article), @section('meta_published') / @section('meta_modified') (ISO 8601),
     @section('seo_schema') (JSON-LD object or list, as JSON).
     Everything else falls back to the site defaults; non-public and search-result pages are noindex. --}}
@php
    $autoLang = app()->getLocale() === 'ar' ? 'ar' : 'en';
    $autoDefaults = config("landing.$autoLang.seo");
    $autoTitle = trim(html_entity_decode(strip_tags($__env->yieldContent('title')), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?: 'i-page';
    $autoDesc = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($__env->yieldContent('meta_description')), ENT_QUOTES | ENT_HTML5, 'UTF-8'))) ?: $autoDefaults['description'];
    $autoImage = html_entity_decode(trim($__env->yieldContent('meta_image'))) ?: asset('images/og/ipage-share-v3.jpg');
    // @section(name, value) escapes its value, so decode entities before reading the JSON.
    $autoSchema = json_decode(html_entity_decode(trim($__env->yieldContent('seo_schema')) ?: '[]', ENT_QUOTES | ENT_HTML5, 'UTF-8'), true) ?: [];

    $isPublicPage = request()->is('browse', 'browse/*');
    // Search results, filtered or personalised views are not worth indexing.
    $isVariant = request()->filled('q') || request()->filled('country') || request()->filled('tab') || request()->filled('partial');
    $home = rtrim(url('/'), '/') . '/';

    if (request()->is('browse')) {
        // Lets Google show a search box for i-page in its results.
        $autoSchema[] = [
            '@type' => 'WebSite', '@id' => $home . '#website', 'url' => $home, 'name' => 'i-page',
            'publisher' => ['@id' => $home . '#organization'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => ['@type' => 'EntryPoint', 'urlTemplate' => route('guest.search-organizations') . '?q={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    $seo = [
        'no_title' => true,
        'title' => $autoTitle,
        'description' => \Illuminate\Support\Str::limit($autoDesc, 200),
        'url' => url()->current() . (request()->query('page') > 1 ? '?page=' . (int) request()->query('page') : ''),
        'image' => $autoImage,
        'image_alt' => $autoTitle,
        'locale' => $autoDefaults['locale'],
        'type' => trim($__env->yieldContent('meta_type')) ?: 'website',
        'published_time' => html_entity_decode(trim($__env->yieldContent('meta_published'))) ?: null,
        'modified_time' => html_entity_decode(trim($__env->yieldContent('meta_modified'))) ?: null,
        'schema' => $autoSchema,
        'robots' => $isPublicPage && ! auth()->check() && ! $isVariant ? 'index,follow,max-image-preview:large,max-snippet:-1' : 'noindex,follow',
    ];
@endphp
@include('partials.seo')
