<?php

namespace App\Repositories\Contracts;

use App\Models\Group;
use Illuminate\Pagination\LengthAwarePaginator;

interface GroupRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function create(array $data): Group;

    public function update(Group $group, array $data): Group;

    public function delete(Group $group): void;

    public function findById(int $id): Group;

    public function getByBranch(int $branchId);

    public function addMembers(Group $group, array $userIds);

    public function removeMembers(Group $group, array $userIds);
}
