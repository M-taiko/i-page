<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'industry_key',
        'default_departments',
        'default_channels',
        'default_roles',
        'default_workflows',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_departments' => 'array',
            'default_channels' => 'array',
            'default_roles' => 'array',
            'default_workflows' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }
}
