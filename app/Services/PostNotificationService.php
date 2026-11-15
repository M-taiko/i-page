<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Channel;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

/**
 * In-app notifications for newly published channel and brand posts (ADR-015).
 * Managers get a management notice, subscribers a new-post notice; each user at most once,
 * never the author, never members who muted the channel.
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
        if (! $channelIds) {
            return ['managers' => 0, 'subscribers' => 0];
        }

        $managerIds = $this->managerIds($post, $channelIds);
        $subscriberIds = DB::table('channel_user')
            ->whereIn('channel_id', $channelIds)
            ->where('status', 'approved')->whereNull('muted_at')
            ->distinct()->pluck('user_id')->all();

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
        if ($post->isBrandPost()) {
            return Channel::where('brand_id', $post->brand_id)
                ->when($post->brand_audience === 'public', fn ($q) => $q->where('type', 'public'))
                ->when($post->brand_audience === 'private', fn ($q) => $q->where('type', 'private'))
                ->pluck('id')->all();
        }

        $source = $post->channel;
        if (! $source) {
            return [];
        }
        if (! $post->cascade) {
            return [$source->id];
        }

        // Descendants, any depth (cycle-safe). Mirrors Channel::inheritableAncestorIds():
        // a public channel never shows posts from a private ancestor.
        $ids = [$source->id];
        $frontier = [$source->id];
        while ($frontier) {
            $children = DB::table('channel_channel')->whereIn('parent_channel_id', $frontier)->pluck('child_channel_id')->all();
            $frontier = array_values(array_diff(array_unique($children), $ids));
            $ids = array_merge($ids, $frontier);
        }
        $descendants = array_diff($ids, [$source->id]);
        if ($source->type === 'private' && $descendants) {
            $descendants = Channel::whereIn('id', $descendants)->where('type', 'private')->pluck('id')->all();
        }

        return array_values(array_merge([$source->id], $descendants));
    }

    private function managerIds(Post $post, array $channelIds): array
    {
        $ids = DB::table('channel_user')->whereIn('channel_id', $channelIds)
            ->where('status', 'approved')->whereIn('role', ['admin', 'moderator'])->pluck('user_id')->all();
        $ids = array_merge($ids, Channel::whereIn('id', $channelIds)->whereNotNull('admin_user_id')->pluck('admin_user_id')->all());

        $brandIds = Channel::whereIn('id', $channelIds)->whereNotNull('brand_id')->distinct()->pluck('brand_id')->all();
        if ($post->brand_id) {
            $brandIds[] = $post->brand_id;
        }
        $ids = array_merge($ids, DB::table('brand_user')->whereIn('brand_id', array_unique($brandIds))->pluck('user_id')->all());

        $ids = array_merge($ids, DB::table('organization_memberships')
            ->where('organization_id', $post->organization_id ?? Brand::find($post->brand_id)?->organization_id)
            ->where('status', 'active')->whereIn('role', ['organization_admin', 'manager'])
            ->whereNull('deleted_at')->pluck('user_id')->all());

        return $ids;
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
