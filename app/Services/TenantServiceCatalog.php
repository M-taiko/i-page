<?php
namespace App\Services;
use App\Models\{AppointmentService,Organization,User};
class TenantServiceCatalog
{
    public const HOTEL_DEFAULTS = [
        ['name'=>'Room reservation','kind'=>'room','description'=>'Room booking and stay enquiries.','duration_minutes'=>30,'slot_capacity'=>20],
        ['name'=>'Open buffet breakfast','kind'=>'restaurant','description'=>'Open buffet breakfast reservation.','duration_minutes'=>120,'slot_capacity'=>80],
        ['name'=>'Restaurant table','kind'=>'restaurant','description'=>'Lunch or dinner table reservation.','duration_minutes'=>120,'slot_capacity'=>50],
        ['name'=>'Swimming pool','kind'=>'pool','description'=>'Swimming pool access appointment.','duration_minutes'=>90,'slot_capacity'=>30],
        ['name'=>'Spa treatment','kind'=>'spa','description'=>'Spa treatment and wellness appointment.','duration_minutes'=>60,'slot_capacity'=>8],
        ['name'=>'Gym session','kind'=>'gym','description'=>'Gym access or trainer consultation.','duration_minutes'=>60,'slot_capacity'=>20],
        ['name'=>'Playground visit','kind'=>'playground','description'=>'Supervised playground visit.','duration_minutes'=>60,'slot_capacity'=>25],
    ];
    public function seedDefaults(Organization $organization): void
    {
        if ($organization->template?->key !== 'hotel') return;
        foreach (self::HOTEL_DEFAULTS as $row) AppointmentService::firstOrCreate(
            ['organization_id'=>$organization->id,'channel_id'=>null,'name'=>$row['name']],
            $row+['is_active'=>true]
        );
    }
    public function can(User $user,Organization $organization,string $ability): bool
    {
        if ($user->hasRole('super_admin')) return true;
        $membership=$organization->memberships()->where('user_id',$user->id)->where('status','active')->first();
        if (!$membership) return false;
        if ($membership->role === 'organization_admin') return true;
        return (bool) $membership->{'can_'.$ability.'_services'};
    }
    public function abilities(User $user,Organization $organization): array {return collect(['add','edit','delete'])->mapWithKeys(fn($ability)=>[$ability=>$this->can($user,$organization,$ability)])->all();}
    public function canView(User $user,Organization $organization): bool {return in_array(true,$this->abilities($user,$organization),true);}
}
