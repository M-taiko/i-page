<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

/**
 * In-app notifications for newly published channel and tenant posts (ADR-016).
 * Managers (channel admins/moderators of the receiving channels or of any ancestor, and
 * organization admins/managers) get a management notice, subscribers a new-post notice;
 * each user at most once, never the author, never members who muted the channel.
 */
class PostNotificationService
{
    private const CHUNK = 500;

    /** @return array{managers:int, subscribers:int} number of notifications written */
    public function published(Post $post): array
    {
        if ($post->status !== 'published') {
            return ['managers' => 0, 'subscribers' => 0];
        }

        $channelIds = $this->targetChannelIds($post);
        $organizationId = $post->organization_id ?? $post->channel?->organization_id;

        $managerIds = $this->managerIds($channelIds, $organizationId);
        $subscriberIds = $channelIds ? DB::table('channel_user')
            ->whereIn('channel_id', $channelIds)
            ->where('status', 'approved')->whereNull('muted_at')
            ->distinct()->pluck('user_id')->all() : [];

        $exclude = [$post->author_id];
        $managerIds = array_values(array_diff(array_unique($managerIds), $exclude));
        $subscriberIds = array_values(array_diff(array_unique($subscriberIds), $managerIds, $exclude));

        $source = $post->sourceName() ?? config('app.name');
        $headline = $post->title ?: \Illuminate\Support\Str::limit($post->body, 60);
        $link = route('guest.post-detail', $post->id);

        $this->insert($managerIds, 'post.published.manage', [
            'message' => __('New post in :source by :author: :title', ['source' => $source, 'author' => $post->author?->full_name ?? __('System'), 'title' => $headline]),
            'link' => $link,
            'post_id' => $post->id,
            'channels' => count($channelIds),
        ]);
        $this->insert($subscriberIds, 'post.published', [
            'message' => __(':source: :title', ['source' => $source, 'title' => $headline]),
            'link' => $link,
            'post_id' => $post->id,
        ]);

        return ['managers' => count($managerIds), 'subscribers' => count($subscriberIds)];
    }

    /** Channels the post appears in. */
    public function targetChannelIds(Post $post): array
    {
        return $post->receivingChannelIds();
    }

    private function managerIds(array $channelIds, ?int $organizationId): array
    {
        $ids = [];
        if ($channelIds) {
            // Roles inherit down the tree, so managers of any ancestor are managers here too.
            $scope = array_values(array_unique(array_merge($channelIds, $this->ancestorIdsOf($channelIds))));
            $ids = DB::table('channel_user')->whereIn('channel_id', $scope)
                ->where('status', 'approved')->whereIn('role', ['admin', 'moderator'])->pluck('user_id')->all();
            $ids = array_merge($ids, Channel::whereIn('id', $channelIds)->whereNotNull('admin_user_id')->pluck('admin_user_id')->all());
        }

        if ($organizationId) {
            $ids = array_merge($ids, DB::table('organization_memberships')
                ->where('organization_id', $organizationId)
                ->where('status', 'active')->whereIn('role', ['organization_admin', 'manager'])
                ->whereNull('deleted_at')->pluck('user_id')->all());
        }

        return $ids;
    }

    private function ancestorIdsOf(array $ids): array
    {
        $seen = $ids;
        $frontier = $ids;
        while ($frontier) {
            $parents = DB::table('channel_channel')->whereIn('child_channel_id', $frontier)->pluck('parent_channel_id')->all();
            $frontier = array_values(array_diff(array_unique($parents), $seen));
            $seen = array_merge($seen, $frontier);
        }

        return array_values(array_diff($seen, $ids));
    }

    private function insert(array $userIds, string $type, array $data): void
    {
        $now = now();
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);

        foreach (array_chunk($userIds, self::CHUNK) as $chunk) {
            DB::table('notifications')->insert(array_map(fn ($id) => [
                'user_id' => $id, 'type' => $type, 'data' => $json, 'read_at' => null, 'created_at' => $now, 'updated_at' => $now,
            ], $chunk));
        }
    }
}
