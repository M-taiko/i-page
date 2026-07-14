<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AudienceSegment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'rules',
        'is_active',
    ];

    protected $casts = [
        'rules' => 'array',
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function matchesUser(User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        foreach ($this->rules as $rule) {
            if (!$this->evaluateRule($rule, $user)) {
                return false;
            }
        }
        return true;
    }

    private function evaluateRule(array $rule, User $user): bool
    {
        $type = $rule['type'] ?? $rule['scope'] ?? null;
        $values = (array)($rule['value'] ?? []);

        return match($type) {
            'role' => $this->checkUserRole($user, $values),
            'language' => in_array($user->language ?? 'en', $values),
            'location', 'location_id' => $this->checkUserLocations($user, $values),
            'department', 'department_id' => $this->checkUserDepartments($user, $values),
            'brand', 'brand_id' => $this->checkUserBrands($user, $values),
            default => false,
        };
    }

    private function checkUserRole(User $user, array $roleIds): bool
    {
        return $user->organizationMemberships()
            ->where('organization_id', $this->organization_id)
            ->whereIn('role', $roleIds)
            ->exists();
    }

    private function checkUserLocations(User $user, array $locationIds): bool
    {
        return $user->locationMemberships()
            ->whereIn('location_id', $locationIds)
            ->exists();
    }

    private function checkUserDepartments(User $user, array $departmentIds): bool
    {
        return $user->organizationMemberships()
            ->where('organization_id', $this->organization_id)
            ->whereIn('department_id', $departmentIds)
            ->exists();
    }

    private function checkUserBrands(User $user, array $brandIds): bool
    {
        return $user->locationMemberships()
            ->whereHas('location', function ($q) use ($brandIds) {
                $q->whereIn('brand_id', $brandIds);
            })
            ->exists();
    }
}
