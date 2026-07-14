<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AudienceService
{
    public function getVisiblePosts(User $user, int $organizationId = null): Collection
    {
        $query = Post::where('status', 'published')
            ->whereNotNull('published_at');

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        return $query->get()->filter(function (Post $post) use ($user) {
            return $this->canUserSeePost($user, $post);
        });
    }

    public function canUserSeePost(User $user, Post $post): bool
    {
        // Published posts with no audience restrictions visible to all org members
        if ($post->status !== 'published') {
            return false;
        }

        // User must be member of the post's organization
        if (!$user->organizationMemberships()
            ->where('organization_id', $post->organization_id)
            ->where('status', 'active')
            ->exists()) {
            return false;
        }

        // Check audience targeting
        $audience = $post->audience;
        if (!$audience) {
            return true; // No restrictions
        }

        return $audience->matchesUser($user);
    }

    public function getUsersForPost(Post $post): Collection
    {
        $org = $post->organization;
        $users = $org->users()
            ->whereHas('organizationMemberships', function ($q) use ($org) {
                $q->where('organization_id', $org->id)
                    ->where('status', 'active');
            })
            ->get();

        if (!$post->audience) {
            return $users;
        }

        return $users->filter(function (User $user) use ($post) {
            return $post->audience->matchesUser($user);
        });
    }

    public function getTargetingRulesDescription(Post $post): string
    {
        if (!$post->audience) {
            return 'Visible to all organization members';
        }

        if ($post->audience->segment_id) {
            return "Segment: {$post->audience->segment->name}";
        }

        if ($post->audience->inline_rules) {
            $rules = $post->audience->inline_rules;
            $descriptions = [];

            foreach ($rules as $rule) {
                $scope = $rule['scope'] ?? null;
                $value = $rule['value'] ?? null;

                $descriptions[] = match($scope) {
                    'language' => 'Language: ' . implode(', ', (array)$value),
                    'role' => 'Role: ' . implode(', ', (array)$value),
                    'location' => 'Location ID: ' . implode(', ', (array)$value),
                    'department' => 'Department ID: ' . implode(', ', (array)$value),
                    'brand' => 'Brand ID: ' . implode(', ', (array)$value),
                    default => null,
                };
            }

            return implode(' AND ', array_filter($descriptions));
        }

        return 'Visible to all organization members';
    }
}
