<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Channel extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'audience_profile',
        'audience_count',
        'logo_path',
        'admin_user_id',
        'status',
        'qr_path',
        'share_url',
        'organization_id',
        'design_key',
        'design_options',
    ];

    protected $casts = [
        'audience_count' => 'integer',
        'design_options' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'channel_user')
            ->withPivot('role', 'status', 'source', 'joined_at', 'muted_at', 'can_post', 'can_edit', 'can_delete', 'can_manage_bookings')
            ->using(ChannelUser::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function appointmentServices(): HasMany
    {
        return $this->hasMany(AppointmentService::class);
    }

    public function qrCodes(): MorphMany
    {
        return $this->morphMany(QrCode::class, 'ownable');
    }

    public function parentChannels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'channel_channel', 'child_channel_id', 'parent_channel_id');
    }

    public function childChannels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'channel_channel', 'parent_channel_id', 'child_channel_id');
    }

    // ------------------------------------------ Hierarchy & feed (ADR-014)

    public const POST_SETTING_DEFAULTS = [
        'layout' => 'list',          // list | grid | compact
        'card_style' => 'post',      // post (as chosen per post) | classic | image | gradient | spotlight
        'excerpt' => 280,
        'show_author' => true,
        'show_date' => true,
        'show_views' => true,
        'show_reactions' => true,
        'show_comments' => true,
    ];

    /** Role → default granular permissions used when inviting a member. */
    public const ROLE_PERMISSIONS = [
        'member' => ['can_post' => false, 'can_edit' => false, 'can_delete' => false],
        'moderator' => ['can_post' => false, 'can_edit' => true, 'can_delete' => true],
        'admin' => ['can_post' => true, 'can_edit' => true, 'can_delete' => true],
    ];

    private ?array $ancestorIdsCache = null;

    /** Ids of every ancestor channel (parents, grandparents, ...). Cycle-safe. */
    public function ancestorIds(): array
    {
        if ($this->ancestorIdsCache !== null) {
            return $this->ancestorIdsCache;
        }

        $seen = [];
        $frontier = [$this->id];
        while ($frontier) {
            $parents = \Illuminate\Support\Facades\DB::table('channel_channel')
                ->whereIn('child_channel_id', $frontier)->pluck('parent_channel_id')->all();
            $frontier = array_values(array_diff(array_unique($parents), $seen, [$this->id]));
            $seen = array_merge($seen, $frontier);
        }

        return $this->ancestorIdsCache = $seen;
    }

    /** Ancestors this channel may inherit posts from: a public channel never inherits from private ones. */
    public function inheritableAncestorIds(): array
    {
        $ids = $this->ancestorIds();
        if (! $ids) {
            return [];
        }

        return self::whereIn('id', $ids)
            ->where('organization_id', $this->organization_id)
            ->when($this->type !== 'private', fn ($q) => $q->where('type', 'public'))
            ->pluck('id')->all();
    }

    /** Ids of every descendant of the given channels (any depth, cycle-safe), excluding the start ids. */
    public static function descendantIdsOf(array $ids): array
    {
        $seen = $ids;
        $frontier = $ids;
        while ($frontier) {
            $children = \Illuminate\Support\Facades\DB::table('channel_channel')
                ->whereIn('parent_channel_id', $frontier)->pluck('child_channel_id')->all();
            $frontier = array_values(array_diff(array_unique($children), $seen));
            $seen = array_merge($seen, $frontier);
        }

        return array_values(array_diff($seen, $ids));
    }

    /** Posts shown in this channel: its own, cascading posts of its ancestors, and tenant posts placed here. */
    public function feedQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $inherited = $this->inheritableAncestorIds();

        return Post::query()->where(function ($q) use ($inherited) {
            $q->where('channel_id', $this->id);
            if ($inherited) {
                $q->orWhere(fn ($w) => $w->whereIn('channel_id', $inherited)->where('cascade', true));
            }
            $this->orWhereTenantPost($q, $inherited);
        });
    }

    /**
     * Posts shown in any of $channels — the union of their feedQuery() results in one query:
     * their own posts, cascading posts of their inheritable ancestors, and tenant posts placed
     * in them (placement "all", or "selected" directly / through a cascading ancestor).
     */
    public static function feedQueryFor(iterable $channels): \Illuminate\Database\Eloquent\Builder
    {
        $ids = $inherited = $orgIds = [];
        foreach ($channels as $channel) {
            $ids[] = $channel->id;
            $orgIds[] = $channel->organization_id;
            $inherited = array_merge($inherited, $channel->inheritableAncestorIds());
        }
        if (! $ids) {
            return Post::query()->whereRaw('1 = 0');
        }
        $inherited = array_values(array_unique($inherited));
        $orgIds = array_values(array_unique($orgIds));
        $targetedIn = fn (array $channelIds) => fn ($e) => $e->selectRaw(1)->from('post_channel')
            ->whereColumn('post_channel.post_id', 'posts.id')->whereIn('post_channel.channel_id', $channelIds);

        return Post::query()->where(function ($q) use ($ids, $inherited, $orgIds, $targetedIn) {
            $q->whereIn('channel_id', $ids);
            if ($inherited) {
                $q->orWhere(fn ($w) => $w->whereIn('channel_id', $inherited)->where('cascade', true));
            }
            $q->orWhere(fn ($w) => $w->whereNull('channel_id')->whereIn('organization_id', $orgIds)
                ->where(fn ($p) => $p->where('placement', 'all')
                    ->orWhere(fn ($s) => $s->where('placement', 'selected')
                        ->where(fn ($t) => $t->whereExists($targetedIn($ids))
                            ->when($inherited, fn ($c) => $c->orWhere(fn ($cc) => $cc->where('cascade', true)->whereExists($targetedIn($inherited))))))));
        });
    }

    /**
     * Tenant posts reaching this channel (ADR-016): placement "all", or "selected" with this channel
     * chosen, or an inheritable ancestor chosen while the post cascades. Private ancestors never
     * reach public channels because $inherited already excludes them.
     */
    private function orWhereTenantPost($q, array $inherited): void
    {
        $q->orWhere(fn ($w) => $w->whereNull('channel_id')
            ->where('organization_id', $this->organization_id)
            ->where(fn ($p) => $p->where('placement', 'all')
                ->orWhere(fn ($s) => $s->where('placement', 'selected')
                    ->where(fn ($t) => $t
                        ->whereExists(fn ($e) => $e->selectRaw(1)->from('post_channel')
                            ->whereColumn('post_channel.post_id', 'posts.id')->where('post_channel.channel_id', $this->id))
                        ->when($inherited, fn ($c) => $c->orWhere(fn ($cc) => $cc->where('cascade', true)
                            ->whereExists(fn ($e) => $e->selectRaw(1)->from('post_channel')
                                ->whereColumn('post_channel.post_id', 'posts.id')->whereIn('post_channel.channel_id', $inherited))))))));
    }

    /**
     * Currently pinned, published posts for the highlights strip: this channel's own pins, plus
     * ancestor pins whose scope is "all" (options.pin_scope; missing = all), plus pinned tenant posts
     * placed here.
     */
    public function highlights(int $limit = 10)
    {
        $inherited = $this->inheritableAncestorIds();

        return Post::query()
            ->where(function ($q) use ($inherited) {
                $q->where('channel_id', $this->id);
                if ($inherited) {
                    $q->orWhere(fn ($w) => $w->whereIn('channel_id', $inherited)
                        ->where(fn ($s) => $s->whereNull('options->pin_scope')->orWhere('options->pin_scope', 'all')));
                }
                $this->orWhereTenantPost($q, $inherited);
            })
            ->where('status', 'published')
            ->where('pinned_until', '>', now())
            ->with(['media', 'channel', 'organization'])
            ->latest('published_at')->limit($limit)->get();
    }

    /** Post presentation settings; a channel without its own uses the nearest ancestor's (any depth). */
    public function postSettings(): array
    {
        $own = $this->design_options['posts'] ?? null;

        $channel = $this;
        $seen = [$this->id];
        while ($own === null && ($channel = $channel->parentChannels()->first()) && ! in_array($channel->id, $seen, true)) {
            $seen[] = $channel->id;
            $own = $channel->design_options['posts'] ?? null;
        }

        return array_merge(self::POST_SETTING_DEFAULTS, array_intersect_key($own ?? [], self::POST_SETTING_DEFAULTS));
    }

    // ------------------------------------------ Roles & visibility (ADR-016)

    private array $roleCache = [];

    /** Organization admins/managers (and super admins) control every channel of the tenant. */
    public static function isOrgManager(User $user, int $organizationId): bool
    {
        return $user->hasRole('super_admin') || $user->organizationMemberships()
            ->where('organization_id', $organizationId)->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])->exists();
    }

    /**
     * The user's effective channel role: their own approved role here, upgraded by any admin or
     * moderator role they hold on an ancestor (roles inherit down to every sub-channel).
     * Returns admin | moderator | member | null.
     */
    public function roleFor(?User $user): ?string
    {
        if (! $user) {
            return null;
        }
        if (array_key_exists($user->id, $this->roleCache)) {
            return $this->roleCache[$user->id];
        }

        $rows = \Illuminate\Support\Facades\DB::table('channel_user')
            ->where('user_id', $user->id)->where('status', 'approved')
            ->whereIn('channel_id', array_merge([$this->id], $this->ancestorIds()))
            ->get(['channel_id', 'role']);

        $role = $rows->firstWhere('channel_id', $this->id)?->role;
        $inherited = $rows->where('channel_id', '!=', $this->id)->pluck('role');
        if ($inherited->contains('admin') || $role === 'admin') {
            $role = 'admin';
        } elseif ($inherited->contains('moderator') || $role === 'moderator') {
            $role = 'moderator';
        }

        return $this->roleCache[$user->id] = $role;
    }

    /** Whether the user manages this channel outright (all permissions, settings, members, QR). */
    public function isManagedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->admin_user_id === $user->id
            || self::isOrgManager($user, $this->organization_id)
            || $this->roleFor($user) === 'admin';
    }

    /** Approved member of this channel. */
    public function hasMember(?User $user): bool
    {
        return $user && \Illuminate\Support\Facades\DB::table('channel_user')
            ->where('channel_id', $this->id)->where('user_id', $user->id)->where('status', 'approved')->exists();
    }

    /** Member list: managers, moderators (own or inherited) and the channel's own approved members. */
    public function membersVisibleTo(?User $user): bool
    {
        return $this->isManagedBy($user) || $this->roleFor($user) !== null;
    }

    /** Settings, members and QR codes. */
    public function canAdminister(?User $user): bool
    {
        return $this->isManagedBy($user);
    }

    /**
     * Channels of $organizationId that $user may open in the dashboard: everything for organization
     * managers; otherwise the channels they are an approved member of, plus every sub-channel of
     * channels where they are admin or moderator.
     */
    public function scopeVisibleTo($query, User $user, int $organizationId)
    {
        $query->where('organization_id', $organizationId);
        if (self::isOrgManager($user, $organizationId)) {
            return $query;
        }

        $rows = \Illuminate\Support\Facades\DB::table('channel_user')->where('user_id', $user->id)->where('status', 'approved')->get(['channel_id', 'role']);
        $managed = $rows->whereIn('role', ['admin', 'moderator'])->pluck('channel_id')->all();
        $ids = array_merge($rows->pluck('channel_id')->all(), $managed ? self::descendantIdsOf($managed) : []);

        return $query->where(fn ($q) => $q->whereIn('id', $ids)->orWhere('admin_user_id', $user->id));
    }

    /** Granular permission check: $ability is post | edit | delete. */
    public function userCan(?User $user, string $ability): bool
    {
        if (! $user || ! in_array($ability, ['post', 'edit', 'delete'], true)) {
            return false;
        }

        if ($this->isManagedBy($user)) {
            return true;
        }

        if ($ability !== 'post' && $this->roleFor($user) === 'moderator') {
            return true;   // moderators edit and delete in their channel and its sub-channels
        }

        return \Illuminate\Support\Facades\DB::table('channel_user')
            ->where('channel_id', $this->id)->where('user_id', $user->id)
            ->where('status', 'approved')->where('can_' . $ability, true)->exists();
    }

    public function canManageBookings(?User $user): bool
    {
        if (!$user) return false;
        if ($this->canAdminister($user)) return true;
        return (bool) \Illuminate\Support\Facades\DB::table('channel_user')
            ->whereIn('channel_id', array_merge([$this->id], $this->ancestorIds()))
            ->where('user_id',$user->id)->where('status','approved')->where('can_manage_bookings', true)->exists();
    }

    public function getMemberCountAttribute(): int
    {
        return $this->users()->count();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublic($query)
    {
        return $query->where('type', 'public');
    }

    public function scopePrivate($query)
    {
        return $query->where('type', 'private');
    }

    /**
     * Public channels are open to any authenticated user. Private channels
     * require an approved channel_user row (or channel/org admin standing) —
     * a "pending" join request must not grant content access.
     */
    public function canBeAccessedBy(User $user): bool
    {
        if ($this->type !== 'private') {
            return true;
        }

        if ($this->admin_user_id === $user->id || $user->hasRole('super_admin')) {
            return true;
        }

        if (self::isOrgManager($user, $this->organization_id) || $this->roleFor($user) !== null) {
            return true;   // org managers, and members/managers of this channel or an ancestor (ADR-016)
        }

        $membership = $this->users()->where('user_id', $user->id)->first();

        return $membership && $membership->pivot->status === 'approved';
    }

    public function hasPendingRequestFrom(User $user): bool
    {
        $membership = $this->users()->where('user_id', $user->id)->first();

        return $membership && $membership->pivot->status === 'pending';
    }
}
