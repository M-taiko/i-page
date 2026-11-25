<?php
namespace App\Services;
use App\Models\{Channel, HotelUnit, Organization, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class HotelHierarchyService
{
    public function create(Organization $organization, User $actor, array $data): HotelUnit
    {
        abort_unless($organization->template?->key === 'hotel', 404);
        abort_unless($organization->isActive(), 403);
        return DB::transaction(function () use ($organization, $actor, $data) {
            $organization = Organization::whereKey($organization->id)->lockForUpdate()->firstOrFail();
            if ($organization->max_channels !== null && $organization->channels()->count() >= $organization->max_channels) {
                throw ValidationException::withMessages(['name' => __('The organization channel limit has been reached.')]);
            }
            $parent = !empty($data['parent_id']) ? HotelUnit::where('organization_id', $organization->id)->whereKey($data['parent_id'])->lockForUpdate()->firstOrFail() : null;
            if (($data['kind'] === 'country' && $parent) || ($data['kind'] === 'hotel' && $parent && $parent->kind !== 'country')) {
                throw ValidationException::withMessages(['parent_id' => __('Choose a country for the hotel; countries belong directly to the group.')]);
            }
            abort_unless(Channel::isOrgManager($actor, $organization->id) || ($parent && $parent->channel->canAdminister($actor)), 403);
            // New hierarchy channels are private; public guest channels are configured separately.
            $channel = Channel::create(['organization_id'=>$organization->id,'name'=>$data['name'], 'slug'=>Str::slug($data['name']).'-'.Str::random(8), 'type'=>$data['kind'] === 'hotel' ? 'public' : 'private','audience_profile'=>$data['kind'] === 'hotel' ? 'public' : 'business','status'=>'active']);
            if ($parent) $channel->parentChannels()->attach($parent->channel_id);
            return HotelUnit::create([
                'organization_id'=>$organization->id,'parent_id'=>$parent?->id,'channel_id'=>$channel->id,
                'kind'=>$data['kind'],'name'=>$data['name'],'country'=>$parent?->country ?? $data['country'],
                'city'=>$data['kind'] === 'hotel' ? ($data['city'] ?? null) : null,
                'timezone'=>$data['timezone'],'currency'=>$data['currency'],
            ]);
        });
    }
}
