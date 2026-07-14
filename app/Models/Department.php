<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use Auditable, SoftDeletes;
    protected $fillable = ['organization_id', 'location_id', 'brand_id', 'parent_department_id', 'name', 'description', 'slug'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function parentDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_department_id');
    }

    /**
     * @deprecated Use location() instead
     */
    public function branch(): BelongsTo
    {
        return $this->location();
    }

    public function qrCodes(): MorphMany
    {
        return $this->morphMany(QrCode::class, 'ownable');
    }
}
