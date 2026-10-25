<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostPollVote;
use App\Models\PostRating;
use App\Models\PostRsvp;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Rules for the non-like/comment interactions (ADR-013). Throws \DomainException with a
 * user-facing message when an interaction is not allowed.
 */
class PostInteractionService
{
    /** @param array<int> $optionIds */
    public function vote(Post $post, User $user, array $optionIds): void
    {
        $this->requireFormat($post, 'poll');

        if ($post->pollClosed()) {
            throw new \DomainException(__('This poll has ended.'));
        }

        $optionIds = array_values(array_unique(array_map('intval', $optionIds)));
        $valid = $post->pollOptions()->whereIn('id', $optionIds)->pluck('id')->all();

        if (! $valid || count($valid) !== count($optionIds)) {
            throw new \DomainException(__('Please choose a valid option.'));
        }

        $multiple = (bool) $post->option('poll.multiple', false);
        if (! $multiple && count($valid) > 1) {
            throw new \DomainException(__('You can only choose one option.'));
        }

        $already = PostPollVote::where('post_id', $post->id)->where('user_id', $user->id)->exists();
        if ($already) {
            throw new \DomainException(__('You have already voted.'));
        }

        DB::transaction(function () use ($post, $user, $valid) {
            foreach ($valid as $optionId) {
                PostPollVote::create(['post_id' => $post->id, 'option_id' => $optionId, 'user_id' => $user->id]);
            }
        });
    }

    public function rsvp(Post $post, User $user, string $status): void
    {
        $this->requireFormat($post, 'event');

        if (! array_key_exists($status, config('posts.rsvp'))) {
            throw new \DomainException(__('Invalid answer.'));
        }

        DB::transaction(function () use ($post, $user, $status) {
            // Lock the event's RSVP rows so concurrent "going" answers cannot exceed the capacity.
            $post->rsvps()->lockForUpdate()->get();

            $capacity = $post->option('event.capacity');
            if ($status === 'going' && $capacity) {
                $going = $post->rsvps()->where('status', 'going')->where('user_id', '!=', $user->id)->count();
                if ($going >= (int) $capacity) {
                    throw new \DomainException(__('This event is full.'));
                }
            }

            PostRsvp::updateOrCreate(['post_id' => $post->id, 'user_id' => $user->id], ['status' => $status]);
        });
    }

    public function rate(Post $post, User $user, int $rating): void
    {
        $this->requireFormat($post, 'rating');

        if ($rating < 1 || $rating > 5) {
            throw new \DomainException(__('Rating must be between 1 and 5.'));
        }

        PostRating::updateOrCreate(['post_id' => $post->id, 'user_id' => $user->id], ['rating' => $rating]);
    }

    /** Set, change or (same type again) remove the user's reaction. Returns the new type or null. */
    public function react(Post $post, User $user, string $type): ?string
    {
        if (! in_array($type, $post->enabledReactions(), true)) {
            throw new \DomainException(__('This reaction is not available for this post.'));
        }

        $existing = Reaction::where('post_id', $post->id)->where('user_id', $user->id)->get();
        $same = $existing->firstWhere('type', $type);

        Reaction::where('post_id', $post->id)->where('user_id', $user->id)->delete();

        if ($same) {
            return null;
        }

        Reaction::create(['post_id' => $post->id, 'user_id' => $user->id, 'type' => $type]);

        return $type;
    }

    private function requireFormat(Post $post, string $format): void
    {
        if (($post->post_format ?? 'standard') !== $format) {
            throw new \DomainException(__('This post does not support that action.'));
        }
    }
}
