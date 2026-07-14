<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Channel;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Layer 3 (end user): personal feed + discovery.
 */
class UserFeedController extends Controller
{
    private function resolveCurrentOrganization(): ?Organization
    {
        $user = auth()->user();
        $currentOrganization = $user->currentOrganization;

        if ($currentOrganization) {
            session(['current_organization_id' => $currentOrganization->id]);
            view()->share('currentOrganization', $currentOrganization);
        }

        return $currentOrganization;
    }

    public function index(Request $request): View
    {
        $this->resolveCurrentOrganization();
        $user = auth()->user();

        $tab = in_array($request->query('tab'), ['for_you', 'following', 'discover', 'trending'])
            ? $request->query('tab')
            : 'for_you';

        $subscribedChannelIds = $user->channels()->pluck('channels.id')->toArray();
        $followedOrgIds = $user->followedOrganizations()->pluck('organization_id')->toArray();
        $followedBrandIds = $user->followedBrands()->pluck('brand_id')->toArray();
        $followedBrandChannelIds = Channel::whereIn('brand_id', $followedBrandIds)->pluck('id')->toArray();
        $followingChannelIds = array_values(array_unique(array_merge($subscribedChannelIds, $followedBrandChannelIds)));

        $query = Post::where('status', 'published')->with('author', 'channel.brand', 'channel.organization', 'organization');

        switch ($tab) {
            case 'following':
                // Strictly what the user follows/subscribes to.
                $query->where(function ($q) use ($followingChannelIds, $followedOrgIds) {
                    $q->whereIn('channel_id', $followingChannelIds)
                      ->orWhere(function ($inner) use ($followedOrgIds) {
                          $inner->whereIn('organization_id', $followedOrgIds)->whereNull('channel_id');
                      });
                });
                $query->latest('published_at');
                break;

            case 'discover':
                // Public channels the user does NOT already follow — new things to find.
                $query->whereHas('channel', function ($q) use ($followingChannelIds) {
                    $q->where('type', 'public')->whereNotIn('id', $followingChannelIds ?: [0]);
                });
                $query->latest('published_at');
                break;

            case 'trending':
                // Most engaged public posts recently (reactions + comments).
                $query->whereHas('channel', fn ($q) => $q->where('type', 'public'))
                    ->withCount(['reactions', 'comments'])
                    ->where('published_at', '>=', now()->subDays(14))
                    ->orderByRaw('(reactions_count + comments_count) desc');
                break;

            case 'for_you':
            default:
                // Personalized: following + a light sprinkle of public discovery.
                $query->where(function ($q) use ($followingChannelIds, $followedOrgIds) {
                    $q->whereIn('channel_id', $followingChannelIds)
                      ->orWhere(function ($inner) use ($followedOrgIds) {
                          $inner->whereIn('organization_id', $followedOrgIds)->whereNull('channel_id');
                      })
                      ->orWhereHas('channel', fn ($c) => $c->where('type', 'public'));
                });
                $query->latest('published_at');
                break;
        }

        $posts = $query->paginate(15)->withQueryString();

        foreach ($posts as $post) {
            $post->recordViewFor($user);
        }

        // Collections: personal folders of subscribed channels (Instagram-Highlight strip).
        $collections = $user->collections()
            ->withCount('channels')
            ->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->get();

        return view('feed.index', compact('posts', 'collections', 'tab'));
    }

    /**
     * Discovery: browse public channels across ALL organizations, plus any
     * private channel the user already subscribes to. End users typically
     * have no organization membership, so this is org-agnostic by design.
     */
    public function exploreChannels(): View
    {
        $this->resolveCurrentOrganization();
        $user = auth()->user();

        $subscribedChannelIds = $user->channels()->pluck('channels.id')->toArray();

        $channels = Channel::where('status', 'active')
            ->where(function ($q) use ($subscribedChannelIds) {
                $q->where('type', 'public')
                  ->orWhereIn('id', $subscribedChannelIds);
            })
            ->with('organization', 'brand')
            ->withCount('users', 'posts')
            ->latest('created_at')
            ->paginate(20);

        $collections = $user->collections()->orderBy('sort_order')->get();

        // Map channel_id => [collection_id, ...] for quick "already in" checks in the view.
        $channelCollectionMap = [];
        foreach ($collections as $collection) {
            foreach ($collection->channels()->pluck('channels.id') as $channelId) {
                $channelCollectionMap[$channelId][] = $collection->id;
            }
        }

        return view('feed.explore-channels', compact('channels', 'subscribedChannelIds', 'collections', 'channelCollectionMap'));
    }

    /**
     * Discovery: browse all active organizations to follow/subscribe into.
     */
    public function exploreOrganizations(): View
    {
        $this->resolveCurrentOrganization();

        $organizations = Organization::where('is_active', true)
            ->withCount('users', 'channels', 'posts')
            ->paginate(12);

        return view('feed.explore-organizations', compact('organizations'));
    }

    public function searchOrganizations(): \Illuminate\Http\JsonResponse
    {
        $query = request()->input('q', '');

        if (strlen($query) < 1) {
            return response()->json(['organizations' => []]);
        }

        $organizations = Organization::where('is_active', true)
            ->where('name', 'like', '%' . $query . '%')
            ->withCount('users', 'channels', 'posts')
            ->limit(10)
            ->get();

        return response()->json([
            'organizations' => $organizations->map(fn($org) => [
                'id' => $org->id,
                'name' => $org->name,
                'users_count' => $org->users_count,
                'channels_count' => $org->channels_count,
                'posts_count' => $org->posts_count,
            ])->toArray(),
        ]);
    }
}
