<?php

namespace App\Observers;

use App\Models\OrganizationMembership;
use Illuminate\Support\Facades\Log;

class MembershipAuditObserver
{
    public function created(OrganizationMembership $membership): void
    {
        Log::channel('audit')->info('Organization membership created', [
            'membership_id' => $membership->id,
            'organization_id' => $membership->organization_id,
            'user_id' => $membership->user_id,
            'role' => $membership->role,
            'status' => $membership->status,
            'performed_by' => auth()->id() ?? 'system',
            'timestamp' => now(),
        ]);
    }

    public function updated(OrganizationMembership $membership): void
    {
        $changes = $membership->getChanges();
        
        if (isset($changes['role']) || isset($changes['status']) || isset($changes['department_id'])) {
            Log::channel('audit')->warning('Organization membership modified', [
                'membership_id' => $membership->id,
                'organization_id' => $membership->organization_id,
                'user_id' => $membership->user_id,
                'changes' => $changes,
                'performed_by' => auth()->id() ?? 'system',
                'timestamp' => now(),
            ]);
        }
    }

    public function deleted(OrganizationMembership $membership): void
    {
        Log::channel('audit')->warning('Organization membership deleted', [
            'membership_id' => $membership->id,
            'organization_id' => $membership->organization_id,
            'user_id' => $membership->user_id,
            'was_role' => $membership->role,
            'was_status' => $membership->status,
            'performed_by' => auth()->id() ?? 'system',
            'timestamp' => now(),
        ]);
    }
}
