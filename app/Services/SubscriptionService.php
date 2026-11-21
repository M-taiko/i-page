<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Channel memberships created by visitors (docs/27 "Channel Bell & Organization Follow Subscriptions").
 *
 *  - join():   the visitor subscribes to a channel → source "self", notifications on.
 *  - follow(): following an organization subscribes to its main public channels → source "follow", muted.
 *  - unfollow(): removes only the "follow" memberships of that organization.
 */
class SubscriptionService
{
    public function __construct(private WorkflowService $workflow)
    {
    }

    /** @return string approved | pending | already */
    public function join(User $user, Channel $channel): string
    {
        $row = $this->row($user, $channel);

        if ($row && $row->status === 'approved' && $row->source === 'self') {
            return 'already';
        }
        if ($row && $row->status === 'approved') {
            // It came from following the organization: now the visitor chose it, so notify them.
            $this->update($user, $channel, ['source' => 'self', 'muted_at' => null]);

            return 'approved';
        }
        if ($row && $row->status === 'pending') {
            return 'pending';
        }

        if ($channel->type === 'private') {
            $this->insert($user, $channel, ['status' => 'pending', 'source' => 'self']);
            $this->workflow->requestApproval($channel->organization, 'channel_join', $channel, $user, ['requested_role' => 'member']);

            return 'pending';
        }

        $this->insert($user, $channel, ['status' => 'approved', 'source' => 'self', 'joined_at' => now()]);

        return 'approved';
    }

    public function leave(User $user, Channel $channel): void
    {
        DB::table('channel_user')->where('user_id', $user->id)->where('channel_id', $channel->id)->delete();
    }

    /** Toggle notifications for one membership. @return bool|null true = notifications on; null = not a member */
    public function toggleBell(User $user, Channel $channel): ?bool
    {
        $row = $this->row($user, $channel);
        if (! $row || $row->status !== 'approved') {
            return null;
        }
        $on = $row->muted_at !== null;   // muted → turn on
        // Turning the bell on is an explicit choice, so the membership becomes the visitor's own.
        $this->update($user, $channel, $on ? ['muted_at' => null, 'source' => 'self'] : ['muted_at' => now()]);

        return $on;
    }

    /** @return int number of channels added */
    public function follow(User $user, Organization $organization): int
    {
        if (! $user->followedOrganizations()->where('organization_id', $organization->id)->exists()) {
            $user->followedOrganizations()->attach($organization->id);
        }

        $existing = DB::table('channel_user')->where('user_id', $user->id)->pluck('channel_id')->all();
        $now = now();
        $rows = $this->mainChannels($organization)->reject(fn (Channel $c) => in_array($c->id, $existing))
            ->map(fn (Channel $c) => [
                'channel_id' => $c->id, 'user_id' => $user->id, 'role' => 'member', 'status' => 'approved', 'source' => 'follow',
                'joined_at' => $now, 'muted_at' => $now, 'can_post' => false, 'can_edit' => false, 'can_delete' => false,
            ])->values()->all();
        if ($rows) {
            DB::table('channel_user')->insert($rows);
        }

        return count($rows);
    }

    /** @return int number of channels removed */
    public function unfollow(User $user, Organization $organization): int
    {
        $user->followedOrganizations()->detach($organization->id);

        return DB::table('channel_user')->where('user_id', $user->id)->where('source', 'follow')
            ->whereIn('channel_id', Channel::where('organization_id', $organization->id)->select('id'))
            ->delete();
    }

    /** Public, active, top-level channels of the organization. */
    public function mainChannels(Organization $organization)
    {
        return Channel::where('organization_id', $organization->id)
            ->where('type', 'public')->where('status', 'active')
            ->whereDoesntHave('parentChannels')
            ->get();
    }

    private function row(User $user, Channel $channel): ?object
    {
        return DB::table('channel_user')->where('user_id', $user->id)->where('channel_id', $channel->id)->first();
    }

    private function insert(User $user, Channel $channel, array $values): void
    {
        DB::table('channel_user')->insert(['channel_id' => $channel->id, 'user_id' => $user->id, 'role' => 'member'] + $values + Channel::ROLE_PERMISSIONS['member']);
    }

    private function update(User $user, Channel $channel, array $values): void
    {
        DB::table('channel_user')->where('user_id', $user->id)->where('channel_id', $channel->id)->update($values);
    }
}
