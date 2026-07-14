<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('user.manage');
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasPermissionTo('user.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('user.manage');
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasPermissionTo('user.manage');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('user.manage') && $user->id !== $model->id;
    }

    public function manageBulk(User $user): bool
    {
        return $user->hasPermissionTo('user.manage');
    }
}
