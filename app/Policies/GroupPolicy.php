<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('group.manage');
    }

    public function view(User $user, Group $group): bool
    {
        return $user->hasPermissionTo('group.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('group.manage');
    }

    public function update(User $user, Group $group): bool
    {
        return $user->hasPermissionTo('group.manage');
    }

    public function delete(User $user, Group $group): bool
    {
        return $user->hasPermissionTo('group.manage');
    }

    public function manage(User $user, Group $group): bool
    {
        return $user->hasPermissionTo('group.manage');
    }
}
