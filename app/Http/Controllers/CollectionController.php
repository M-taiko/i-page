<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Channel;
use App\Models\Organization;
use App\Models\Post;
use App\Models\UserCollection;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Layer 3: personal collections (folders) of subscribed channels.
 * Purely personal — never shared, never affects permissions.
 */
class CollectionController extends Controller
{
    private const ICONS = [
        '💼', '🛒', '🎓', '🏋️', '⚽', '🍔', '🏥', '🏦',
        '🎮', '🎵', '🎬', '📰', '✈️', '❤️', '📚', '💻', '📦', '🛢', '📁',
    ];

    private const COLORS = [
        '#4557f5' => 'Blue',
        '#059669' => 'Green',
        '#d97706' => 'Orange',
        '#7c3aed' => 'Purple',
        '#dc2626' => 'Red',
        '#0d9488' => 'Teal',
        '#db2777' => 'Pink',
        '#f59e0b' => 'Amber',
    ];

    /**
     * Manage page: grid of all the user's collections.
     */
    public function index(): View
    {
        $collections = auth()->user()->collections()
            ->withCount('channels')
            ->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->get();

        $subscribedChannels = auth()->user()->channels()->with('organization', 'brand')->get();

        // channel_id => [collection_id, ...] so the "Manage Channels" modal can pre-check membership.
        $channelCollectionMap = [];
        foreach ($collections as $collection) {
            foreach ($collection->channels()->pluck('channels.id') as $channelId) {
                $channelCollectionMap[$channelId][] = $collection->id;
            }
        }

        return view('collections.index', [
            'collections' => $collections,
            'subscribedChannels' => $subscribedChannels,
            'icons' => self::ICONS,
            'colors' => self::COLORS,
            'channelCollectionMap' => $channelCollectionMap,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:60',
            'icon' => 'required|string|max:10',
            'color' => 'required|string|max:7',
        ]);

        $maxOrder = auth()->user()->collections()->max('sort_order') ?? 0;

        $collection = auth()->user()->collections()->create([
            ...$validated,
            'sort_order' => $maxOrder + 1,
        ]);

        // Optional: create-and-add-channel in one step
        if ($request->filled('channel_id')) {
            $channel = Channel::find($request->input('channel_id'));
            if ($channel && auth()->user()->channels()->where('channel_id', $channel->id)->exists()) {
                $collection->channels()->attach($channel->id);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['collection' => $collection->loadCount('channels')]);
        }

        return back()->with('success', __('Collection created.'));
    }

    public function update(Request $request, UserCollection $collection): RedirectResponse
    {
        $this->authorize('update', $collection);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:60',
            'icon' => 'sometimes|required|string|max:10',
            'color' => 'sometimes|required|string|max:7',
        ]);

        $collection->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['collection' => $collection]);
        }

        return back()->with('success', __('Collection updated.'));
    }

    public function destroy(UserCollection $collection): RedirectResponse
    {
        $this->authorize('delete', $collection);

        $collection->delete();

        return back()->with('success', __('Collection deleted.'));
    }

    public function togglePin(UserCollection $collection): RedirectResponse
    {
        $this->authorize('update', $collection);

        $collection->update(['is_pinned' => !$collection->is_pinned]);

        return back();
    }

    public function toggleMute(UserCollection $collection): RedirectResponse
    {
        $this->authorize('update', $collection);

        $collection->update(['is_muted' => !$collection->is_muted]);

        return back();
    }

    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:user_collections,id',
        ]);

        foreach ($validated['order'] as $index => $collectionId) {
            UserCollection::where('id', $collectionId)
                ->where('user_id', auth()->id())
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function addChannel(UserCollection $collection, Channel $channel): RedirectResponse
    {
        $this->authorize('update', $collection);

        $this->ensureSubscribed($channel);

        if (!$collection->channels()->where('channel_id', $channel->id)->exists()) {
            $collection->channels()->attach($channel->id);
        }

        return back()->with('success', __('Added to :name.', ['name' => $collection->name]));
    }

    public function removeChannel(UserCollection $collection, Channel $channel): RedirectResponse
    {
        $this->authorize('update', $collection);

        $collection->channels()->detach($channel->id);

        return back()->with('success', __('Removed from :name.', ['name' => $collection->name]));
    }

    /**
     * Add every accessible channel of an organization (public + already-subscribed
     * private ones) to the collection, auto-subscribing where needed.
     */
    public function addOrganization(UserCollection $collection, Organization $organization): RedirectResponse
    {
        $this->authorize('update', $collection);

        $subscribedIds = auth()->user()->channels()->pluck('channels.id')->toArray();

        $channels = $organization->channels()
            ->where(function ($q) use ($subscribedIds) {
                $q->where('type', 'public')->orWhereIn('id', $subscribedIds);
            })
            ->get();

        foreach ($channels as $channel) {
            $this->ensureSubscribed($channel);
            if (!$collection->channels()->where('channel_id', $channel->id)->exists()) {
                $collection->channels()->attach($channel->id);
            }
        }

        return back()->with('success', __(':count channels from :name added to :collection.', [
            'count' => $channels->count(),
            'name' => $organization->name,
            'collection' => $collection->name,
        ]));
    }

    /**
     * Add every accessible channel of a brand to the collection, auto-subscribing where needed.
     */
    public function addBrand(UserCollection $collection, Brand $brand): RedirectResponse
    {
        $this->authorize('update', $collection);

        $subscribedIds = auth()->user()->channels()->pluck('channels.id')->toArray();

        $channels = $brand->channels()
            ->where(function ($q) use ($subscribedIds) {
                $q->where('type', 'public')->orWhereIn('id', $subscribedIds);
            })
            ->get();

        foreach ($channels as $channel) {
            $this->ensureSubscribed($channel);
            if (!$collection->channels()->where('channel_id', $channel->id)->exists()) {
                $collection->channels()->attach($channel->id);
            }
        }

        return back()->with('success', __(':count channels from :name added to :collection.', [
            'count' => $channels->count(),
            'name' => $brand->name,
            'collection' => $collection->name,
        ]));
    }

    private function ensureSubscribed(Channel $channel): void
    {
        $user = auth()->user();
        if (!$user->channels()->where('channel_id', $channel->id)->exists()) {
            $user->channels()->attach($channel->id, ['role' => 'member', 'joined_at' => now()]);
        }
    }

    /**
     * Filtered feed: only posts from channels inside this collection.
     */
    public function show(UserCollection $collection): View
    {
        $this->authorize('view', $collection);

        $channelIds = $collection->channels()->pluck('channels.id')->toArray();

        $posts = Post::where('status', 'published')
            ->whereIn('channel_id', $channelIds ?: [0])
            ->with('author', 'channel.brand', 'channel.organization')
            ->latest('published_at')
            ->paginate(15)
            ->withQueryString();

        foreach ($posts as $post) {
            $post->recordViewFor(auth()->user());
        }

        $collection->loadCount('channels');
        $collectionChannels = $collection->channels()->with('organization', 'brand')->get();

        return view('collections.show', compact('collection', 'posts', 'collectionChannels'));
    }
}
