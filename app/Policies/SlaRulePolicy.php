<?php

namespace App\Policies;

use App\Models\SlaRule;
use App\Models\User;

class SlaRulePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SlaRule $rule): bool
    {
        return $user->organizationMemberships()
            ->where('organization_id', $rule->organization_id)
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

    public function update(User $user, SlaRule $rule): bool
    {
        return $user->organizationMemberships()
            ->where('organization_id', $rule->organization_id)
            ->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])
            ->exists() || $user->hasRole('super_admin');
    }

    public function delete(User $user, SlaRule $rule): bool
    {
        return $this->update($user, $rule);
    }
}
