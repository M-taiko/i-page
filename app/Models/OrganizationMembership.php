<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationMembership extends Model
{
    use SoftDeletes;

    protected $table = 'organization_memberships';

    protected $fillable = [
        'organization_id',
        'user_id',
        'role',
        'employee_id',
        'job_title',
        'employment_type',
        'department_id',
        'manager_user_id',
        'primary_location_id',
        'status',
        'joined_date',
        'invited_by',
        'business_verified_at',
        'business_verified_by',
    ];

    protected $casts = [
        'joined_date' => 'datetime',
        'business_verified_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function primaryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'primary_location_id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function businessVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_verified_by');
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInvited(): bool
    {
        return $this->status === 'invited';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isBusinessVerified(): bool
    {
        return $this->business_verified_at !== null;
    }

    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    public function suspend(): void
    {
        $this->update(['status' => 'suspended']);
    }

    public function deactivate(): void
    {
        $this->update(['status' => 'inactive']);
    }
}
