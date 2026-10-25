<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Organization;
use App\Models\Post;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/** Public, animated product page served at `/` (docs/27 "Product Landing Page"). */
class LandingController extends Controller
{
    public function __invoke(Request $request): View
    {
        $lang = $request->query('lang');
        if (in_array($lang, ['ar', 'en'], true)) {
            $request->session()->put('landing_lang', $lang);
        }
        $lang = $request->session()->get('landing_lang', 'ar');

        // Aggregate counts only; nothing personal is exposed.
        $stats = Cache::remember('landing.stats', 600, fn () => [
            'organizations' => Organization::where('status', 'active')->count(),
            'channels' => Channel::where('type', 'public')->where('status', 'active')->count(),
            'posts' => Post::where('status', 'published')->count(),
            'scans' => (int) QrCode::sum('scan_count'),
        ]);

        return view('landing', [
            'seo' => $this->seo($lang),
            't' => config("landing.$lang"),
            'lang' => $lang,
            'other' => $lang === 'ar' ? 'en' : 'ar',
            'stats' => $stats,
            'home' => $this->workspaceUrl($request),
        ]);
    }

    /** Share card + SEO metadata for the landing page in the given language. */
    private function seo(string $lang): array
    {
        $t = config("landing.$lang.seo");
        $home = rtrim(url('/'), '/') . '/';
        $canonical = $lang === 'ar' ? $home : $home . '?lang=en';
        $image = asset('images/og/ipage-share.jpg');

        return [
            'title' => $t['title'],
            'description' => $t['description'],
            'url' => $canonical,
            'image' => $image,
            'image_alt' => $t['image_alt'],
            'locale' => $t['locale'],
            'alternates' => ['ar' => $home, 'en' => $home . '?lang=en', 'x-default' => $home],
            'schema' => [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'Organization',
                        '@id' => $home . '#organization',
                        'name' => 'i-Page',
                        'url' => $home,
                        'logo' => asset('images/pwa/icon-512.png'),
                        'slogan' => config('landing.en.brand_tag'),
                    ],
                    [
                        '@type' => 'WebSite',
                        '@id' => $home . '#website',
                        'url' => $home,
                        'name' => 'i-Page',
                        'inLanguage' => $lang,
                        'publisher' => ['@id' => $home . '#organization'],
                    ],
                    [
                        '@type' => 'SoftwareApplication',
                        'name' => 'i-Page',
                        'applicationCategory' => 'BusinessApplication',
                        'operatingSystem' => 'Web',
                        'description' => $t['description'],
                        'url' => $home,
                        'image' => $image,
                        'inLanguage' => $lang,
                    ],
                ],
            ],
        ];
    }

    /** Where a signed-in user's "Open my workspace" button goes. */
    private function workspaceUrl(Request $request): ?string
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        return $user->hasRole('super_admin') ? route('admin.dashboard') : route('user.feed');
    }
}
