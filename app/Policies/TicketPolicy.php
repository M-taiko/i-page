<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        // User must be member of ticket's organization
        if (!$user->organizationMemberships()
            ->where('organization_id', $ticket->organization_id)
            ->where('status', 'active')
            ->exists()) {
            return false;
        }

        // If user is the creator, they can view
        if ($ticket->created_by === $user->id) {
            return true;
        }

        // Admins can view any ticket in their org
        return $user->organizationMemberships()
            ->where('organization_id', $ticket->organization_id)
            ->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])
            ->exists();
    }

    public function create(User $user): bool
    {
        // Any org member can create a ticket
        return $user->organizationMemberships()
            ->where('status', 'active')
            ->exists();
    }

    public function update(User $user, Ticket $ticket): bool
    {
        // Only admins can update tickets
        return $user->organizationMemberships()
            ->where('organization_id', $ticket->organization_id)
            ->where('status', 'active')
            ->whereIn('role', ['organization_admin', 'manager'])
            ->exists() || $user->hasRole('super_admin');
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $this->update($user, $ticket);
    }

    public function resolve(User $user, Ticket $ticket): bool
    {
        return $this->update($user, $ticket);
    }

    public function close(User $user, Ticket $ticket): bool
    {
        return $this->update($user, $ticket);
    }

    public function reopen(User $user, Ticket $ticket): bool
    {
        return $this->update($user, $ticket);
    }

    public function addMessage(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }
}
