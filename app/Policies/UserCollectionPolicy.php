<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserCollection;

class UserCollectionPolicy
{
    public function view(User $user, UserCollection $collection): bool
    {
        return $user->id === $collection->user_id;
    }

    public function update(User $user, UserCollection $collection): bool
    {
        return $user->id === $collection->user_id;
    }

    public function delete(User $user, UserCollection $collection): bool
    {
        return $user->id === $collection->user_id;
    }
}
