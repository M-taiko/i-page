<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Post $post): bool
    {
        // Org-level post (no channel): visible to any authenticated user (Layer 3 discovery).
        if (!$post->channel_id) {
            return true;
        }

        // Public channel post: visible to any authenticated user.
        if ($post->channel && $post->channel->type === 'public') {
            return true;
        }

        // Private channel post: only channel subscribers or org members.
        $isSubscriber = $user->channels()->where('channel_id', $post->channel_id)->exists();

        $isOrgMember = $user->organizationMemberships()
            ->where('organization_id', $post->organization_id)
            ->where('status', 'active')
            ->exists();

        return $isSubscriber || $isOrgMember;
    }

    public function create(User $user): bool
    {
        // Organization members can create posts
        return $user->organizationMemberships()
            ->where('status', 'active')
            ->exists();
    }

    public function update(User $user, Post $post): bool
    {
        // Only the post author or org admin can update
        $isAuthor = $post->author_id === $user->id;
        
        $isOrgAdmin = $user->organizationMemberships()
            ->where('organization_id', $post->organization_id)
            ->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])
            ->exists();

        return ($isAuthor || $isOrgAdmin) || $user->hasRole('super_admin');
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }

    public function approve(User $user, Post $post): bool
    {
        return $user->organizationMemberships()
            ->where('organization_id', $post->organization_id)
            ->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])
            ->exists() || $user->hasRole('super_admin');
    }
}
