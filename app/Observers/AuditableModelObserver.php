<?php

namespace App\Observers;

use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;

class AuditableModelObserver
{
    public function created(Model $model): void
    {
        if (!$this->shouldAudit($model)) {
            return;
        }

        ActivityLogService::logCreated($model);
    }

    public function updated(Model $model): void
    {
        if (!$this->shouldAudit($model)) {
            return;
        }

        $changes = $this->getChanges($model);
        if (!empty($changes)) {
            ActivityLogService::logUpdated($model, $changes);
        }
    }

    public function deleted(Model $model): void
    {
        if (!$this->shouldAudit($model)) {
            return;
        }

        ActivityLogService::logDeleted($model);
    }

    public function restored(Model $model): void
    {
        if (!$this->shouldAudit($model)) {
            return;
        }

        ActivityLogService::logCustom($model, 'restored');
    }

    protected function shouldAudit(Model $model): bool
    {
        return method_exists($model, 'createdBy');
    }

    protected function getChanges(Model $model): array
    {
        $changes = [];
        foreach ($model->getChanges() as $key => $value) {
            $original = $model->getOriginal($key);
            if ($original !== $value) {
                $changes[$key] = [
                    'old' => $original,
                    'new' => $value,
                ];
            }
        }
        return $changes;
    }
}
