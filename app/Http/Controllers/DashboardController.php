<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Organization;
use App\Models\User;
use App\Models\Post;
use App\Models\Group;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index($organization): View
    {
        $organizationModel = Organization::findOrFail($organization);

        $kpis = [
            'total_channels' => Channel::where('organization_id', $organizationModel->id)->count(),
            'active_users' => User::where('last_seen_at', '>=', now()->subDays(7))->count(),
            'posts_today' => Post::where('organization_id', $organizationModel->id)->published()->whereDate('created_at', today())->count(),
            'groups' => Group::where('organization_id', $organizationModel->id)->count(),
            'vip_guests' => User::where('is_vip', true)->where('check_out_at', '>=', now())->count(),
            'pending_notices' => Post::where('organization_id', $organizationModel->id)->where('status', 'pending_approval')->count(),
        ];

        $recent_posts = Post::where('organization_id', $organizationModel->id)
            ->published()
            ->with('author', 'channel')
            ->latest('published_at')
            ->limit(5)
            ->get();

        return view('dashboard.dashboard-modern', compact('kpis', 'recent_posts', 'organizationModel'));
    }
}
