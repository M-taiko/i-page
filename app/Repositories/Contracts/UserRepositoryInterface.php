<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function delete(User $user): void;

    public function findById(int $id): User;

    public function findByEmail(string $email): ?User;

    public function findByIpageId(string $ipageId): ?User;

    public function getActive();

    public function getVips();

    public function getByChannel(int $channelId);

    public function bulkCreate(array $users);
}
