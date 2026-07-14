<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'sla_rule_id',
        'event_type',
        'status',
        'deadline_at',
        'breached_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'breached_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(SlaRule::class, 'sla_rule_id');
    }

    public function isBreached(): bool
    {
        return $this->status === 'breached';
    }

    public function isAtRisk(): bool
    {
        return $this->status === 'at_risk';
    }

    public function getTimeRemainingMinutes(): int
    {
        return now()->diffInMinutes($this->deadline_at, false);
    }
}
