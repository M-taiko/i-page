<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Organization;
use App\Models\Post;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index($organization): View
    {
        $organizationModel = Organization::findOrFail($organization);

        // Get only organization-level posts (no channel)
        $posts = Post::where('organization_id', $organization)
            ->whereNull('channel_id')
            ->where('status', 'published')
            ->latest('published_at')
            ->with('author', 'channel', 'organization')
            ->paginate(12);

        // Get all public channels in the organization
        $channels = Channel::where('organization_id', $organization)
            ->where('type', 'public')
            ->withCount('users', 'posts')
            ->get();

        // The user's subscribed channels (only if authenticated)
        $subscribedChannelIds = [];
        if (auth()->check()) {
            $subscribedChannelIds = auth()->user()->subscribedChannels()
                ->where('organization_id', $organization)
                ->pluck('channels.id')
                ->toArray();
        }

        return view('home.index-modern', compact('posts', 'organizationModel', 'subscribedChannelIds', 'channels'));
    }
}
