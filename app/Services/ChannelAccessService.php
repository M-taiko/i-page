<?php

namespace App\Services;

use App\Models\{Channel, Organization, OrganizationMembership, QrCode, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChannelAccessService
{
    public function authorizeManager(User $actor, Organization $organization): void
    {
        abort_unless(Channel::isOrgManager($actor, $organization->id), 403);
        abort_unless($actor->hasRole('super_admin') || $organization->isActive(), 403);
    }

    public function normalize(Organization $organization, array $rows): array
    {
        $ids = Channel::where('organization_id', $organization->id)->pluck('id')->all();
        $grants = [];
        foreach ($rows as $id => $row) {
            if (!in_array((int) $id, $ids, true)) {
                throw ValidationException::withMessages(['grants' => __('A selected channel does not belong to this organization.')]);
            }
            if (empty($row['selected'])) continue;
            $full = !empty($row['full']);
            $grants[(int) $id] = ['role' => $full ? 'admin' : 'member',
                'can_post' => $full || !empty($row['post']),
                'can_edit' => $full || !empty($row['edit']),
                'can_delete' => $full || !empty($row['delete']),
                'can_manage_bookings' => $full || !empty($row['manage_bookings'])];
        }
        return $grants;
    }

    /** Replace direct channel grants. Organization/owner/ancestor privileges remain explicit. */
    public function replace(Organization $organization, User $user, array $grants): void
    {
        DB::transaction(function () use ($organization, $user, $grants) {
            foreach (Channel::where('organization_id', $organization->id)->get() as $channel) {
                if (Channel::isOrgManager($user, $organization->id) || $channel->admin_user_id === $user->id) continue;
                if (isset($grants[$channel->id])) {
                    $channel->users()->syncWithoutDetaching([$user->id => $grants[$channel->id] + ['status' => 'approved', 'joined_at' => now()]]);
                } else {
                    $channel->users()->detach($user->id);
                }
            }
        });
    }

    public function redeem(string $code, User $user): Organization
    {
        return DB::transaction(function () use ($code, $user) {
            $invite = QrCode::where('code', $code)->lockForUpdate()->firstOrFail();
            $meta = $invite->metadata ?? [];
            abort_unless(($meta['kind'] ?? '') === 'channel_access', 404);
            // A repeat callback from the same accepted account is harmless.
            if (($meta['redeemed_by'] ?? null) === $user->id) return $invite->organization;
            abort_unless($invite->canBeScanned() && empty($meta['redeemed_by']), 410, __('This invitation is expired, cancelled or already used.'));
            if (!empty($meta['email']) && strcasecmp($meta['email'], $user->email) !== 0) {
                throw ValidationException::withMessages(['invitation' => __('Sign in with the email address specified in this invitation.')]);
            }
            $organization = $invite->organization;
            abort_unless($organization && $organization->isActive(), 403);
            $creator = User::find($meta['created_by'] ?? null);
            abort_unless($creator && Channel::isOrgManager($creator, $organization->id), 403);
            $grants = $meta['grants'] ?? [];
            $channels = Channel::where('organization_id', $organization->id)->whereIn('id', array_keys($grants))->get();
            abort_unless($channels->count() === count($grants) && $channels->isNotEmpty(), 410);
            $membership = OrganizationMembership::where('organization_id', $organization->id)->where('user_id', $user->id)->lockForUpdate()->first();
            abort_if($membership && !in_array($membership->status, ['active', 'pending', 'invited'], true), 403);
            if (!$membership || $membership->status !== 'active') {
                OrganizationMembership::updateOrCreate(['organization_id' => $organization->id, 'user_id' => $user->id],
                    ['role' => 'member', 'status' => 'active', 'joined_date' => now(), 'invited_by' => $creator->id]);
            }
            foreach ($channels as $channel) {
                $grant = $grants[$channel->id];
                $existing = $channel->users()->where('users.id', $user->id)->first()?->pivot;
                // Accepting an invitation adds permissions; it never downgrades existing access.
                if ($existing && $existing->status === 'approved') {
                    if (in_array($existing->role, ['admin', 'moderator']) && $grant['role'] !== 'admin') $grant['role'] = $existing->role;
                    foreach (['can_post', 'can_edit', 'can_delete', 'can_manage_bookings'] as $ability) $grant[$ability] = $grant[$ability] || $existing->$ability;
                }
                $channel->users()->syncWithoutDetaching([$user->id => $grant + ['status' => 'approved', 'joined_at' => now()]]);
            }
            $invite->update(['is_active' => false, 'metadata' => array_merge($meta, ['redeemed_by' => $user->id, 'redeemed_at' => now()->toIso8601String()])]);
            return $organization;
        });
    }
}
