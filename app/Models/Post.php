<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Post extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'author_id',
        'channel_id',
        'organization_id',
        'brand_id',
        'location_id',
        'title',
        'summary',
        'body',
        'image_path',
        'post_type',
        'post_format',
        'card_style',
        'badge_key',
        'badge_label',
        'options',
        'priority',
        'language',
        'requires_acknowledgment',
        'is_emergency',
        'status',
        'approved_by',
        'approved_at',
        'scheduled_for',
        'published_at',
        'pinned_until',
        'audience',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'pinned_until' => 'datetime',
        'approved_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'requires_acknowledgment' => 'boolean',
        'is_emergency' => 'boolean',
        'options' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PostReceipt::class);
    }

    public function audience(): HasOne
    {
        return $this->hasOne(PostAudience::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'owner');
    }

    // ---------------------------------------------------- Formats (ADR-013)

    public function pollOptions(): HasMany
    {
        return $this->hasMany(PostPollOption::class)->orderBy('sort_order');
    }

    public function pollVotes(): HasMany
    {
        return $this->hasMany(PostPollVote::class);
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(PostRsvp::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(PostRating::class);
    }

    /** A value from the per-format settings JSON, with a default. */
    public function option(string $key, mixed $default = null): mixed
    {
        return data_get($this->options ?? [], $key, $default);
    }

    /** Gallery images (media rows), in upload order. */
    public function galleryImages()
    {
        return $this->media->where('usage_type', 'gallery')->sortBy('id')->values();
    }

    /** First image to use as a thumbnail: the gallery's first photo, else the single image. */
    public function coverUrl(): ?string
    {
        $first = $this->galleryImages()->first();
        if ($first) {
            return \Illuminate\Support\Facades\Storage::disk($first->disk)->url($first->path);
        }

        return $this->image_path ? \Illuminate\Support\Facades\Storage::url($this->image_path) : null;
    }

    /** Reaction keys enabled for this post (defaults to like only). */
    public function enabledReactions(): array
    {
        $all = array_keys(config('posts.reactions'));
        $enabled = array_values(array_intersect((array) $this->option('reactions', ['like']), $all));

        return $enabled ?: ['like'];
    }

    /** Badge as [label, color, icon] or null. */
    public function badge(): ?array
    {
        if (! $this->badge_key || ! isset(config('posts.badges')[$this->badge_key])) {
            return null;
        }

        [$label, $color, $icon] = config('posts.badges')[$this->badge_key];

        return ['label' => $this->badge_label ?: __($label), 'color' => $color, 'icon' => $icon];
    }

    /** True when the post shows the interaction widgets instead of the plain like button. */
    public function hasWidgets(): bool
    {
        return ($this->post_format ?: 'standard') !== 'standard' || $this->enabledReactions() !== ['like'];
    }

    public function pollClosed(): bool
    {
        $ends = $this->option('poll.ends_at');

        return $ends && \Illuminate\Support\Carbon::parse($ends)->isPast();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    public function scopeForAudience($query, string $audience)
    {
        return $query->where('audience', $audience);
    }

    public function isPinned(): bool
    {
        return $this->pinned_until && $this->pinned_until->isFuture();
    }

    /**
     * Record that $user has seen this post. Idempotent — a post viewed
     * multiple times by the same user only ever counts once.
     */
    public function recordViewFor(User $user): void
    {
        $receipt = PostReceipt::firstOrCreate(
            ['post_id' => $this->id, 'user_id' => $user->id],
            ['delivered_at' => now()]
        );

        if (!$receipt->first_viewed_at) {
            $receipt->update(['first_viewed_at' => now()]);
        }
    }

    public function viewsCount(): int
    {
        return $this->receipts()->whereNotNull('first_viewed_at')->count();
    }

    /**
     * Engagement funnel for this post, derived from post_receipts.
     */
    public function getStats(): array
    {
        return [
            'total_recipients' => $this->receipts()->count(),
            'delivered' => $this->receipts()->whereNotNull('delivered_at')->count(),
            'viewed' => $this->receipts()->whereNotNull('first_viewed_at')->count(),
            'read' => $this->receipts()->whereNotNull('read_at')->count(),
            'acknowledged' => $this->receipts()->whereNotNull('acknowledged_at')->count(),
        ];
    }
}
