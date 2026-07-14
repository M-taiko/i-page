<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'locations';

    protected $fillable = [
        'organization_id',
        'brand_id',
        'name',
        'city',
        'country',
        'timezone',
        'location_type',
        'latitude',
        'longitude',
        'opening_date',
    ];

    protected $casts = [
        'opening_date' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'location_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'location_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'location_id');
    }

    public function qrCodes(): MorphMany
    {
        return $this->morphMany(QrCode::class, 'ownable');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(LocationMembership::class);
    }
}

