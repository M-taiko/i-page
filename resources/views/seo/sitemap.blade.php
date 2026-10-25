<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($urls as $u)
    <url>
        <loc>{{ $u['loc'] }}</loc>
@if (! empty($u['lastmod']))
        <lastmod>{{ $u['lastmod']->toAtomString() }}</lastmod>
@endif
        <changefreq>{{ $u['changefreq'] }}</changefreq>
        <priority>{{ $u['priority'] }}</priority>
@foreach (($u['alternates'] ?? []) as $lang => $href)
        <xhtml:link rel="alternate" hreflang="{{ $lang }}" href="{{ $href }}" />
@endforeach
    </url>
@endforeach
</urlset>
