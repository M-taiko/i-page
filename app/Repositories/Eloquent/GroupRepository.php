<?php

namespace App\Repositories\Eloquent;

use App\Models\Group;
use App\Repositories\Contracts\GroupRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class GroupRepository implements GroupRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return Group::query()
            ->when($filters['q'] ?? null, fn($q, $v) => $q->where('name', 'like', "%$v%"))
            ->when($filters['location_id'] ?? null, fn($q, $v) => $q->where('location_id', $v))
            ->with('users', 'location')
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Group
    {
        return Group::create($data);
    }

    public function update(Group $group, array $data): Group
    {
        $group->update($data);
        return $group->refresh();
    }

    public function delete(Group $group): void
    {
        $group->delete();
    }

    public function findById(int $id): Group
    {
        return Group::findOrFail($id);
    }

    public function getByLocation(int $locationId)
    {
        return Group::where('location_id', $locationId)->get();
    }

    /**
     * @deprecated Use getByLocation() instead
     */
    public function getByBranch(int $branchId)
    {
        return $this->getByLocation($branchId);
    }

    public function addMembers(Group $group, array $userIds)
    {
        return $group->users()->attach($userIds);
    }

    public function removeMembers(Group $group, array $userIds)
    {
        return $group->users()->detach($userIds);
    }
}
