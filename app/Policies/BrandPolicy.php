<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;

class BrandPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Brand $brand): bool
    {
        // User must be a member of the brand's organization
        return $user->organizationMemberships()
            ->where('organization_id', $brand->organization_id)
            ->where('status', 'active')
            ->exists();
    }

    public function create(User $user): bool
    {
        // Organization admins can create brands
        return $user->organizationMemberships()
            ->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])
            ->exists() || $user->hasRole('super_admin');
    }

    public function update(User $user, Brand $brand): bool
    {
        // Only organization admins can update brands
        return $user->organizationMemberships()
            ->where('organization_id', $brand->organization_id)
            ->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])
            ->exists() || $user->hasRole('super_admin');
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $this->update($user, $brand);
    }
}
