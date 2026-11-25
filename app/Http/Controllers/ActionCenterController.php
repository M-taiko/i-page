<?php
namespace App\Http\Controllers;

use App\Models\{Channel, Organization, OrganizationMembership, Post, User};
use App\Services\{ActionCenterService, PostApprovalService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ActionCenterController extends Controller
{
    private function organization(): Organization
    {
        $organization = auth()->user()->currentOrganization;
        abort_unless($organization && $organization->isActive() && $organization->is_active, 403);
        return $organization;
    }

    public function index(Request $request, ActionCenterService $service)
    {
        $validated = $request->validate(['type' => ['nullable', Rule::in(['posts', 'organization', 'channels'])]]);
        $organization = $this->organization();
        $data = $service->data($request->user(), $organization, $validated['type'] ?? null);
        return view('action-center.index', $data + compact('organization'));
    }

    public function decide(Request $request, string $kind, int $id, ActionCenterService $service)
    {
        abort_unless(in_array($kind, ['posts', 'organization', 'channels'], true), 404);
        $request->validate(['decision' => 'required|in:approve,reject', 'reason' => 'nullable|string|max:1000', 'user_id' => $kind === 'channels' ? 'required|integer' : 'nullable|integer']);
        $organization = $this->organization();
        $decision = $request->input('decision');
        return DB::transaction(function () use ($request, $kind, $id, $organization, $service, $decision) {
            // Scope both the displayed list and every mutation to the same actor and tenant.
            $query = $service->queries($request->user(), $organization)[$kind];
            if ($kind === 'channels') {
                $query->where('m.channel_id', $id)->where('m.user_id', $request->integer('user_id'));
            } else {
                $query->where($kind === 'posts' ? 'posts.id' : 'm.id', $id);
            }
            $item = $query->lockForUpdate()->first();
            abort_unless($item, 404, __('This task is no longer available.'));
            if ($kind === 'posts') {
                $post = Post::findOrFail($id);
                $this->authorize('approve', $post);
                $approvals = app(PostApprovalService::class);
                $decision === 'approve' ? $approvals->approve($post, $request->user()) : $approvals->reject($post, $request->user(), $request->input('reason'));
                return back()->with('success', __('Decision saved.'));
            }
            $person = User::findOrFail($item->subject_user_id);
            if ($kind === 'organization') {
                return app(OrganizationJoinController::class)->{$decision}($organization, $person);
            }
            $channel = Channel::where('organization_id', $organization->id)->findOrFail($item->channel_id);
            $method = $decision === 'approve' ? 'approveJoinRequest' : 'rejectJoinRequest';
            return app(TenantChannelController::class)->{$method}($channel, $person);
        });
    }
}
