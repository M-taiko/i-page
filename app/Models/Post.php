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
