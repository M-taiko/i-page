<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestController extends Controller
{
    private function recordViews(iterable $posts): void
    {
        if (!auth()->check()) {
            return;
        }

        foreach ($posts as $post) {
            $post->recordViewFor(auth()->user());
        }
    }

    public function home(Request $request): View
    {
        $tab = in_array($request->query('tab'), ['latest', 'trending']) ? $request->query('tab') : 'latest';

        $query = Post::whereHas('channel', fn ($q) => $q->where('type', 'public'))
            ->where('status', 'published')
            ->with('author', 'channel.brand', 'channel.organization');

        if ($tab === 'trending') {
            $query->withCount(['reactions', 'comments'])
                ->where('published_at', '>=', now()->subDays(14))
                ->orderByRaw('(reactions_count + comments_count) desc');
        } else {
            $query->latest('published_at');
        }

        $posts = $query->paginate(20)->withQueryString();
        $this->recordViews($posts);

        // Featured organizations for discovery
        $organizations = Organization::where('is_active', true)
            ->withCount('users', 'channels', 'posts')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('guest.home', compact('posts', 'organizations', 'tab'));
    }

    public function searchOrganizations(Request $request): View
    {
        $search = $request->get('q', '');

        $organizations = Organization::where('is_active', true)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->withCount('users', 'channels', 'posts')
            ->paginate(12);

        return view('guest.organizations', compact('organizations', 'search'));
    }

    public function organizationDetail(Organization $organization): View
    {
        $organization->loadCount(['users', 'channels', 'posts']);

        // Brands with their public channels (Organization → Brand → Channels)
        $brands = $organization->brands()
            ->where('is_active', true)
            ->with(['channels' => fn ($q) => $q->where('type', 'public')->withCount('users', 'posts')])
            ->get();

        // Public channels not grouped under any brand
        $unbrandedChannels = $organization->channels()
            ->where('type', 'public')
            ->whereNull('brand_id')
            ->withCount('users', 'posts')
            ->get();

        // Recent posts from this organization's public channels
        $posts = Post::whereHas('channel', function ($query) use ($organization) {
            $query->where('organization_id', $organization->id)
                  ->where('type', 'public');
        })
        ->where('status', 'published')
        ->with('author', 'channel.brand')
        ->latest('published_at')
        ->paginate(12)
        ->withQueryString();
        $this->recordViews($posts);

        return view('guest.organization-detail', compact('organization', 'brands', 'unbrandedChannels', 'posts'));
    }

    public function channelDetail(Organization $organization, $channelSlug): View
    {
        $channel = $organization->channels()
            ->where('slug', $channelSlug)
            ->where('type', 'public')
            ->with('brand')
            ->withCount('users')
            ->firstOrFail();

        $posts = $channel->posts()
            ->where('status', 'published')
            ->with('author')
            ->latest('published_at')
            ->paginate(20)
            ->withQueryString();
        $this->recordViews($posts);

        $isSubscribed = auth()->check() && auth()->user()->channels()->where('channel_id', $channel->id)->exists();
        $isFavorited = auth()->check() && auth()->user()->collections()
            ->where('is_favorites', true)
            ->whereHas('channels', fn ($q) => $q->where('channels.id', $channel->id))
            ->exists();

        return view('guest.channel-detail', compact('organization', 'channel', 'posts', 'isSubscribed', 'isFavorited'));
    }

    /**
     * A single post's public, permanently shareable page — reachable by
     * anyone (no login required) as long as it's published and either an
     * org-level post or in a public channel. Private-channel posts 404 here;
     * they're only reachable via the authenticated posts.show route.
     */
    public function postDetail(Post $post): View
    {
        abort_unless($post->status === 'published', 404);
        abort_if($post->channel && $post->channel->type !== 'public', 404);

        $post->load('author', 'channel.brand', 'channel.organization', 'organization');
        $this->recordViews([$post]);

        $comments = $post->comments()->approved()->with('user')->latest()->get();

        return view('guest.post-detail', compact('post', 'comments'));
    }
}
