<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostApprovalPolicy
{
    public function approve(User $user, Post $post): bool
    {
        // Only org admins or super admins can approve posts
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->organizationMemberships()
            ->where('organization_id', $post->organization_id)
            ->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])
            ->exists();
    }

    public function reject(User $user, Post $post): bool
    {
        return $this->approve($user, $post);
    }

    public function schedule(User $user, Post $post): bool
    {
        return $this->approve($user, $post);
    }
}
