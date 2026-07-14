<?php

namespace App\Repositories\Contracts;

use App\Models\Channel;
use Illuminate\Pagination\LengthAwarePaginator;

interface ChannelRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function create(array $data): Channel;

    public function update(Channel $channel, array $data): Channel;

    public function delete(Channel $channel): void;

    public function findById(int $id): Channel;

    public function findBySlug(string $slug): ?Channel;

    public function getActive();

    public function getPublic();

    public function getPrivate();
}
