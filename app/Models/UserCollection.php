<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A user's personal folder for organizing their subscribed channels.
 * Purely personal — never shared, never affects permissions.
 */
class UserCollection extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'color',
        'sort_order',
        'is_pinned',
        'is_muted',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_muted' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'user_collection_channels', 'collection_id', 'channel_id')
            ->withTimestamps();
    }
}
