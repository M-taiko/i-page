<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostReceipt extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'delivered_at',
        'first_viewed_at',
        'read_at',
        'acknowledged_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'first_viewed_at' => 'datetime',
        'read_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markViewed(): self
    {
        if (!$this->first_viewed_at) {
            $this->update(['first_viewed_at' => now()]);
        }
        return $this;
    }

    public function markRead(): self
    {
        $this->update(['read_at' => now()]);
        return $this;
    }

    public function markAcknowledged(): self
    {
        $this->update(['acknowledged_at' => now()]);
        return $this;
    }
}
