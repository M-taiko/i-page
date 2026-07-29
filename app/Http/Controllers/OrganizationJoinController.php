<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class OrganizationJoinController extends Controller
{
    public function __construct(private WorkflowService $workflowService)
    {
    }

    /**
     * Self-service: request to join an organization as a business/staff
     * member. Creates a pending membership + workflow instance, notifying
     * the organization's admins.
     */
    public function requestJoin(Request $request, Organization $organization): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        $existing = $user->membershipFor($organization);

        if ($existing && in_array($existing->status, ['active', 'pending', 'invited'])) {
            $message = __('You already have a membership or pending request with :name.', ['name' => $organization->name]);

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->with('error', $message);
        }

        $membership = OrganizationMembership::updateOrCreate(
            ['organization_id' => $organization->id, 'user_id' => $user->id],
            ['role' => 'staff', 'status' => 'pending']
        );

        $this->workflowService->requestApproval($organization, 'organization_join', $organization, $user, [
            'membership_id' => $membership->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'organization' => ['id' => $organization->id, 'name' => $organization->name],
            ]);
        }

        return back()->with('success', __('Your request to join :name has been sent for approval.', ['name' => $organization->name]));
    }

    public function approve(Organization $organization, User $user): RedirectResponse
    {
        $this->authorizeOrgAdmin($organization);

        $membership = $user->membershipFor($organization);
        abort_unless($membership && $membership->isPending(), 404);

        $instance = $this->pendingJoinInstance($organization, $user);
        if ($instance) {
            $this->workflowService->approve($instance, auth()->user());
        }

        $membership->update([
            'status' => 'active',
            'joined_date' => now(),
            'employee_id' => $membership->employee_id ?: $this->generateEmployeeId($organization),
        ]);

        return back()->with('success', __(':name was approved to join the organization.', ['name' => $user->full_name]));
    }

    /**
     * Auto-generated org-scoped member identifier — a starting point the
     * member can still edit later via updateBusinessDetails().
     */
    private function generateEmployeeId(Organization $organization): string
    {
        $prefix = strtoupper(\Illuminate\Support\Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $organization->slug), 0, 4));
        $prefix = $prefix !== '' ? $prefix : 'ORG';

        $sequence = $organization->memberships()->whereNotNull('employee_id')->count() + 1;

        return $prefix . '-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function reject(Organization $organization, User $user): RedirectResponse
    {
        $this->authorizeOrgAdmin($organization);

        $membership = $user->membershipFor($organization);
        abort_unless($membership && $membership->isPending(), 404);

        $instance = $this->pendingJoinInstance($organization, $user);
        if ($instance) {
            $this->workflowService->reject($instance, auth()->user());
        }

        $membership->update(['status' => 'rejected']);

        return back()->with('success', __('Join request rejected.'));
    }

    /**
     * Self-service: the requester withdraws their own pending join request.
     * Quiet action — no notification, unlike admin approve/reject.
     */
    public function cancelRequest(Request $request, Organization $organization): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        $membership = $user->membershipFor($organization);
        abort_unless($membership && $membership->isPending(), 404);

        $instance = $this->pendingJoinInstance($organization, $user);

        if ($instance) {
            $instance->update(['status' => 'cancelled']);

            // The approver notification is tied to this exact request via
            // workflow_instance_id — remove it so it doesn't sit stale in
            // the admin's notification list for a request that no longer exists.
            \App\Models\Notification::where('type', 'workflow_approval_requested')
                ->where('data->workflow_instance_id', $instance->id)
                ->delete();
        }

        // Hard delete: a cancelled request never became a real membership, and
        // organization_memberships has a unique (organization_id, user_id) index —
        // a soft-deleted row would still block re-requesting.
        $membership->forceDelete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('Your request to join :name was cancelled.', ['name' => $organization->name]));
    }

    /**
     * Self-service: an active member reports their own business details.
     * Editing resets verification — the org admin re-confirms after changes.
     */
    public function updateBusinessDetails(Request $request, Organization $organization): RedirectResponse
    {
        $user = auth()->user();
        $membership = $user->membershipFor($organization);
        abort_unless($membership && $membership->isActive(), 403);

        $validated = $request->validate([
            'job_title' => 'nullable|string|max:120',
            'employee_id' => 'nullable|string|max:60',
            'department_id' => [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('departments', 'id')->where('organization_id', $organization->id),
            ],
        ]);

        $membership->update([
            ...$validated,
            'business_verified_at' => null,
            'business_verified_by' => null,
        ]);

        return back()->with('success', __('Business details saved — awaiting verification.'));
    }

    public function verifyBusinessDetails(Organization $organization, User $user): RedirectResponse
    {
        $this->authorizeOrgAdmin($organization);

        $membership = $user->membershipFor($organization);
        abort_unless($membership && $membership->isActive(), 404);

        $membership->update([
            'business_verified_at' => now(),
            'business_verified_by' => auth()->id(),
        ]);

        return back()->with('success', __(':name\'s business details are now verified.', ['name' => $user->full_name]));
    }

    /**
     * Admin-initiated: invite an existing (already-registered) user to join
     * as staff. Mirrors requestJoin() but in the opposite direction — the
     * *invited user* must accept, not an org admin. Uses the pre-existing
     * 'invited' status (distinct from the self-requested 'pending' status)
     * so both flows can never collide.
     */
    public function inviteUser(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorizeOrgAdmin($organization);

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'required|in:organization_admin,manager,moderator,staff',
        ]);

        $targetUser = User::findOrFail($validated['user_id']);
        $existing = $targetUser->membershipFor($organization);

        if ($existing && in_array($existing->status, ['active', 'pending', 'invited'])) {
            return back()->with('error', __(':name already has a membership or pending status with this organization.', ['name' => $targetUser->full_name]));
        }

        OrganizationMembership::updateOrCreate(
            ['organization_id' => $organization->id, 'user_id' => $targetUser->id],
            ['role' => $validated['role'], 'status' => 'invited', 'invited_by' => auth()->id()]
        );

        Notification::create([
            'user_id' => $targetUser->id,
            'type' => 'organization_invite',
            'data' => [
                'message' => __(':org invited you to join as :role.', [
                    'org' => $organization->name,
                    'role' => auth()->user()->formatRoleLabel($validated['role']),
                ]),
                'link' => route('profile.settings') . '#business',
            ],
        ]);

        return back()->with('success', __('Invitation sent to :name.', ['name' => $targetUser->full_name]));
    }

    public function cancelInvite(Organization $organization, User $user): RedirectResponse
    {
        $this->authorizeOrgAdmin($organization);

        $membership = $user->membershipFor($organization);
        abort_unless($membership && $membership->status === 'invited', 404);

        Notification::where('user_id', $user->id)
            ->where('type', 'organization_invite')
            ->where('data->link', route('profile.settings') . '#business')
            ->delete();

        $membership->forceDelete();

        return back()->with('success', __('Invitation cancelled.'));
    }

    /**
     * Self-service: the invited user accepts. Business details can then be
     * filled in by either party via updateBusinessDetails() (self) or
     * updateMemberBusinessDetails() (admin).
     */
    public function acceptInvite(Request $request, Organization $organization): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        $membership = $user->membershipFor($organization);
        abort_unless($membership && $membership->status === 'invited', 404);

        $membership->update([
            'status' => 'active',
            'joined_date' => now(),
            'employee_id' => $membership->employee_id ?: $this->generateEmployeeId($organization),
        ]);

        if ($membership->invited_by) {
            Notification::create([
                'user_id' => $membership->invited_by,
                'type' => 'organization_invite_accepted',
                'data' => [
                    'message' => __(':name accepted the invitation to join :org.', ['name' => $user->full_name, 'org' => $organization->name]),
                    'link' => route('organizations.settings') . '#members',
                ],
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('You joined :name.', ['name' => $organization->name]));
    }

    public function declineInvite(Request $request, Organization $organization): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        $membership = $user->membershipFor($organization);
        abort_unless($membership && $membership->status === 'invited', 404);

        if ($membership->invited_by) {
            Notification::create([
                'user_id' => $membership->invited_by,
                'type' => 'organization_invite_declined',
                'data' => [
                    'message' => __(':name declined the invitation to join :org.', ['name' => $user->full_name, 'org' => $organization->name]),
                    'link' => null,
                ],
            ]);
        }

        $membership->forceDelete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('Invitation declined.'));
    }

    /**
     * Admin fills in business details on behalf of a member — same fields,
     * same verification-reset behavior as the self-service version.
     */
    public function updateMemberBusinessDetails(Request $request, Organization $organization, User $user): RedirectResponse
    {
        $this->authorizeOrgAdmin($organization);

        $membership = $user->membershipFor($organization);
        abort_unless($membership && $membership->isActive(), 404);

        $validated = $request->validate([
            'job_title' => 'nullable|string|max:120',
            'employee_id' => 'nullable|string|max:60',
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where('organization_id', $organization->id),
            ],
        ]);

        $membership->update([
            ...$validated,
            'business_verified_at' => null,
            'business_verified_by' => null,
        ]);

        return back()->with('success', __('Business details updated.'));
    }

    private function pendingJoinInstance(Organization $organization, User $user)
    {
        return \App\Models\WorkflowInstance::where('workflowable_type', Organization::class)
            ->where('workflowable_id', $organization->id)
            ->where('requested_by', $user->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();
    }

    private function authorizeOrgAdmin(Organization $organization): void
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('super_admin')
            || $user->membershipFor($organization)?->role === 'organization_admin';

        abort_unless($isAdmin, 403);
    }
}
