<?php
namespace Database\Seeders;
use App\Models\{Channel, HotelUnit, Organization};
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Reorganize the existing local hotel demo without resetting users or posts. */
class HotelChannelLayoutSeeder extends Seeder
{
    use WithoutModelEvents;
    public function run(): void {
        if (!app()->environment(['local','testing'])) throw new \RuntimeException('Local demo only.');
        DB::transaction(function () {
            $org = Organization::where('slug','hilton-demo')->sole();
            $org->update(['name'=>'Hilton']);
            foreach (HotelUnit::where('organization_id',$org->id)->where('kind','hotel')->with('parent','channel')->get() as $hotel) {
                $hotel->channel->update(['type'=>'public','audience_profile'=>'public']);
                if ($hotel->parent) {
                    foreach (DB::table('channel_user')->where('channel_id',$hotel->parent->channel_id)->where('role','admin')->where('status','approved')->get() as $grant) {
                        DB::table('channel_user')->updateOrInsert(['channel_id'=>$hotel->channel_id,'user_id'=>$grant->user_id],['role'=>'admin','status'=>'approved','can_post'=>true,'can_edit'=>true,'can_delete'=>true,'joined_at'=>now(),'source'=>'invite']);
                    }
                    $hotel->channel->parentChannels()->detach($hotel->parent->channel_id);
                    $hotel->update(['parent_id'=>null]);
                }
                $ensure = function ($label, $parent) use ($org,$hotel) {
                    $channel = Channel::firstOrCreate(['organization_id'=>$org->id,'slug'=>Str::slug($hotel->city.' · '.$label)],['name'=>$label,'type'=>'private','audience_profile'=>'business','status'=>'active']);
                    $channel->parentChannels()->sync([$parent->id]);
                    return $channel;
                };
                $staff = $ensure('Staff',$hotel->channel);
                foreach (['Housekeeping','Food & Beverage','Maintenance'] as $label) {
                    $ensure($label,$staff);
                }
                $desk = $ensure('Front Desk',$hotel->channel);
                $ensure('Night Shift',$desk);
                $dining = $ensure('Food & Beverage',$staff);
                $ensure('Breakfast Team',$dining);
                $guest = Channel::where('organization_id',$org->id)->where('slug',Str::slug($hotel->city.' · Guest Lounge'))->first();
                $offers = Channel::where('organization_id',$org->id)->where('slug',Str::slug($hotel->city.' · Offers & Experiences'))->first();
                if ($guest && $offers) $offers->parentChannels()->sync([$guest->id]);
                foreach (Channel::where('organization_id',$org->id)->where('name','like',$hotel->city.' · %')->get() as $channel) {
                    $name = Str::after($channel->name,' · ');
                    $channel->update(['name'=>$name==='Guest Lounge'?'Guest':$name]);
                }
            }
            foreach (HotelUnit::where('organization_id',$org->id)->where('kind','country')->get() as $country) {
                $country->channel->update(['status'=>'archived']);
            }
        });
    }
}
