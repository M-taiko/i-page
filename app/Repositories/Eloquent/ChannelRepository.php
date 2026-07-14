<?php

namespace App\Repositories\Eloquent;

use App\Models\Channel;
use App\Repositories\Contracts\ChannelRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ChannelRepository implements ChannelRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return Channel::query()
            ->when($filters['q'] ?? null, fn($q, $v) => $q->where('name', 'like', "%$v%"))
            ->when($filters['type'] ?? null, fn($q, $v) => $q->where('type', $v))
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->with('admin')
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Channel
    {
        return Channel::create($data);
    }

    public function update(Channel $channel, array $data): Channel
    {
        $channel->update($data);
        return $channel->refresh();
    }

    public function delete(Channel $channel): void
    {
        $channel->delete();
    }

    public function findById(int $id): Channel
    {
        return Channel::findOrFail($id);
    }

    public function findBySlug(string $slug): ?Channel
    {
        return Channel::where('slug', $slug)->first();
    }

    public function getActive()
    {
        return Channel::active()->get();
    }

    public function getPublic()
    {
        return Channel::public()->get();
    }

    public function getPrivate()
    {
        return Channel::private()->get();
    }
}
