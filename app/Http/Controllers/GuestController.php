<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Post;
use App\Support\Designs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GuestController extends Controller
{
    /**
     * The organization's design template, or null for the classic page.
     * `?preview_design=key` previews another design of the same industry, for organization admins only.
     */
    private function resolveDesign(Organization $organization): ?array
    {
        $industry = $organization->template?->industry_key;
        $preview = request()->query('preview_design');

        if ($preview && auth()->check() && Gate::allows('update', $organization)) {
            if ($design = Designs::find($industry, $preview)) {
                return $design;
            }
        }

        return Designs::forOrganization($organization);
    }

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

        // Public channel posts plus tenant posts shown on their tenant page (ADR-016).
        $query = Post::where(fn ($q) => $q->whereHas('channel', fn ($c) => $c->where('type', 'public'))
                ->orWhere(fn ($t) => $t->whereNull('channel_id')->whereNotNull('placement')->where('show_on_page', true)))
            ->where('status', 'published')
            ->with('author', 'channel.organization', 'organization');

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

        // Public, top-level channels; sub-channels surface in their parent's "sub-channels" popup.
        $channels = $organization->channels()
            ->where('type', 'public')
            ->whereDoesntHave('parentChannels')
            ->withCount('users', 'posts')
            ->with(['childChannels' => fn ($cq) => $cq->withCount('users', 'posts')])
            ->orderBy('name')
            ->get();

        // The tenant's own posts shown on its page, plus posts of its public channels (ADR-016).
        $posts = Post::where(fn ($q) => $q
                ->whereHas('channel', fn ($c) => $c->where('organization_id', $organization->id)->where('type', 'public'))
                ->orWhere(fn ($t) => $t->whereNull('channel_id')->where('organization_id', $organization->id)
                    ->whereNotNull('placement')->where('show_on_page', true)))
            ->where('status', 'published')
            ->with('author', 'channel', 'organization', 'media')
            // Pinned first, then the tenant's own posts, then channel posts — newest first in each group.
            ->orderByRaw('CASE WHEN pinned_until > ? THEN 0 WHEN channel_id IS NULL THEN 1 ELSE 2 END', [now()])
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();
        $this->recordViews($posts);

        // Organizations that picked a design template render through the Valex public layout (ADR-012).
        if ($design = $this->resolveDesign($organization)) {
            $channelGroups = $channels->isEmpty() ? collect() : collect([['title' => __('Channels'), 'channels' => $channels]]);

            return view('guest.valex.organization', [
                'organization' => $organization,
                'design' => $design,
                'options' => Designs::options($organization, $design),
                'channelGroups' => $channelGroups,
                'posts' => $posts,
                'isFollowing' => auth()->check()
                    && auth()->user()->followedOrganizations()->where('organization_id', $organization->id)->exists(),
            ]);
        }

        return view('guest.organization-detail', compact('organization', 'channels', 'posts'));
    }

    public function channelDetail(Organization $organization, $channelSlug): View
    {
        $channel = $organization->channels()
            ->where('slug', $channelSlug)
            ->where('type', 'public')
            ->withCount('users')
            ->firstOrFail();

        // Own posts + posts cascading from parent channels (ADR-014).
        $posts = $channel->feedQuery()
            ->where('status', 'published')
            ->with(['author', 'channel', 'organization', 'media'])
            ->latest('published_at')
            ->paginate(20)
            ->withQueryString();
        $this->recordViews($posts);

        $highlights = $channel->highlights();
        $postSettings = $channel->postSettings();
        view()->share(compact('highlights', 'postSettings'));

        $isSubscribed = auth()->check() && auth()->user()->channels()->where('channel_id', $channel->id)->exists();
        $isFavorited = auth()->check() && auth()->user()->collections()
            ->where('is_favorites', true)
            ->whereHas('channels', fn ($q) => $q->where('channels.id', $channel->id))
            ->exists();

        // All public, top-level channels of this organization, for the
        // Instagram-style "stories" strip — lets a visitor jump between
        // sibling channels without going back to the organization page.
        // Sub-channels are excluded here too; they live under their parent's
        // popup on the organization page.
        $orgChannels = $organization->channels()
            ->where('type', 'public')
            ->whereDoesntHave('parentChannels')
            ->withCount('posts')
            ->orderBy('name')
            ->get();

        // Navigation strip (any depth): a channel with sub-channels shows them (drill down);
        // a leaf shows its siblings. Both get a "back to parent" item.
        $parentChannel = $channel->parentChannels()->where('type', 'public')->first();
        $hasChildren = $channel->childChannels()->where('type', 'public')->exists();
        $stripSource = ($hasChildren || ! $parentChannel) ? $channel : $parentChannel;
        $subChannels = $stripSource->childChannels()
            ->where('type', 'public')
            ->orderBy('name')
            ->get();

        // Channels with a design template render through the Valex public layout (ADR-012 addendum).
        $design = Designs::forChannel($channel);
        $preview = request()->query('preview_design');
        if ($preview && auth()->check() && Gate::allows('update', $organization) && ($previewDesign = Designs::findChannel($preview))) {
            $design = $previewDesign;
        }

        if ($design) {
            // A channel with sub-channels lists them; a leaf lists its siblings.
            $related = $stripSource->childChannels()
                ->where('type', 'public')
                ->where('channels.id', '!=', $channel->id)
                ->withCount('users', 'posts')
                ->with(['childChannels' => fn ($q) => $q->withCount('users', 'posts')])
                ->orderBy('name')
                ->get();
            $channelGroups = $related->isEmpty()
                ? collect()
                : collect([['title' => $stripSource->is($channel) ? __('Sub-channels') : __('Related channels'), 'channels' => $related]]);

            return view('guest.valex.channel', [
                'organization' => $organization,
                'channel' => $channel,
                'design' => $design,
                'options' => Designs::channelOptions($channel, $organization, $design),
                'posts' => $posts,
                'parentChannel' => $parentChannel,
                'channelGroups' => $channelGroups,
                'isSubscribed' => $isSubscribed,
                'isFavorited' => $isFavorited,
            ]);
        }

        return view('guest.channel-detail', compact('organization', 'channel', 'posts', 'isSubscribed', 'isFavorited', 'orgChannels', 'parentChannel', 'subChannels'));
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
        abort_unless($post->isVisibleTo(auth()->user()), 404);

        $post->load('author', 'channel.organization', 'organization');
        $this->recordViews([$post]);

        $comments = $post->comments()->approved()->with('user')->latest()->get();

        return view('guest.post-detail', compact('post', 'comments'));
    }
}
