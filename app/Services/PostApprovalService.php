<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PostApprovalService
{
    public function approve(Post $post, User $approver): Post
    {
        return DB::transaction(function () use ($post, $approver) {
            $post->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            return $post;
        });
    }

    public function reject(Post $post, User $rejector, string $reason = null): Post
    {
        return DB::transaction(function () use ($post, $rejector, $reason) {
            $post->update([
                'status' => 'rejected',
                'approved_by' => $rejector->id,
                'approved_at' => now(),
            ]);

            return $post;
        });
    }

    public function schedule(Post $post, \DateTime $scheduledFor, User $scheduler): Post
    {
        return DB::transaction(function () use ($post, $scheduledFor, $scheduler) {
            $post->update([
                'status' => 'scheduled',
                'scheduled_for' => $scheduledFor,
                'approved_by' => $scheduler->id,
                'approved_at' => now(),
            ]);

            return $post;
        });
    }

    public function publish(Post $post, User $publisher = null): Post
    {
        return DB::transaction(function () use ($post, $publisher) {
            $post->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            return $post;
        });
    }

    public function archive(Post $post, User $archiver): Post
    {
        return DB::transaction(function () use ($post, $archiver) {
            $post->update([
                'status' => 'archived',
            ]);

            return $post;
        });
    }

    public function expire(Post $post): Post
    {
        return DB::transaction(function () use ($post) {
            $post->update([
                'status' => 'expired',
            ]);

            return $post;
        });
    }
}
