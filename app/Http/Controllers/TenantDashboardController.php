<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Channel;
use App\Models\Post;
use Illuminate\Http\Request;

class TenantDashboardController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->organizations()->first();
        if (!$organization) {
            abort(403, __('Unauthorized'));
        }

        $stats = [
            'total_channels' => Channel::where('organization_id', $organization->id)->count(),
            'max_channels' => $organization->max_channels,
            'total_posts' => Post::where('organization_id', $organization->id)->count(),
            'total_users' => $organization->users()->count(),
            'recent_posts' => Post::where('organization_id', $organization->id)
                ->with('author', 'channel')
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return view('tenant.dashboard', compact('organization', 'stats'));
    }
}
