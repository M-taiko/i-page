<?php
namespace App\Support;
use App\Models\{Channel, Organization, User};
class ChannelNavigation
{
    public static function options(User $user, ?Organization $organization): array
    {
        if (!$organization || !$organization->isActive()) return [];
        if (!$user->hasRole('super_admin') && !$user->organizationMemberships()->where('organization_id',$organization->id)->where('status','active')->exists()) return [];
        $channels = Channel::visibleTo($user,$organization->id)->where('status','active')->with('parentChannels:id')->get()->keyBy('id');
        return $channels->map(function ($channel) use ($channels, $organization) {
            $names = [$channel->name]; $seen = [$channel->id]; $current = $channel;
            while ($parent = $current->parentChannels->first(fn ($item) => $channels->has($item->id) && !in_array($item->id,$seen))) {
                $current = $channels[$parent->id]; $seen[] = $current->id; array_unshift($names,$current->name);
            }
            return ['id'=>$channel->id,'label'=>$organization->name.' / '.implode(' / ',$names),'url'=>route('tenant.channels.show',$channel)];
        })->sortBy('label')->values()->all();
    }
}
