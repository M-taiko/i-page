<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Channel;
use App\Models\Post;
use App\Services\FeedService;
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

    /**
     * Signed-in home (docs/27 "Personal Browse Feed"). "for-you": posts of the channels the user
     * joined; "popular": public posts ranked by freshness and views with a per-visit random factor.
     * Users without channels land on "popular". ?partial=1 returns the next batch for infinite scroll.
     */
    public function index(Request $request, FeedService $feed): View|\Illuminate\Http\JsonResponse
    {
        $this->resolveCurrentOrganization();
        $user = $request->user();
        $joined = $feed->joinedChannels($user);
        $hasFeed = $joined->isNotEmpty();

        $tab = $request->query('tab');
        if (! in_array($tab, ['for-you', 'popular'], true) || ($tab === 'for-you' && ! $hasFeed)) {
            $tab = $hasFeed ? 'for-you' : 'popular';
        }

        $query = $tab === 'for-you' ? $feed->forYou($joined, $user) : $feed->popular($feed->seed($request, 'popular'));
        $posts = $feed->page($query, $user, ['tab' => $tab]);
        $feed->recordViews($posts, $user);

        if ($request->boolean('partial')) {
            return response()->json([
                'html' => view('guest.partials._feed-cards', ['posts' => $posts])->render(),
                'next' => $posts->nextPageUrl(),
            ]);
        }

        // Collections: personal folders of subscribed channels (Instagram-Highlight strip).
        $collections = $user->collections()
            ->withCount('channels')
            ->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->get();

        $stats = [
            'channels' => $joined->count(),
            'today' => $hasFeed ? (clone $feed->forYou($joined, $user))->where('status', 'published')->where('published_at', '>=', now()->startOfDay())->count() : 0,
            'unread' => $user->notifications()->whereNull('read_at')->count(),
        ];

        return view('feed.index', [
            'posts' => $posts,
            'collections' => $collections,
            'tab' => $tab,
            'hasFeed' => $hasFeed,
            'stats' => $stats,
            'suggested' => $hasFeed ? collect() : $feed->suggestedChannels($joined, 10),
        ]);
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
            ->with('organization')
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
            ->when(request('q'), fn ($q) => $q->where('name', 'like', '%' . request('q') . '%'))
            ->withCount('users', 'channels', 'posts')
            ->paginate(12)
            ->withQueryString();

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

    /**
     * Discovery: search-only directory of people (by name or @username) —
     * foundation for future user-to-user chat. No "browse all" grid, since
     * listing every user isn't a meaningful default view.
     */
    public function explorePeople(): View
    {
        $this->resolveCurrentOrganization();

        return view('feed.explore-people');
    }

    public function searchPeople(): \Illuminate\Http\JsonResponse
    {
        $query = request()->input('q', '');

        if (strlen($query) < 1) {
            return response()->json(['users' => []]);
        }

        $users = \App\Models\User::where('id', '!=', auth()->id())
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', '%' . $query . '%')
                    ->orWhere('last_name', 'like', '%' . $query . '%')
                    ->orWhere('username', 'like', '%' . $query . '%');
            })
            ->limit(15)
            ->get();

        return response()->json([
            'users' => $users->map(fn ($u) => [
                'id' => $u->id,
                'full_name' => $u->full_name,
                'username' => $u->username,
                'initials' => $u->initials,
                'avatar_path' => $u->avatar_path ? \Illuminate\Support\Facades\Storage::url($u->avatar_path) : null,
                'profile_level' => $u->profile_level,
            ])->toArray(),
        ]);
    }
}
