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
        'brand_id',
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

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'channel_user')
            ->withPivot('role', 'status', 'joined_at', 'muted_at', 'can_post', 'can_edit', 'can_delete')
            ->using(ChannelUser::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
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

    /** Posts shown in this channel: its own plus cascading posts of its ancestors. */
    public function feedQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $inherited = $this->inheritableAncestorIds();

        return Post::query()->where(function ($q) use ($inherited) {
            $q->where('channel_id', $this->id);
            if ($inherited) {
                $q->orWhere(fn ($w) => $w->whereIn('channel_id', $inherited)->where('cascade', true));
            }
            $this->orWhereBrandPost($q);
        });
    }

    /** Brand posts reaching this channel (ADR-015): public channels never get private-audience posts. */
    private function orWhereBrandPost($q): void
    {
        if (! $this->brand_id) {
            return;
        }

        $q->orWhere(fn ($w) => $w->whereNull('channel_id')
            ->where('brand_id', $this->brand_id)
            ->whereIn('brand_audience', ['all', $this->type === 'private' ? 'private' : 'public']));
    }

    /**
     * Currently pinned, published posts for the highlights strip: this channel's own pins, plus
     * ancestor pins whose scope is "all" (options.pin_scope; missing = all). Independent of the
     * post's own cascade flag, so a post can be highlighted everywhere while staying in one feed.
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
                $this->orWhereBrandPost($q);
            })
            ->where('status', 'published')
            ->where('pinned_until', '>', now())
            ->with(['media', 'channel', 'brand'])
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

    /** Whether the user manages this channel outright (all permissions). */
    public function isManagedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin') || $this->admin_user_id === $user->id) {
            return true;
        }

        if ($this->brand && $this->brand->isManagedBy($user)) {
            return true;   // brand admins manage every channel of their brand (ADR-015)
        }

        return $user->organizationMemberships()
            ->where('organization_id', $this->organization_id)->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])->exists();
    }

    /** Approved member of this channel. */
    public function hasMember(?User $user): bool
    {
        return $user && \Illuminate\Support\Facades\DB::table('channel_user')
            ->where('channel_id', $this->id)->where('user_id', $user->id)->where('status', 'approved')->exists();
    }

    /**
     * Who may see this channel's member list (ADR-015): managers of the channel/brand/organization,
     * brand moderators, and the channel's own approved members. Nobody else.
     */
    public function membersVisibleTo(?User $user): bool
    {
        return $this->isManagedBy($user)
            || ($this->brand && $this->brand->isModeratedBy($user))
            || $this->hasMember($user);
    }

    /** Settings, members and QR codes: managers plus the channel's own admins (channel_user role admin). */
    public function canAdminister(?User $user): bool
    {
        return $this->isManagedBy($user) || ($user && \Illuminate\Support\Facades\DB::table('channel_user')
            ->where('channel_id', $this->id)->where('user_id', $user->id)
            ->where('status', 'approved')->where('role', 'admin')->exists());
    }

    /**
     * Channels of $organizationId that $user may open in the dashboard (ADR-015): everything for
     * organization admins/managers and super admins; otherwise the channels they are an approved
     * member or admin of, plus every channel of brands they manage or moderate.
     */
    public function scopeVisibleTo($query, User $user, int $organizationId)
    {
        $query->where('organization_id', $organizationId);

        $orgManager = $user->hasRole('super_admin') || $user->organizationMemberships()
            ->where('organization_id', $organizationId)->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])->exists();
        if ($orgManager) {
            return $query;
        }

        $db = \Illuminate\Support\Facades\DB::class;
        $memberOf = $db::table('channel_user')->where('user_id', $user->id)->where('status', 'approved')->pluck('channel_id');
        $brands = $db::table('brand_user')->where('user_id', $user->id)->pluck('brand_id');

        return $query->where(fn ($q) => $q->whereIn('id', $memberOf)
            ->orWhere('admin_user_id', $user->id)
            ->orWhereIn('brand_id', $brands));
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

        if ($ability !== 'post' && $this->brand && $this->brand->isModeratedBy($user)) {
            return true;   // brand moderators edit and delete across the brand
        }

        return \Illuminate\Support\Facades\DB::table('channel_user')
            ->where('channel_id', $this->id)->where('user_id', $user->id)
            ->where('status', 'approved')->where('can_' . $ability, true)->exists();
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

        if ($user->membershipFor($this->organization)?->role === 'organization_admin') {
            return true;
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
