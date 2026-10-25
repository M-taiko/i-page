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
            't' => config("landing.$lang"),
            'lang' => $lang,
            'other' => $lang === 'ar' ? 'en' : 'ar',
            'stats' => $stats,
            'home' => $this->workspaceUrl($request),
        ]);
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
