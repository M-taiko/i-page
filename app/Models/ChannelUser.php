<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ChannelUser extends Pivot
{
    public $timestamps = false;

    protected $fillable = [
        'channel_id',
        'user_id',
        'role',
        'status',
        'joined_at',
        'muted_at',
    ];
}
