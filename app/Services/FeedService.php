<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Shared engine for the /browse and /feed pages (docs/27 "Personal Browse Feed").
 *
 *  - forYou():   posts of the channels a user joined (with cascades and tenant placements)
 *                plus posts tenants put on the pages of organizations the user follows.
 *  - discover(): public posts in a per-visit random order.
 *  - popular():  public posts ranked by freshness and views with a per-visit random factor,
 *                so newer and more-viewed posts surface first without the list being static.
 *
 * Both random orders are seeded once per visit (session) so infinite scroll never repeats or skips.
 */
class FeedService
{
    public const PAGE_SIZE = 10;

    /** Active channels the user is an approved member of. */
    public function joinedChannels(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return Channel::whereIn('id', DB::table('channel_user')->where('user_id', $user->id)->where('status', 'approved')->select('channel_id'))
            ->where('status', 'active')->get();
    }

    public function forYou(Collection $channels, ?User $user = null): Builder
    {
        $followedOrgIds = $user ? $user->followedOrganizations()->pluck('organization_id')->all() : [];
        $query = Channel::feedQueryFor($channels);

        if ($followedOrgIds) {
            $query->orWhere(fn ($t) => $t->whereNull('channel_id')->whereNotNull('placement')
                ->where('show_on_page', true)->whereIn('organization_id', $followedOrgIds));
        }

        return Post::query()->whereIn('posts.id', $query->select('posts.id'))
            ->latest('published_at')->orderByDesc('id');
    }

    /** Public posts: public active channels plus tenant posts shown on tenant pages. */
    public function publicPosts(): Builder
    {
        return Post::where(fn ($q) => $q
            ->whereHas('channel', fn ($c) => $c->where('type', 'public')->where('status', 'active'))
            ->orWhere(fn ($t) => $t->whereNull('channel_id')->whereNotNull('placement')->where('show_on_page', true)));
    }

    public function discover(int $seed): Builder
    {
        return $this->publicPosts()->inRandomOrder($seed);
    }

    /**
     * Freshness (halves after about a week) and views (log scale, relative to the most viewed post,
     * so it works for a new platform and a busy one alike) weigh half each, multiplied by a seeded
     * random factor between 0.6 and 1 for variety.
     */
    public function popular(int $seed): Builder
    {
        $maxViews = (int) DB::query()->fromSub(
            DB::table('post_receipts')->whereNotNull('first_viewed_at')->groupBy('post_id')->selectRaw('COUNT(*) AS c'), 'v'
        )->max('c');
        $views = '(SELECT COUNT(*) FROM post_receipts pr WHERE pr.post_id = posts.id AND pr.first_viewed_at IS NOT NULL)';
        $fresh = '(1 / (1 + GREATEST(TIMESTAMPDIFF(HOUR, posts.published_at, NOW()), 0) / 168))';
        $popular = '(LN(1 + ' . $views . ') / ' . log(1 + max(1, $maxViews)) . ')';

        return $this->publicPosts()
            ->orderByRaw("(($fresh * 0.5) + ($popular * 0.5)) * (0.6 + 0.4 * RAND(?)) DESC", [$seed])
            ->orderByDesc('posts.id');
    }

    /** The seed for this visit: new on a first page load, kept while scrolling. */
    public function seed(Request $request, string $key): int
    {
        $sessionKey = 'feed_seed_' . $key;
        $seed = (int) $request->session()->get($sessionKey, 0);
        if (! $seed || (! $request->boolean('partial') && (int) $request->query('page', 1) <= 1)) {
            $seed = random_int(1, 2_000_000_000);
            $request->session()->put($sessionKey, $seed);
        }

        return $seed;
    }

    /** Eager loads and counters the feed cards need, then one page of results. */
    public function page(Builder $query, ?User $user, array $appends = []): Paginator
    {
        return $query->where('status', 'published')
            ->with(['author', 'channel.organization', 'organization', 'media', 'pollOptions'])
            ->withCount([
                'reactions as like_count',
                'comments as comment_count' => fn ($c) => $c->where('status', 'approved'),
                'receipts as views_count' => fn ($r) => $r->whereNotNull('first_viewed_at'),
            ])
            ->when($user, fn ($q) => $q->withExists(['reactions as user_liked' => fn ($r) => $r->where('user_id', $user->id)]))
            ->simplePaginate(self::PAGE_SIZE)
            ->appends($appends);
    }

    public function recordViews(iterable $posts, ?User $user): void
    {
        if ($user) {
            foreach ($posts as $post) {
                $post->recordViewFor($user);
            }
        }
    }

    /** Popular public channels the user has not joined yet. */
    public function suggestedChannels(Collection $joined, int $take = 8): Collection
    {
        return Channel::where('type', 'public')->where('status', 'active')
            ->whereNotIn('id', $joined->pluck('id'))
            ->withCount(['users' => fn ($u) => $u->where('channel_user.status', 'approved')])
            ->with('organization')
            ->orderByDesc('users_count')
            ->limit(30)->get()->shuffle()->take($take);
    }
}
