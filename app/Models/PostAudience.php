<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostAudience extends Model
{
    protected $fillable = [
        'post_id',
        'segment_id',
        'inline_rules',
    ];

    protected $casts = [
        'inline_rules' => 'array',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(AudienceSegment::class);
    }

    public function matchesUser(User $user): bool
    {
        if ($this->segment_id) {
            return $this->segment->matchesUser($user);
        }

        if ($this->inline_rules) {
            $segment = new AudienceSegment(['rules' => $this->inline_rules, 'is_active' => true]);
            return $segment->matchesUser($user);
        }

        return true; // No restrictions = everyone
    }
}
