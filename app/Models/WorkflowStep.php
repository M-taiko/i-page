<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends Model
{
    protected $fillable = [
        'workflow_instance_id',
        'step_order',
        'role_required',
        'decided_by',
        'decision',
        'comment',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
        ];
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
