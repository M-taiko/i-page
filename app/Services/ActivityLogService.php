<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLogService
{
    public static function log(
        Model $model,
        string $event,
        ?string $description = null,
        ?array $changes = null,
        ?int $organizationId = null,
        ?int $userId = null
    ): ActivityLog {
        $userId = $userId ?? auth()->id();
        $organizationId = $organizationId ?? session('current_organization_id');

        return ActivityLog::create([
            'user_id' => $userId,
            'organization_id' => $organizationId,
            'subject_type' => $model::class,
            'subject_id' => $model->id,
            'event' => $event,
            'description' => $description,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logCreated(Model $model, ?string $description = null): ActivityLog
    {
        return self::log($model, 'created', $description);
    }

    public static function logUpdated(Model $model, ?array $changes = null, ?string $description = null): ActivityLog
    {
        return self::log($model, 'updated', $description, $changes);
    }

    public static function logDeleted(Model $model, ?string $description = null): ActivityLog
    {
        return self::log($model, 'deleted', $description);
    }

    public static function logCustom(Model $model, string $event, ?string $description = null, ?array $data = null): ActivityLog
    {
        return self::log($model, $event, $description, $data);
    }

    public static function getOrganizationLogs(int $organizationId, int $limit = 50)
    {
        return ActivityLog::where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getUserLogs(int $userId, int $limit = 50)
    {
        return ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
