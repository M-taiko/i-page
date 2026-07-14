<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return User::query()
            ->when($filters['organization_id'] ?? null, fn($q, $v) => $q->whereHas('organizations', fn($sq) => $sq->where('organizations.id', $v)))
            ->when($filters['q'] ?? null, fn($q, $v) => $q->where('email', 'like', "%$v%")
                ->orWhere('first_name', 'like', "%$v%")
                ->orWhere('last_name', 'like', "%$v%"))
            ->when($filters['gender'] ?? null, fn($q, $v) => $q->where('gender', $v))
            ->when($filters['nationality'] ?? null, fn($q, $v) => $q->where('nationality', $v))
            ->when($filters['channel_id'] ?? null, fn($q, $v) => $q->whereHas('channels', fn($sq) => $sq->where('channels.id', $v)))
            ->distinct()
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->refresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function findById(int $id): User
    {
        return User::findOrFail($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByIpageId(string $ipageId): ?User
    {
        return User::where('ipage_id', $ipageId)->first();
    }

    public function getActive()
    {
        return User::whereNull('deleted_at')->get();
    }

    public function getVips()
    {
        return User::where('is_vip', true)->get();
    }

    public function getByChannel(int $channelId)
    {
        return User::whereHas('channels', fn($q) => $q->where('channel_id', $channelId))->get();
    }

    public function bulkCreate(array $users)
    {
        return User::insert($users);
    }
}
