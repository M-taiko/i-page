<?php
namespace App\Services;
use App\Models\{Channel, HotelUnit};
use Illuminate\Validation\ValidationException;
class HotelPropertyService
{
    public function configure(\App\Models\Organization $organization, \App\Models\User $user, bool $enabled, ?int $propertyId, array $options, ?Channel $context=null): array {
        $modules = array_values(array_diff($options['modules'] ?? [],['bookings']));
        if ($enabled) {
            if ($organization->template?->key !== 'hotel') throw ValidationException::withMessages(['booking_enabled'=>__('Bookings are available for hotel organizations only.')]);
            $inherited = $context ? $this->forChannel($context) : null;
            $propertyId = $propertyId ?: $inherited?->id;
            $property = HotelUnit::where('organization_id',$organization->id)->where('kind','hotel')->whereKey($propertyId)->first();
            if (!$property || (!$property->channel?->canAdminister($user) && $inherited?->id !== $property->id)) throw ValidationException::withMessages(['booking_property_id'=>__('Choose a hotel within your management scope.')]);
            $options['booking_property_id']=$property->id; $modules[]='bookings';
        }
        $options['modules']=$modules;
        return $options;
    }
    public function forChannel(Channel $channel): ?HotelUnit {
        $explicit = $channel->design_options['booking_property_id'] ?? null;
        $query = HotelUnit::where('organization_id',$channel->organization_id)->where('kind','hotel');
        if ($explicit) return $query->whereKey($explicit)->first();
        $own = (clone $query)->where('channel_id',$channel->id)->first();
        if ($own) return $own;
        $parents = $query->whereIn('channel_id',$channel->ancestorIds())->get();
        return $parents->count() === 1 ? $parents->first() : null;
    }
    public function require(Channel $channel): HotelUnit {
        $hotel = $this->forChannel($channel);
        if (!$hotel) throw ValidationException::withMessages(['booking_property_id'=>__('Select a hotel for this booking channel.')]);
        return $hotel;
    }
    public function options(\App\Models\User $user, \App\Models\Organization $organization): array {
        return HotelUnit::where('organization_id',$organization->id)->where('kind','hotel')->with('channel')->get()
            ->filter(fn($hotel)=>$hotel->channel?->canAdminister($user))->pluck('name','id')->all();
    }
}
