<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Organization;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/** Dynamic /sitemap.xml and /robots.txt (docs/27 "Sharing & SEO"). */
class SeoController extends Controller
{
    private const SITEMAP_LIMIT = 1000;

    public function sitemap(): Response
    {
        $xml = Cache::remember('seo.sitemap', 3600, function () {
            $home = rtrim(url('/'), '/') . '/';
            $urls = [
                ['loc' => $home, 'priority' => '1.0', 'changefreq' => 'weekly',
                    'alternates' => ['ar' => $home, 'en' => $home . '?lang=en', 'x-default' => $home]],
                ['loc' => $home . '?lang=en', 'priority' => '0.8', 'changefreq' => 'weekly',
                    'alternates' => ['ar' => $home, 'en' => $home . '?lang=en', 'x-default' => $home]],
                ['loc' => route('guest.home'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ];

            $organizations = Organization::where('status', 'active')->where('is_active', true)
                ->orderByDesc('updated_at')->limit(self::SITEMAP_LIMIT)->get(['id', 'updated_at']);
            foreach ($organizations as $org) {
                $urls[] = ['loc' => route('guest.organization-detail', $org), 'lastmod' => $org->updated_at, 'priority' => '0.7', 'changefreq' => 'weekly'];
            }

            $channels = Channel::where('type', 'public')->where('status', 'active')
                ->whereIn('organization_id', $organizations->pluck('id'))
                ->orderByDesc('updated_at')->limit(self::SITEMAP_LIMIT * 2)->get(['organization_id', 'slug', 'updated_at']);
            foreach ($channels as $channel) {
                $urls[] = ['loc' => route('guest.channel-detail', [$channel->organization_id, $channel->slug]), 'lastmod' => $channel->updated_at, 'priority' => '0.5', 'changefreq' => 'weekly'];
            }

            $urls[] = ['loc' => route('guest.search-organizations'), 'priority' => '0.6', 'changefreq' => 'weekly'];

            // Public posts: posts of public channels and posts tenants show on their page (newest first).
            $posts = \App\Models\Post::where('status', 'published')
                ->where(fn ($q) => $q->whereHas('channel', fn ($c) => $c->where('type', 'public')->where('status', 'active')->whereIn('organization_id', $organizations->pluck('id')))
                    ->orWhere(fn ($t) => $t->whereNull('channel_id')->where('show_on_page', true)->whereIn('organization_id', $organizations->pluck('id'))))
                ->latest('published_at')->limit(self::SITEMAP_LIMIT)->get(['id', 'updated_at', 'published_at']);
            foreach ($posts as $post) {
                $urls[] = ['loc' => route('guest.post-detail', $post->id), 'lastmod' => $post->updated_at ?? $post->published_at, 'priority' => '0.4', 'changefreq' => 'monthly'];
            }

            return view('seo.sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $private = ['/admin', '/admin-dashboard', '/tenant', '/organizations', '/organization/', '/dashboard', '/settings', '/tickets', '/posts', '/feed', '/profile', '/collections', '/notifications', '/otp', '/qr/', '/channels/', '/browse/search?'];

        $lines = ['User-agent: *'];
        foreach ($private as $path) {
            $lines[] = 'Disallow: ' . $path;
        }
        $lines[] = 'Allow: /';
        $lines[] = '';
        $lines[] = 'Sitemap: ' . url('/sitemap.xml');

        return response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
