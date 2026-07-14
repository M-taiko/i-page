<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\PostReceipt;

class PostReceiptService
{
    public function recordDelivery(Post $post, User $user): PostReceipt
    {
        return PostReceipt::updateOrCreate(
            ['post_id' => $post->id, 'user_id' => $user->id],
            ['delivered_at' => now()]
        );
    }

    public function recordView(Post $post, User $user): PostReceipt
    {
        $receipt = PostReceipt::firstOrCreate(
            ['post_id' => $post->id, 'user_id' => $user->id],
            ['delivered_at' => now()]
        );

        if (!$receipt->first_viewed_at) {
            $receipt->update(['first_viewed_at' => now()]);
        }

        return $receipt;
    }

    public function recordRead(Post $post, User $user): PostReceipt
    {
        $receipt = PostReceipt::firstOrCreate(
            ['post_id' => $post->id, 'user_id' => $user->id],
            ['delivered_at' => now(), 'first_viewed_at' => now()]
        );

        $receipt->update(['read_at' => now()]);
        return $receipt;
    }

    public function recordAcknowledgment(Post $post, User $user): PostReceipt
    {
        $receipt = PostReceipt::firstOrCreate(
            ['post_id' => $post->id, 'user_id' => $user->id],
            ['delivered_at' => now(), 'first_viewed_at' => now()]
        );

        $receipt->update(['acknowledged_at' => now()]);
        return $receipt;
    }

    public function getPostStats(Post $post): array
    {
        $receipts = $post->receipts;
        $total = $receipts->count();

        return [
            'total_recipients' => $total,
            'delivered' => $receipts->whereNotNull('delivered_at')->count(),
            'viewed' => $receipts->whereNotNull('first_viewed_at')->count(),
            'read' => $receipts->whereNotNull('read_at')->count(),
            'acknowledged' => $receipts->whereNotNull('acknowledged_at')->count(),
            'view_rate' => $total > 0 ? round(($receipts->whereNotNull('first_viewed_at')->count() / $total) * 100, 2) : 0,
            'read_rate' => $total > 0 ? round(($receipts->whereNotNull('read_at')->count() / $total) * 100, 2) : 0,
            'acknowledgment_rate' => $post->requires_acknowledgment && $total > 0
                ? round(($receipts->whereNotNull('acknowledged_at')->count() / $total) * 100, 2)
                : 0,
        ];
    }
}
