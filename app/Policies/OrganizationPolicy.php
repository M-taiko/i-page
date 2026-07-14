<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can list their own organizations
    }

    public function view(User $user, Organization $organization): bool
    {
        // Super admin can view any organization
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // User must be a member of the organization
        return $user->organizations()
            ->where('organizations.id', $organization->id)
            ->exists();
    }

    public function create(User $user): bool
    {
        // Only super admins can create organizations
        return $user->hasRole('super_admin');
    }

    public function update(User $user, Organization $organization): bool
    {
        // Super admin can update any organization
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Organization owners/admins can update
        return $user->organizations()
            ->where('organizations.id', $organization->id)
            ->wherePivotIn('role', ['organization_admin', 'manager'])
            ->exists();
    }

    public function delete(User $user, Organization $organization): bool
    {
        // Only super admins can delete organizations
        return $user->hasRole('super_admin');
    }

    public function manageBrands(User $user, Organization $organization): bool
    {
        return $this->update($user, $organization);
    }

    public function manageLocations(User $user, Organization $organization): bool
    {
        return $this->update($user, $organization);
    }

    public function manageMembers(User $user, Organization $organization): bool
    {
        return $this->update($user, $organization);
    }

    public function manageDepartments(User $user, Organization $organization): bool
    {
        return $this->update($user, $organization);
    }

    public function manageChannels(User $user, Organization $organization): bool
    {
        return $this->update($user, $organization);
    }

    public function viewAnalytics(User $user, Organization $organization): bool
    {
        return $this->view($user, $organization);
    }
}
