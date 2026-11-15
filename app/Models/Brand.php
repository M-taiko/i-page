<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'description',
        'logo_path',
        'colors',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'colors' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'brand_followers', 'brand_id', 'user_id')
            ->withTimestamps();
    }

    // ------------------------------------------------ Command center (ADR-015)

    /** Brand admins and moderators assigned by the organization. */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'brand_user')->withPivot('role')->withTimestamps();
    }

    /** Posts published at brand level (no channel). */
    public function brandPosts(): HasMany
    {
        return $this->hasMany(Post::class)->whereNull('channel_id')->whereNotNull('brand_audience');
    }

    /** @return array<int> ids of every channel of this brand */
    public function channelIds(): array
    {
        return $this->channels()->pluck('id')->all();
    }

    private function orgRole(User $user): ?string
    {
        return $user->organizationMemberships()
            ->where('organization_id', $this->organization_id)->where('status', 'active')
            ->value('role');
    }

    private function brandRole(User $user): ?string
    {
        return \Illuminate\Support\Facades\DB::table('brand_user')
            ->where('brand_id', $this->id)->where('user_id', $user->id)->value('role');
    }

    /** Organization admins/managers, super admins and brand admins manage the whole brand. */
    public function isManagedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasRole('super_admin')
            || in_array($this->orgRole($user), ['organization_admin', 'manager'], true)
            || $this->brandRole($user) === 'admin';
    }

    /** Brand admins plus brand moderators. */
    public function isModeratedBy(?User $user): bool
    {
        return $user && ($this->isManagedBy($user) || $this->brandRole($user) === 'moderator');
    }

    /** Only organization owners/admins assign brand managers. */
    public function canAssignManagers(?User $user): bool
    {
        return $user && ($user->hasRole('super_admin') || in_array($this->orgRole($user), ['organization_admin', 'manager'], true));
    }
}
