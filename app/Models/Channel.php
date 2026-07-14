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
    ];

    protected $casts = [
        'audience_count' => 'integer',
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
            ->withPivot('role', 'joined_at', 'muted_at')
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
}
