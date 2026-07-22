<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use Illuminate\Database\Eloquent\Model;

class WorkflowService
{
    /**
     * Get the active workflow for a module, seeding a default single-step
     * "role approval" definition the first time a module is used.
     */
    public function definitionFor(Organization $organization, string $module, string $defaultApproverRole = 'organization_admin'): WorkflowDefinition
    {
        return WorkflowDefinition::firstOrCreate(
            ['organization_id' => $organization->id, 'module' => $module, 'is_active' => true],
            [
                'name' => ucfirst(str_replace('_', ' ', $module)) . ' Approval',
                'steps' => [
                    ['order' => 1, 'role' => $defaultApproverRole, 'required' => true],
                ],
            ]
        );
    }

    public function requestApproval(
        Organization $organization,
        string $module,
        Model $workflowable,
        User $requester,
        array $context = []
    ): WorkflowInstance {
        $definition = $this->definitionFor($organization, $module);

        $instance = $definition->instances()->create([
            'workflowable_type' => $workflowable::class,
            'workflowable_id' => $workflowable->getKey(),
            'requested_by' => $requester->id,
            'status' => 'pending',
            'current_step' => 1,
            'context' => $context,
        ]);

        foreach ($definition->steps as $step) {
            $instance->steps()->create([
                'step_order' => $step['order'],
                'role_required' => $step['role'],
            ]);
        }

        $this->notifyApprovers($instance, $definition, $organization);

        return $instance;
    }

    public function approve(WorkflowInstance $instance, User $approver, ?string $comment = null): WorkflowInstance
    {
        $step = $instance->steps()->where('step_order', $instance->current_step)->first();

        $step?->update([
            'decided_by' => $approver->id,
            'decision' => 'approved',
            'comment' => $comment,
            'decided_at' => now(),
        ]);

        $nextStep = $instance->steps()->where('step_order', '>', $instance->current_step)->orderBy('step_order')->first();

        if ($nextStep) {
            $instance->update(['current_step' => $nextStep->step_order]);
        } else {
            $instance->update(['status' => 'approved']);
            $this->notifyRequester($instance, 'approved');
        }

        return $instance->fresh();
    }

    public function reject(WorkflowInstance $instance, User $approver, ?string $comment = null): WorkflowInstance
    {
        $step = $instance->steps()->where('step_order', $instance->current_step)->first();

        $step?->update([
            'decided_by' => $approver->id,
            'decision' => 'rejected',
            'comment' => $comment,
            'decided_at' => now(),
        ]);

        $instance->update(['status' => 'rejected']);
        $this->notifyRequester($instance, 'rejected');

        return $instance->fresh();
    }

    /**
     * Notify whoever can act on the first step: users holding that step's
     * required org role, plus the workflowable's own admin (e.g. a channel's
     * admin_user_id) when it has one — same "who can approve" set used by
     * Channel::canBeAccessedBy().
     */
    private function notifyApprovers(WorkflowInstance $instance, WorkflowDefinition $definition, Organization $organization): void
    {
        $firstStepRole = $definition->steps[0]['role'] ?? null;
        $recipientIds = collect();

        if ($firstStepRole) {
            $recipientIds = $organization->memberships()->where('role', $firstStepRole)->pluck('user_id');
        }

        $workflowable = $instance->workflowable;
        if ($workflowable instanceof Channel && $workflowable->admin_user_id) {
            $recipientIds->push($workflowable->admin_user_id);
        }

        $label = $this->labelFor($workflowable);
        $link = $this->approverLinkFor($workflowable);

        foreach ($recipientIds->unique()->filter(fn ($id) => $id !== $instance->requested_by) as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type' => 'workflow_approval_requested',
                'data' => [
                    'message' => __(':name requested to join :target', [
                        'name' => $instance->requester->full_name,
                        'target' => $label,
                    ]),
                    'link' => $link,
                ],
            ]);
        }
    }

    private function notifyRequester(WorkflowInstance $instance, string $decision): void
    {
        $workflowable = $instance->workflowable;
        $label = $this->labelFor($workflowable);
        $link = $decision === 'approved' ? $this->requesterLinkFor($workflowable) : null;

        Notification::create([
            'user_id' => $instance->requested_by,
            'type' => $decision === 'approved' ? 'workflow_approved' : 'workflow_rejected',
            'data' => [
                'message' => $decision === 'approved'
                    ? __('Your request to join :target was approved!', ['target' => $label])
                    : __('Your request to join :target was declined.', ['target' => $label]),
                'link' => $link,
            ],
        ]);
    }

    private function labelFor(?Model $workflowable): string
    {
        return $workflowable->name ?? __('the item');
    }

    private function approverLinkFor(?Model $workflowable): ?string
    {
        if ($workflowable instanceof Channel) {
            return route('tenant.channels.show', $workflowable->id);
        }

        return null;
    }

    private function requesterLinkFor(?Model $workflowable): ?string
    {
        if ($workflowable instanceof Channel) {
            return route('dashboard.channels.show', [$workflowable->organization_id, $workflowable->id]);
        }

        return null;
    }
}
