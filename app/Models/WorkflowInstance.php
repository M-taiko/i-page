<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowInstance extends Model
{
    protected $fillable = [
        'workflow_definition_id',
        'workflowable_type',
        'workflowable_id',
        'requested_by',
        'status',
        'current_step',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    public function workflowable(): MorphTo
    {
        return $this->morphTo();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('step_order');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
