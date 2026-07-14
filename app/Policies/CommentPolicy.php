<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comment $comment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Comment author can delete their own; otherwise a moderator of the post's org.
        return $user->id === $comment->user_id || $this->canModerate($user, $comment);
    }

    public function approve(User $user, Comment $comment): bool
    {
        return $this->canModerate($user, $comment);
    }

    /**
     * A comment can be moderated by super admin, the owning channel's admin,
     * or an org admin / manager / moderator of the post's organization.
     */
    private function canModerate(User $user, Comment $comment): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $post = $comment->post;

        if ($post?->channel && $user->id === $post->channel->admin_user_id) {
            return true;
        }

        return $user->organizationMemberships()
            ->where('organization_id', $post?->organization_id)
            ->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager', 'moderator'])
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comment $comment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        return false;
    }
}
