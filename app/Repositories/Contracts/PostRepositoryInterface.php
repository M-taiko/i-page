<?php

namespace App\Repositories\Contracts;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

interface PostRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function create(array $data): Post;

    public function update(Post $post, array $data): Post;

    public function delete(Post $post): void;

    public function findById(int $id): Post;

    public function getPublished();

    public function getByChannel(int $channelId);

    public function getByAudience(string $audience);

    public function getToday();

    public function getPending();

    public function publish(Post $post): Post;
}
