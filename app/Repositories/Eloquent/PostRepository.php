<?php

namespace App\Repositories\Eloquent;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class PostRepository implements PostRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return Post::query()
            ->when($filters['organization_id'] ?? null, fn($q, $v) => $q->where('organization_id', $v))
            ->when($filters['channel_id'] ?? null, fn($q, $v) => $q->where('channel_id', $v))
            ->when($filters['audience'] ?? null, fn($q, $v) => $q->where('audience', $v))
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->with('author', 'reactions')
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Post
    {
        return Post::create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);
        return $post->refresh();
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }

    public function findById(int $id): Post
    {
        return Post::findOrFail($id);
    }

    public function getPublished()
    {
        return Post::published()->get();
    }

    public function getByChannel(int $channelId)
    {
        return Post::where('channel_id', $channelId)->published()->get();
    }

    public function getByAudience(string $audience)
    {
        return Post::where('audience', $audience)->published()->get();
    }

    public function getToday()
    {
        return Post::today()->published()->get();
    }

    public function getPending()
    {
        return Post::where('status', 'pending_approval')->get();
    }

    public function publish(Post $post): Post
    {
        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
        return $post->refresh();
    }
}
