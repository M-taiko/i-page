<?php
namespace Database\Seeders;
use App\Models\{Channel,HotelRoom,HotelUnit,Organization};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class HotelBookingDemoSeeder extends Seeder
{
    public function run(): void {
        if (!app()->environment(['local','testing'])) throw new \RuntimeException('Local demo only.');
        $org=Organization::where('slug','hilton-demo')->sole();
        DB::transaction(function() use($org){
            foreach(HotelUnit::where('organization_id',$org->id)->where('kind','hotel')->get() as $hotel){
                foreach([['101','Classic Double',2,1200],['102','Family Room',4,1900],['201','Junior Suite',2,2400]] as [$number,$name,$capacity,$rate]) {
                    HotelRoom::firstOrCreate(['hotel_unit_id'=>$hotel->id,'number'=>$number],['organization_id'=>$org->id,'name'=>$name,'capacity'=>$capacity,'base_rate_minor'=>$rate*100,'currency'=>$hotel->currency,'is_active'=>true,'description'=>'Demo room with private bathroom and breakfast.']);
                }
                $channel=Channel::where('organization_id',$org->id)->where('slug',Str::slug($hotel->city.' · Guest Lounge'))->first();
                if($channel){$options=$channel->design_options ?? []; $options['booking_property_id']=$hotel->id;$options['modules']=array_values(array_unique(array_merge($options['modules'] ?? [],['bookings'])));$channel->update(['design_options'=>$options]);}
            }
        });
    }
}
