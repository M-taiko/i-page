<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlaRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'brand_id',
        'location_id',
        'category_id',
        'priority',
        'first_response_time',
        'resolution_time',
        're_open_response_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class);
    }

    public function appliesToTicket(Ticket $ticket): bool
    {
        if (!$this->is_active || $this->organization_id !== $ticket->organization_id) {
            return false;
        }

        if ($this->brand_id && $this->brand_id !== $ticket->brand_id) {
            return false;
        }

        if ($this->location_id && $this->location_id !== $ticket->location_id) {
            return false;
        }

        if ($this->category_id && $this->category_id !== $ticket->category_id) {
            return false;
        }

        if ($this->priority && $this->priority !== $ticket->priority) {
            return false;
        }

        return true;
    }
}
