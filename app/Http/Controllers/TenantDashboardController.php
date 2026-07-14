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
        $organization = auth()->user()->currentOrganization;
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

    /**
     * Switch which organization the tenant workspace (/tenant, /tenant/channels)
     * operates on. Only super admins may target an organization they're not a
     * member of — regular org admins are restricted to their own memberships.
     */
    public function switchOrganization(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => 'required|integer|exists:organizations,id',
        ]);

        $user = auth()->user();

        if (!$user->hasRole('super_admin') && !$user->organizations()->where('organizations.id', $validated['organization_id'])->exists()) {
            abort(403);
        }

        session(['current_organization_id' => (int) $validated['organization_id']]);

        return back()->with('success', __('Switched organization.'));
    }
}
