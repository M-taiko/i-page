<?php

namespace App\Policies;

use App\Models\Channel;
use App\Models\User;

class ChannelPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Channel $channel): bool
    {
        // Public channels: visible to all org members
        if ($channel->type === 'public') {
            return $user->organizationMemberships()
                ->where('organization_id', $channel->organization_id)
                ->where('status', 'active')
                ->exists();
        }

        // Private channels: only members with a channel_user record
        return $user->channels()
            ->where('channel_id', $channel->id)
            ->exists();
    }

    public function create(User $user): bool
    {
        // Organization members can create channels
        return $user->organizationMemberships()
            ->where('status', 'active')
            ->exists();
    }

    public function update(User $user, Channel $channel): bool
    {
        // Channel admin or organization admin
        $isChannelAdmin = $user->channels()
            ->where('channel_id', $channel->id)
            ->where('role', 'admin')
            ->exists();

        $isOrgAdmin = $user->organizationMemberships()
            ->where('organization_id', $channel->organization_id)
            ->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])
            ->exists();

        return $isChannelAdmin || $isOrgAdmin || $user->hasRole('super_admin');
    }

    public function delete(User $user, Channel $channel): bool
    {
        return $this->update($user, $channel);
    }

    public function post(User $user, Channel $channel): bool
    {
        return $this->view($user, $channel);
    }
}
