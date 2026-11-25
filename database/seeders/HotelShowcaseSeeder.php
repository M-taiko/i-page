<?php
namespace Database\Seeders;

use App\Models\{Organization,OrganizationTemplate,OrganizationMembership,Channel,User,Post,AppointmentBooking};
use App\Services\{TenantServiceCatalog,AppointmentBookingService,DefaultCollections};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB,Hash,Http,Storage};
use Illuminate\Support\Str;

/** Fictional local presentation data. Re-running reuses the same records. */
class HotelShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local','testing'])) throw new \RuntimeException('Local demo only.');
        $photos = [
            'hotel'=>'photo-1566073771259-6a8506099945',
            'room'=>'photo-1611892440504-42a792e24d32',
            'restaurant'=>'photo-1414235077428-338989a2e8c0',
            'pool'=>'photo-1576013551627-0cc20b96c2a7',
            'spa'=>'photo-1540555700478-4be289fbecef',
        ];
        foreach ($photos as $key=>$photo) {
            $path='demo/hotels/'.$key.'.jpg';
            if (!Storage::disk('public')->exists($path)) {
                $response=Http::timeout(40)->retry(2,500)->get('https://images.unsplash.com/'.$photo,['w'=>1200,'q'=>80,'fit'=>'crop']);
                $response->throw();
                if (!str_starts_with($response->header('Content-Type')??'', 'image/')) throw new \RuntimeException('Invalid demo image: '.$key);
                Storage::disk('public')->put($path,$response->body());
            }
            $photos[$key]=$path;
        }
        $password=Hash::make('123456789');
        DB::transaction(function () use($photos,$password) {
            foreach ([
                ['hilton-demo','Hilton','Cairo','admin@hilton.test','EGP'],
                ['azure-demo','Azure Coast Resorts','Hurghada','admin@azure.demo.test','EGP'],
                ['cedar-demo','Cedar Boutique Hotels','Dubai','admin@cedar.demo.test','AED'],
            ] as [$slug,$name,$city,$email,$currency]) {
                $org=Organization::firstOrCreate(['slug'=>$slug],['name'=>$name,'organization_template_id'=>OrganizationTemplate::where('key','hotel')->sole()->id,'description'=>'Fictional hotel group for product demonstrations.','city'=>$city,'country'=>$currency==='AED'?'United Arab Emirates':'Egypt','email'=>$email,'status'=>'active','is_active'=>true,'max_channels'=>200]);
                $design=$org->design_options??[];
                $design['cover_paths']=[$photos['hotel'],$photos['pool'],$photos['room']];
                $org->update(['logo_path'=>$photos['hotel'],'design_options'=>$design]);
                $admin=$this->user($email,$name,'Admin',$password,'organization_admin');
                OrganizationMembership::updateOrCreate(['organization_id'=>$org->id,'user_id'=>$admin->id],['role'=>'organization_admin','status'=>'active','can_add_services'=>true,'can_edit_services'=>true,'can_delete_services'=>true]);
                app(TenantServiceCatalog::class)->seedDefaults($org->fresh('template'));
                $guests=[];
                foreach(['Mariam','Omar','Nour','Karim','Laila','Adam','Salma','Youssef'] as $i=>$first) {
                    $guests[]=$this->user('guest'.($i+1).'@'.$slug.'.test',$first,'Guest',$password,'member');
                }
                foreach (['Waterfront','City Gardens'] as $branchIndex=>$branch) {
                    $root=Channel::firstOrCreate(['organization_id'=>$org->id,'slug'=>$slug.'-'.Str::slug($branch)],['name'=>$name.' · '.$branch,'type'=>'public','audience_profile'=>'public','status'=>'active','admin_user_id'=>$admin->id]);
                    $options=$root->design_options??[];$options['cover_paths']=[$photos[$branchIndex?'room':'hotel']];$options['modules']=['bookings'];
                    $root->update(['design_options'=>$options,'logo_path'=>$photos['hotel']]);
                    $root->users()->syncWithoutDetaching([$admin->id=>['role'=>'admin','status'=>'approved','can_post'=>true,'can_edit'=>true,'can_delete'=>true,'can_manage_bookings'=>true]]);
                    $channel=Channel::firstOrCreate(['organization_id'=>$org->id,'slug'=>$root->slug.'-experiences'],['name'=>'Offers & Experiences','type'=>'public','audience_profile'=>'public','status'=>'active','admin_user_id'=>$admin->id]);
                    $channel->parentChannels()->syncWithoutDetaching([$root->id]);
                    $channel->update(['logo_path'=>$photos['pool'],'design_options'=>['cover_paths'=>[$photos['pool'],$photos['restaurant']],'modules'=>['bookings']]]);
                    $channel->users()->syncWithoutDetaching([$admin->id=>['role'=>'admin','status'=>'approved','can_post'=>true,'can_edit'=>true,'can_delete'=>true,'can_manage_bookings'=>true]]);
                    foreach($guests as $guest) $channel->users()->syncWithoutDetaching([$guest->id=>['role'=>'member','status'=>'approved','joined_at'=>now()]]);
                    foreach ([
                        ['room','A weekend made for you','Unwind in a beautiful room with breakfast included.', $currency==='AED'?650:2400],
                        ['restaurant','Breakfast worth waking up for','A generous buffet, fresh pastries and a relaxed morning.', $currency==='AED'?95:450],
                        ['pool','Your poolside escape','Reserve a relaxing afternoon by the pool with friends or family.', $currency==='AED'?150:600],
                        ['spa','An hour just for you','Slow down with a restorative wellness appointment.', $currency==='AED'?280:1100],
                    ] as $n=>[$kind,$title,$body,$price]) {
                        $service=\App\Models\AppointmentService::where('organization_id',$org->id)->whereNull('channel_id')->where('kind',$kind)->firstOrFail();
                        $service->update(['price_from'=>$price,'currency'=>$currency]);
                        $post=Post::firstOrCreate(['organization_id'=>$org->id,'channel_id'=>$channel->id,'title'=>$title],['author_id'=>$admin->id,'body'=>$body,'summary'=>$body,'image_path'=>$photos[$kind],'post_type'=>'offer','post_format'=>'standard','card_style'=>'image','badge_key'=>'featured','status'=>'published','published_at'=>now()->subDays($n+1),'audience'=>'channel','cascade'=>false,'options'=>['promotion'=>true,'booking_service_id'=>$service->id,'booking_button_label'=>'Reserve your experience','reactions'=>['like','love','celebrate']]]);
                        foreach($guests as $j=>$guest) {
                            $post->reactions()->firstOrCreate(['user_id'=>$guest->id],['type'=>['love','like','celebrate'][$j%3]]);
                            if($j<3) $post->comments()->firstOrCreate(['user_id'=>$guest->id,'body'=>['This looks like the perfect weekend plan!','Can we book this experience for two people?','We loved our last visit. Looking forward to coming back.'][$j]]);
                        }
                        $guest=$guests[$n];$marker='Showcase '.$slug.' '.$branch.' '.$kind;
                        if(!AppointmentBooking::where('organization_id',$org->id)->where('customer_notes',$marker)->exists()) {
                            $bookings=app(AppointmentBookingService::class);
                            $booking=$bookings->create($channel,$guest,['submission_key'=>(string)Str::uuid(),'service_id'=>$service->id,'date'=>now()->addDays(3+$n+$branchIndex*8)->toDateString(),'time'=>'11:00','guests'=>2,'customer_name'=>$guest->full_name,'customer_notes'=>$marker,'marketing_consent'=>false]);
                            if($n>0) $bookings->update($booking,$admin,['status'=>'contacted','assigned_to'=>$admin->id,'customer_visible'=>true,'message'=>'Our team has reviewed your request and contacted you to confirm the details.']);
                            if($n>1) $bookings->update($booking,$admin,['status'=>$n===2?'confirmed':'follow_up','customer_visible'=>true,'message'=>$n===2?'Your appointment is confirmed. We look forward to welcoming you.':'We are checking your preferred time and will follow up shortly.']);
                        }
                    }
                }
                foreach($guests as $guest) app(DefaultCollections::class)->createFor($guest);
            }
            // Give the existing presentation posts a locally cached photograph too.
            Post::where('organization_id',Organization::where('slug','hilton-demo')->value('id'))->whereNull('image_path')->where('status','published')->each(function($post)use($photos){$post->update(['image_path'=>$photos[array_keys($photos)[$post->id%count($photos)]]]);});
        });
        $mismatch=DB::table('appointment_bookings as b')->join('appointment_services as s','s.id','=','b.service_id')->where('b.customer_notes','like','Showcase %')->whereColumn('b.organization_id','!=','s.organization_id')->count();
        if($mismatch) throw new \RuntimeException('Showcase tenant isolation check failed.');
        $count=AppointmentBooking::where('customer_notes','like','Showcase %')->count();
        $this->command?->info("Showcase bookings: {$count}; cross-tenant service mismatches: {$mismatch}.");
        $this->command?->info('Three independent hotel tenants, six showcase properties, 24 illustrated offers and 24 service bookings are ready. Demo password: 123456789. Photos: Unsplash; all activity is fictional.');
    }

    private function user($email,$first,$last,$password,$role): User
    {
        $user=User::firstOrNew(['email'=>$email]);
        if(!$user->exists) $user->forceFill(['ipage_id'=>'IP'.Str::upper(Str::random(12)),'username'=>Str::before($email,'@').'-'.substr(md5($email),0,6),'first_name'=>$first,'last_name'=>$last,'profile_level'=>'basic','language'=>'en']);
        $user->forceFill(['password'=>$password,'email_verified_at'=>now()])->save();
        $user->assignRole($role);return $user;
    }
}
