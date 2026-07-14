<?php

namespace App\Policies;

use App\Models\AudienceSegment;
use App\Models\User;

class AudienceSegmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AudienceSegment $segment): bool
    {
        return $user->organizationMemberships()
            ->where('organization_id', $segment->organization_id)
            ->where('status', 'active')
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->organizationMemberships()
            ->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])
            ->exists() || $user->hasRole('super_admin');
    }

    public function update(User $user, AudienceSegment $segment): bool
    {
        return $user->organizationMemberships()
            ->where('organization_id', $segment->organization_id)
            ->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])
            ->exists() || $user->hasRole('super_admin');
    }

    public function delete(User $user, AudienceSegment $segment): bool
    {
        return $this->update($user, $segment);
    }
}
