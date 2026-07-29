<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    public const PROFILE_LEVEL_BASIC = 'basic';
    public const PROFILE_LEVEL_PRIVATE = 'private';
    public const PROFILE_LEVEL_BUSINESS = 'business';

    protected $fillable = [
        'ipage_id',
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'mobile',
        'dob',
        'gender',
        'nationality',
        'city',
        'country',
        'profile_level',
        'job_title',
        'department',
        'location_id',
        'avatar_path',
        'cover_path',
        'is_vip',
        'check_in_at',
        'check_out_at',
        'last_seen_at',
        'theme',
        'language',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'is_vip' => 'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * A user-editable, unique handle (distinct from the system-generated
     * ipage_id) — the basis for future user-to-user search/discovery.
     * Collision-safe: appends a numeric suffix until unique, capped at 30 chars.
     */
    public static function generateUniqueUsername(string $firstName, string $lastName): string
    {
        $base = Str::slug($firstName . '.' . $lastName, '.');
        $base = $base !== '' ? Str::limit($base, 24, '') : 'user';

        $candidate = $base;
        $suffix = 0;

        while (static::where('username', $candidate)->exists()) {
            $suffix++;
            $candidate = Str::limit($base, 24, '') . $suffix;
        }

        return $candidate;
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    public function getDisplayRoleAttribute(): string
    {
        if ($this->hasRole('super_admin')) {
            return 'Super Admin';
        }

        $organization = $this->currentOrganization;
        $organizationRole = $organization
            ? $this->organizations()->where('organizations.id', $organization->id)->first()?->pivot->role
            : null;

        if ($organizationRole) {
            return $this->formatRoleLabel($organizationRole);
        }

        $roleName = $this->roles->first()?->name;

        return $roleName ? $this->formatRoleLabel($roleName) : 'Member';
    }

    public function formatRoleLabel(string $roleName): string
    {
        return match ($roleName) {
            'super_admin' => 'Super Admin',
            'organization_admin' => 'Organization Admin',
            'manager' => 'Manager',
            'moderator' => 'Moderator',
            'staff' => 'Staff',
            'member' => 'Member',
            'guest' => 'Guest',
            default => ucfirst(str_replace('_', ' ', $roleName)),
        };
    }

    /**
     * Business tier is earned by holding any organization membership
     * (business/staff data itself lives on organization_memberships, not
     * here). Private tier requires the full personal-detail set. Otherwise
     * Basic — name/phone/email is all that's on file.
     */
    public function computeProfileLevel(): string
    {
        if ($this->organizationMemberships()->where('status', 'active')->exists()) {
            return self::PROFILE_LEVEL_BUSINESS;
        }

        if ($this->dob && $this->gender && $this->nationality && ($this->city || $this->country)) {
            return self::PROFILE_LEVEL_PRIVATE;
        }

        return self::PROFILE_LEVEL_BASIC;
    }

    public function refreshProfileLevel(): void
    {
        $level = $this->computeProfileLevel();

        if ($level !== $this->profile_level) {
            $this->update(['profile_level' => $level]);
        }
    }

    public function getProfileLevelLabelAttribute(): string
    {
        return match ($this->profile_level) {
            self::PROFILE_LEVEL_PRIVATE => 'Private Profile',
            self::PROFILE_LEVEL_BUSINESS => 'Business Profile',
            default => 'Basic Profile',
        };
    }

    /**
     * Canonical "current organization" resolution used everywhere:
     * session-selected org (if the user belongs to it), else first active membership.
     *
     * Super admins have no organization_membership by design (Layer 1 is
     * global), but still need a tenant workspace to manage any organization's
     * channels/posts directly — so for them the session selection is resolved
     * against ALL organizations, not just their (nonexistent) memberships.
     */
    public function getCurrentOrganizationAttribute(): ?Organization
    {
        $sessionId = session('current_organization_id');
        $isSuperAdmin = $this->hasRole('super_admin');

        if ($sessionId) {
            $selected = $isSuperAdmin
                ? Organization::find($sessionId)
                : $this->organizations()->where('organizations.id', $sessionId)->first();

            if ($selected) {
                return $selected;
            }
        }

        if ($isSuperAdmin) {
            return Organization::orderBy('name')->first();
        }

        return $this->organizations()
            ->wherePivot('status', 'active')
            ->first()
            ?? $this->organizations()->first();
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * @deprecated Use location() instead
     */
    public function branch()
    {
        return $this->location();
    }

    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'channel_user')
            ->withPivot('role', 'status', 'joined_at', 'muted_at')
            ->using(ChannelUser::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')
            ->withPivot('position', 'joined_at');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
    }

    public function adminChannels()
    {
        return $this->hasMany(Channel::class, 'admin_user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function organizationMemberships()
    {
        return $this->hasMany(OrganizationMembership::class);
    }

    public function locationMemberships()
    {
        return $this->hasMany(LocationMembership::class);
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_memberships')
            ->withPivot('role', 'status', 'job_title', 'department_id')
            ->withTimestamps();
    }

    /**
     * Get membership for a specific organization
     */
    public function membershipFor(Organization $organization): ?OrganizationMembership
    {
        return $this->organizationMemberships()
            ->where('organization_id', $organization->id)
            ->first();
    }

    public function subscribedChannels()
    {
        return $this->belongsToMany(Channel::class, 'channel_user')
            ->withPivot('role', 'status', 'joined_at', 'muted_at')
            ->using(ChannelUser::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function followedOrganizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_followers', 'user_id', 'organization_id');
    }

    public function followedBrands()
    {
        return $this->belongsToMany(Brand::class, 'brand_followers', 'user_id', 'brand_id')
            ->withTimestamps();
    }

    public function collections()
    {
        return $this->hasMany(UserCollection::class);
    }
}
