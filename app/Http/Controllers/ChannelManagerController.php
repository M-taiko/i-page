<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * The tenant assigns who runs each channel (ADR-016): admin and moderator roles apply to the
 * channel and every sub-channel below it; members get only the granted can_post/edit/delete.
 * Also lists every person across the tenant's channels. Organization admins/managers only.
 */
class ChannelManagerController extends Controller
{
    public function index()
    {
        $organization = $this->organization();
        $tree = TenantPostController::channelTree($organization, true);

        $assignments = DB::table('channel_user')
            ->join('channels', 'channels.id', '=', 'channel_user.channel_id')
            ->join('users', 'users.id', '=', 'channel_user.user_id')
            ->where('channels.organization_id', $organization->id)->whereNull('channels.deleted_at')
            ->where('channel_user.status', 'approved')
            ->where(fn ($q) => $q->whereIn('channel_user.role', ['admin', 'moderator'])
                ->orWhere('channel_user.can_post', true)->orWhere('channel_user.can_edit', true)->orWhere('channel_user.can_delete', true))
            ->get(['channel_user.channel_id', 'channel_user.user_id', 'channel_user.role', 'channel_user.can_post', 'channel_user.can_edit', 'channel_user.can_delete',
                'users.first_name', 'users.last_name', 'users.email'])
            ->groupBy('channel_id');

        $team = User::whereIn('id', $this->teamIds($organization))->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);

        return view('organizations.channel-managers.index', compact('organization', 'tree', 'assignments', 'team'));
    }

    public function store(Request $request)
    {
        $organization = $this->organization();

        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::in($this->teamIds($organization))],
            'channel_id' => ['required', 'integer', Rule::exists('channels', 'id')->where('organization_id', $organization->id)->whereNull('deleted_at')],
            'role' => 'required|in:admin,moderator,member',
            'can_post' => 'nullable|boolean',
            'can_edit' => 'nullable|boolean',
            'can_delete' => 'nullable|boolean',
        ], ['user_id.in' => __('Only active members of the organization can be assigned.')]);

        $permissions = $validated['role'] === 'member'
            ? ['can_post' => $request->boolean('can_post'), 'can_edit' => $request->boolean('can_edit'), 'can_delete' => $request->boolean('can_delete')]
            : Channel::ROLE_PERMISSIONS[$validated['role']];

        $channel = Channel::findOrFail($validated['channel_id']);
        $pivot = ['role' => $validated['role'], 'status' => 'approved'] + $permissions;
        if ($channel->users()->where('user_id', $validated['user_id'])->exists()) {
            $channel->users()->updateExistingPivot($validated['user_id'], $pivot);
        } else {
            $channel->users()->attach($validated['user_id'], $pivot + ['joined_at' => now()]);
        }

        $roleLabel = ['admin' => __('Channel admin'), 'moderator' => __('Moderator'), 'member' => __('Member')][$validated['role']];
        DB::table('notifications')->insert([
            'user_id' => $validated['user_id'],
            'type' => 'channel.role',
            'data' => json_encode([
                'message' => __('You are now :role of :channel.', ['role' => $roleLabel, 'channel' => $channel->name]),
                'link' => route('tenant.channels.show', $channel->id),
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', __(':role saved for :channel.', ['role' => $roleLabel, 'channel' => $channel->name]));
    }

    /** Takes the management role away; the person stays a plain member of the channel. */
    public function destroy(Channel $channel, User $user)
    {
        $organization = $this->organization();
        abort_unless($channel->organization_id === $organization->id, 404);

        $channel->users()->updateExistingPivot($user->id, ['role' => 'member'] + Channel::ROLE_PERMISSIONS['member']);

        return back()->with('success', __('Management role removed.'));
    }

    /** Every employee and subscriber across the tenant's channels, with the channels they are in. */
    public function people(Request $request)
    {
        $organization = $this->organization();
        $channelIds = Channel::where('organization_id', $organization->id)->pluck('id')->all();

        $staffIds = DB::table('organization_memberships')
            ->where('organization_id', $organization->id)->where('status', 'active')->whereNull('deleted_at')
            ->pluck('role', 'user_id');
        $kind = in_array($request->query('kind'), ['staff', 'visitors'], true) ? $request->query('kind') : null;
        $search = trim((string) $request->query('q'));

        $people = User::query()
            ->where(fn ($q) => $q->whereIn('id', DB::table('channel_user')->whereIn('channel_id', $channelIds)->where('status', 'approved')->select('user_id'))
                ->orWhereIn('id', $staffIds->keys()))
            ->when($kind === 'staff', fn ($q) => $q->whereIn('id', $staffIds->keys()))
            ->when($kind === 'visitors', fn ($q) => $q->whereNotIn('id', $staffIds->keys()))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('first_name', 'like', "%$search%")
                ->orWhere('last_name', 'like', "%$search%")->orWhere('email', 'like', "%$search%")->orWhere('mobile', 'like', "%$search%")))
            ->orderBy('first_name')
            ->paginate(25)->withQueryString();

        $memberships = DB::table('channel_user')
            ->join('channels', 'channels.id', '=', 'channel_user.channel_id')
            ->whereIn('channel_user.channel_id', $channelIds)->whereIn('channel_user.user_id', $people->pluck('id'))
            ->where('channel_user.status', 'approved')
            ->get(['channel_user.user_id', 'channel_user.role', 'channels.id', 'channels.name'])
            ->groupBy('user_id');

        return view('organizations.hub.people', compact('organization', 'people', 'memberships', 'staffIds', 'kind', 'search'));
    }

    private function teamIds(Organization $organization): array
    {
        return DB::table('organization_memberships')
            ->where('organization_id', $organization->id)->where('status', 'active')->whereNull('deleted_at')
            ->pluck('user_id')->all();
    }

    private function organization(): Organization
    {
        $user = auth()->user();
        $organization = $user->currentOrganization;
        abort_unless($organization && Channel::isOrgManager($user, $organization->id), 403);

        return $organization;
    }
}
