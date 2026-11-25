<?php
namespace App\Services;

use App\Models\{Channel, Organization, Post, User};
use Illuminate\Support\Facades\DB;

class ActionCenterService
{
    public function queries(User $user, Organization $organization): array
    {
        $membership = $user->organizationMemberships()->where('organization_id', $organization->id)->where('status', 'active')->first();
        $super = $user->hasRole('super_admin');
        $approvePosts = $super || ($membership && in_array($membership->role, ['organization_admin', 'manager'], true));
        $approveOrganization = $super || $membership?->role === 'organization_admin';
        $channels = Channel::visibleTo($user, $organization->id)->get()->filter(fn ($channel) => $channel->canAdminister($user))->modelKeys();

        $posts = DB::table('posts')->where('organization_id', $organization->id)->whereNull('deleted_at')->where('status', 'pending_approval')
            ->when(!$approvePosts, fn ($q) => $q->whereRaw('1 = 0'))
            ->selectRaw("'posts' as kind, id, title, organization_id, channel_id, author_id as subject_user_id, created_at");
        $members = DB::table('organization_memberships as m')->join('users as u', 'u.id', '=', 'm.user_id')
            ->where('m.organization_id', $organization->id)->where('m.status', 'pending')->whereNull('m.deleted_at')->whereNull('u.deleted_at')
            ->when(!$approveOrganization, fn ($q) => $q->whereRaw('1 = 0'))
            ->selectRaw("'organization' as kind, m.id, u.first_name as title, m.organization_id, NULL as channel_id, m.user_id as subject_user_id, m.created_at");
        $joins = DB::table('channel_user as m')->join('channels as c', 'c.id', '=', 'm.channel_id')->join('users as u', 'u.id', '=', 'm.user_id')
            ->where('c.organization_id', $organization->id)->whereIn('c.id', $channels)->where('m.status', 'pending')->whereNull('c.deleted_at')->whereNull('u.deleted_at')
            ->selectRaw("'channels' as kind, c.id as id, u.first_name as title, c.organization_id, c.id as channel_id, m.user_id as subject_user_id, COALESCE(m.joined_at, c.created_at) as created_at");
        return ['posts' => $posts, 'organization' => $members, 'channels' => $joins];
    }

    public function data(User $user, Organization $organization, ?string $kind = null): array
    {
        $queries = $this->queries($user, $organization);
        $counts = array_map(fn ($q) => (clone $q)->count(), $queries);
        $selected = $kind ? [$queries[$kind]] : array_values($queries);
        $union = clone array_shift($selected);
        foreach ($selected as $query) { $union->unionAll(clone $query); }
        $items = DB::query()->fromSub($union, 'tasks')->orderBy('created_at')->orderBy('kind')->orderBy('id')->paginate(20)->withQueryString();
        $people = User::whereIn('id', $items->pluck('subject_user_id'))->get()->keyBy('id');
        $channels = Channel::where('organization_id', $organization->id)->whereIn('id', $items->pluck('channel_id')->filter())->get()->keyBy('id');
        return compact('counts', 'items', 'people', 'channels');
    }
}
