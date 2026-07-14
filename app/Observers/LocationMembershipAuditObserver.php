<?php

namespace App\Observers;

use App\Models\LocationMembership;
use Illuminate\Support\Facades\Log;

class LocationMembershipAuditObserver
{
    public function created(LocationMembership $membership): void
    {
        Log::channel('audit')->info('Location membership created', [
            'membership_id' => $membership->id,
            'location_id' => $membership->location_id,
            'user_id' => $membership->user_id,
            'job_role' => $membership->job_role,
            'is_primary' => $membership->is_primary,
            'status' => $membership->status,
            'performed_by' => auth()->id() ?? 'system',
            'timestamp' => now(),
        ]);
    }

    public function updated(LocationMembership $membership): void
    {
        $changes = $membership->getChanges();
        
        if (isset($changes['status']) || isset($changes['is_primary']) || isset($changes['job_role'])) {
            Log::channel('audit')->warning('Location membership modified', [
                'membership_id' => $membership->id,
                'location_id' => $membership->location_id,
                'user_id' => $membership->user_id,
                'changes' => $changes,
                'performed_by' => auth()->id() ?? 'system',
                'timestamp' => now(),
            ]);
        }
    }

    public function deleted(LocationMembership $membership): void
    {
        Log::channel('audit')->warning('Location membership deleted', [
            'membership_id' => $membership->id,
            'location_id' => $membership->location_id,
            'user_id' => $membership->user_id,
            'was_status' => $membership->status,
            'performed_by' => auth()->id() ?? 'system',
            'timestamp' => now(),
        ]);
    }
}
