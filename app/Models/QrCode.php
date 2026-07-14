<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QrCode extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'ownable_type',
        'ownable_id',
        'code',
        'label',
        'url',
        'expires_at',
        'is_active',
        'is_branded',
        'metadata',
    ];

    protected $casts = [
        'scan_count' => 'integer',
        'is_active' => 'boolean',
        'is_branded' => 'boolean',
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function ownable()
    {
        return $this->morphTo();
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(QrScanLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function canBeScanned(): bool
    {
        return $this->is_active && !$this->isExpired();
    }
}
