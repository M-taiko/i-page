<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;

class LocationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Location $location): bool
    {
        // User must be a member of the location's organization
        return $user->organizationMemberships()
            ->where('organization_id', $location->organization_id)
            ->where('status', 'active')
            ->exists();
    }

    public function create(User $user): bool
    {
        // Handled in controller context with organization parameter
        return true;
    }

    public function update(User $user, Location $location): bool
    {
        return $user->organizationMemberships()
            ->where('organization_id', $location->organization_id)
            ->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])
            ->exists() || $user->hasRole('super_admin');
    }

    public function delete(User $user, Location $location): bool
    {
        return $this->update($user, $location);
    }

    public function manageMembers(User $user, Location $location): bool
    {
        return $this->update($user, $location);
    }
}
