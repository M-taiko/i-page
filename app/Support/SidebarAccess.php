<?php

namespace App\Support;

use App\Models\{Channel, Organization, User};
use Illuminate\Support\Collection;

final class SidebarAccess
{
    public function __construct(
        public readonly ?Organization $organization,
        public readonly bool $activeMember,
        public readonly bool $organizationManager,
        public readonly Collection $channels,
        public readonly Collection $publishingChannels,
        public readonly Collection $managedChannels,
    ) {
    }

    public static function for(User $user, ?Organization $organization): self
    {
        if (! $organization) {
            return new self(null, false, false, collect(), collect(), collect());
        }

        $activeMember = $user->organizationMemberships()
            ->where('organization_id', $organization->id)
            ->where('status', 'active')
            ->exists();
        $organizationManager = $activeMember && Channel::isOrgManager($user, $organization->id);

        if (! $activeMember) {
            return new self($organization, false, false, collect(), collect(), collect());
        }

        $channels = Channel::visibleTo($user, $organization->id)
            ->where('status', 'active')
            ->with('parentChannels:id,name')
            ->orderBy('name')
            ->get();

        return new self(
            $organization,
            true,
            $organizationManager,
            $channels,
            $channels->filter(fn (Channel $channel) => $channel->userCan($user, 'post'))->values(),
            $channels->filter(fn (Channel $channel) => $channel->canAdminister($user))->values(),
        );
    }

    public function canPublish(): bool
    {
        return $this->publishingChannels->isNotEmpty();
    }

    public function canManageOrganization(): bool
    {
        return $this->organizationManager;
    }

    public function canManageTickets(User $user): bool
    {
        return $this->organizationManager || ($this->activeMember && $user->hasPermissionTo('ticket.manage'));
    }
}
