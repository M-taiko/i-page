<?php

namespace App\Observers;

use App\Models\Brand;
use App\Models\Location;
use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ResourceAuditObserver
{
    public function created(Model $model): void
    {
        $this->logResourceAction($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->logResourceAction($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->logResourceAction($model, 'deleted');
    }

    private function logResourceAction(Model $model, string $action): void
    {
        $resourceType = class_basename($model);
        
        Log::channel('audit')->info("{$resourceType} {$action}", [
            'resource_type' => $resourceType,
            'resource_id' => $model->id,
            'organization_id' => $model->organization_id ?? null,
            'action' => $action,
            'data' => $action === 'deleted' ? $model->getAttributes() : $model->getChanges(),
            'performed_by' => auth()->id() ?? 'system',
            'timestamp' => now(),
        ]);
    }
}
