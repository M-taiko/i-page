<?php

namespace Database\Seeders;

use App\Models\{Channel, HotelUnit, Organization, OrganizationMembership, OrganizationTemplate, Post, User};
use App\Services\{DefaultCollections, HotelHierarchyService};
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Hash};
use Illuminate\Support\Str;

/** Local hotel demonstration. Run on an empty database after the foundational seeders. */
class HotelDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    private string $password;
    private array $accounts = [];
    private int $sequence = 0;
    private Organization $organization;

    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Hotel demo accounts are only allowed in local/testing environments.');
        }
        if (User::withTrashed()->exists() || Organization::withTrashed()->exists()) {
            $this->presentation();
            $this->call(HotelShowcaseSeeder::class);
            return;
        }
        $this->password = Hash::make('123456789');
        DB::transaction(function () {
            $this->person('donia.a5ra2019@gmail.com', 'Donia', 'Admin', 'super_admin', 'Platform administrator');
            $this->organization = Organization::create([
                'name'=>'Hilton', 'slug'=>'hilton-demo',
                'organization_template_id'=>OrganizationTemplate::where('key','hotel')->sole()->id,
                'description'=>'Fictional demonstration of a hotel group, country teams and independent properties.',
                'email'=>'info@hilton.test','city'=>'Cairo','country'=>'Egypt',
                'is_active'=>true,'status'=>'active','max_channels'=>200,
            ]);
            $groupAdmin = $this->employee('admin@hilton.test', 'Group', 'Admin', 'Entire hotel group', 'organization_admin');
            $service = app(HotelHierarchyService::class);
            $countries = [
                ['eg','Egypt','Africa/Cairo','EGP',['cairo'=>'Cairo','hurghada'=>'Hurghada']],
                ['ae','United Arab Emirates','Asia/Dubai','AED',['dubai'=>'Dubai','abudhabi'=>'Abu Dhabi']],
            ];
            $propertyChannels = [];
            foreach ($countries as [$code,$countryName,$timezone,$currency,$cities]) {
                $countryAdmin = $this->employee($code.'.admin@hilton.test', $countryName, 'Manager', $countryName.' and its hotels');
                foreach ($cities as $key=>$city) {
                    $hotel = $service->create($this->organization,$groupAdmin,[
                        'kind'=>'hotel','name'=>'Hilton '.$city,'country'=>$countryName,'city'=>$city,'timezone'=>$timezone,'currency'=>$currency,
                    ]);
                    $this->grant($hotel->channel,$countryAdmin,'admin');
                    $admin = $this->employee($key.'.admin@hilton.test',$city,'Manager','Hilton '.$city.' and its departments');
                    $this->grant($hotel->channel,$admin,'admin');
                    $this->post($hotel->channel,$admin,'Welcome to the '.$city.' team','Your hotel workspace: announcements, departmental updates and collaboration.', ['card_style'=>'spotlight','requires_acknowledgment'=>true,'pinned_until'=>now()->addWeeks(2)]);
                    $departments = [];
                    $staff = $this->channel($city.' · Staff','private',$hotel->channel);
                    foreach (['reception'=>'Front Desk','housekeeping'=>'Housekeeping','dining'=>'Food & Beverage','maintenance'=>'Maintenance'] as $departmentKey=>$label) {
                        $channel = $this->channel($city.' · '.$label,'private',$departmentKey === 'reception' ? $hotel->channel : $staff);
                        $departments[$departmentKey] = $channel;
                        $lead = $this->employee($key.'.'.$departmentKey.'@hilton.test',$city,$label,$label.' in '.$city.' only');
                        $this->grant($channel,$lead,'admin');
                        $this->post($channel,$lead,'Daily '.$label.' briefing','Review today’s priorities and share updates with your department.');
                        $this->post($channel,$lead,'Next shift handover','Team checklist and notes for the next shift.', ['status'=>'draft','published_at'=>null]);
                    }
                    // Public channels contain only customer-facing content. Do not cascade internal posts into them.
                    $guests = $this->channel($city.' · Guest Lounge','public',$hotel->channel);
                    $offers = $this->channel($city.' · Offers & Experiences','public',$hotel->channel);
                    $offers->parentChannels()->sync([$guests->id]);
                    $this->channel($city.' · Night Shift','private',$departments['reception']);
                    $this->channel($city.' · Breakfast Team','private',$departments['dining']);
                    $propertyChannels[$key] = $departments + ['hotel'=>$hotel->channel,'guests'=>$guests,'offers'=>$offers];
                    $this->post($guests,$admin,'Welcome to Hilton '.$city,'Discover dining experiences, hotel news and activities. Share your thoughts with our team.', ['card_style'=>'gradient','pinned_until'=>now()->addMonth()]);
                    $this->post($offers,$admin,'A weekend to remember','Explore our weekend dining experiences. Contact reception for details.', ['post_type'=>'offer','card_style'=>'spotlight','badge_key'=>'featured']);
                    $poll = $this->post($guests,$admin,'What would you like to try next?','Help us plan your next experience.', ['post_format'=>'poll','options'=>['poll'=>['multiple'=>false,'show_results'=>'always'],'reactions'=>['like','love']]]);
                    foreach (['Local food tasting','Live music evening','Family activities'] as $order=>$label) {
                        $poll->pollOptions()->create(['label'=>$label,'sort_order'=>$order]);
                    }
                    for ($i=1; $i<=3; $i++) {
                        $guest = $this->person($key.'.guest'.$i.'@demo.test','Guest '.$i,$city,'member','Public guest channels in '.$city);
                        foreach ([$guests,$offers] as $publicChannel) $this->grant($publicChannel,$guest,'member');
                        DB::table('organization_followers')->insert(['organization_id'=>$this->organization->id,'user_id'=>$guest->id,'created_at'=>now(),'updated_at'=>now()]);
                        $poll->reactions()->create(['user_id'=>$guest->id,'type'=>'like']);
                        $poll->comments()->create(['user_id'=>$guest->id,'body'=>['Looking forward to the next event!','A lovely experience, thank you.','Would love to try the local food.'][$i-1]]);
                        $poll->receipts()->create(['user_id'=>$guest->id,'delivered_at'=>now(),'first_viewed_at'=>now(),'read_at'=>now()]);
                    }
                }
            }
            $cairo = $propertyChannels['cairo'];
            $publisher = $this->employee('publisher@hilton.test','Mona','Publisher','Publish only in Cairo Guest Lounge');
            $this->grant($cairo['guests'],$publisher,'member',['can_post'=>true]);
            $editor = $this->employee('editor@hilton.test','Omar','Editor','Edit only in Cairo Offers');
            $this->grant($cairo['offers'],$editor,'member',['can_edit'=>true]);
            $viewer = $this->employee('viewer@hilton.test','Sara','Viewer','Read only in Cairo Front Desk');
            $this->grant($cairo['reception'],$viewer,'member');
            $multi = $this->employee('multi@hilton.test','Nour','Content','Publish in Cairo Guest Lounge and Offers; edit Offers only');
            $this->grant($cairo['guests'],$multi,'member',['can_post'=>true]);
            $this->grant($cairo['offers'],$multi,'member',['can_post'=>true,'can_edit'=>true]);
            $this->post($cairo['guests'],$publisher,'Upcoming guest activities','A proposed activities announcement awaiting review.', ['status'=>'pending_approval','published_at'=>null]);
            $applicant = $this->person('applicant@demo.test','New','Applicant','member','Pending organization membership');
            OrganizationMembership::create(['organization_id'=>$this->organization->id,'user_id'=>$applicant->id,'role'=>'staff','status'=>'pending']);
            $this->grant($cairo['housekeeping'],$viewer,'member',[], 'pending');
            foreach (User::all() as $user) app(DefaultCollections::class)->createFor($user);
        });
        $this->presentation();
        $this->call(HotelShowcaseSeeder::class);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $file = fopen(storage_path('app/hotel-demo-accounts.csv'), 'w');
        fputcsv($file,['Email','Global role','Scope']);
        foreach ($this->accounts as $row) fputcsv($file,$row);
        fclose($file);
        $this->command?->info('Hotel demo created: 2 countries, 4 hotels; accounts in storage/app/hotel-demo-accounts.csv. Local demo password: 123456789');
    }

    private function presentation(): void
    {
        DB::transaction(function () {
            $org = Organization::where('slug', 'hilton-demo')->firstOrFail();
            $password = Hash::make('123456789');
            $make = function ($email, $first, $last, $role) use ($password) {
                $user = User::firstOrNew(['email'=>$email]);
                if (!$user->exists) $user->forceFill(['ipage_id'=>'IP'.Str::upper(Str::random(12)), 'username'=>Str::before($email,'@'), 'first_name'=>$first, 'last_name'=>$last, 'profile_level'=>'basic', 'language'=>'en']);
                $user->forceFill(['password'=>$password, 'email_verified_at'=>now()])->save();
                $user->assignRole($role);
                return $user;
            };
            $make('donia.a5ra2019@gmail.com','Donia','Admin','super_admin');
            $admin=$make('admin@hilton.test','Mona','Hassan','organization_admin');
            OrganizationMembership::updateOrCreate(['organization_id'=>$org->id,'user_id'=>$admin->id],['role'=>'organization_admin','status'=>'active','joined_date'=>now()->subMonths(3)]);
            $names=['Ahmed','Nour','Salma','Omar','Laila','Youssef','Hana','Karim','Mariam','Adam'];
            $guests=[];
            for($i=1;$i<=40;$i++) $guests[]=$make('hotel.guest'.$i.'@demo.test',$names[($i-1)%10],['Hassan','Ali','Mansour','Adel'][($i-1)%4],'member');
            $topics=[
                ['Breakfast with a view','Start your morning with fresh pastries, seasonal fruit and a relaxed breakfast overlooking the gardens.'],
                ['A quieter afternoon by the pool','Take a break, enjoy the sunshine and share your favourite poolside moment with us.'],
                ['Discover local flavours','Our chefs are preparing a selection inspired by local recipes. Which dish would you like to try?'],
                ['Your weekend escape','Enjoy a relaxed weekend with good food, welcoming spaces and time for the people who matter.'],
                ['A little time for yourself','Explore a calmer side of your stay with our spa and wellness experiences.'],
                ['Family moments','We are planning activities for all ages. Tell us what would make your family stay special.'],
                ['Meet the people behind your stay','Thank you to our reception and housekeeping teams for the care they bring to every guest experience.'],
                ['An evening to remember','Join the conversation and share your favourite moments from your stay.'],
            ];
            $comments=['The breakfast selection looks wonderful.','Is this suitable for families with young children?','We enjoyed our last visit and would love to return.','The team made us feel very welcome. Thank you!','Could you share the available times?','The garden is our favourite place to relax.','Looking forward to trying this on our next stay.','A lovely idea for the weekend.'];
            foreach(Channel::where('organization_id',$org->id)->where('type','public')->get() as $channel){
                $manager=$make('channel.'.$channel->id.'@hilton.test','Channel','Manager','staff');
                OrganizationMembership::updateOrCreate(['organization_id'=>$org->id,'user_id'=>$manager->id],['role'=>'staff','status'=>'active','joined_date'=>now()->subMonths(2)]);
                $channel->users()->syncWithoutDetaching([$manager->id=>['role'=>'admin','status'=>'approved','can_post'=>true,'can_edit'=>true,'can_delete'=>true,'joined_at'=>now()]]);
                foreach($guests as $g) $channel->users()->syncWithoutDetaching([$g->id=>['role'=>'member','status'=>'approved','joined_at'=>now()]]);
                for($n=0;$n<16;$n++){
                    [$title,$body]=$topics[$n%8]; $title.=' · '.($n<8?'This week':'Next week');
                    $post=Post::firstOrCreate(['channel_id'=>$channel->id,'title'=>$title,'author_id'=>$manager->id],['organization_id'=>$org->id,'summary'=>$body,'body'=>$body,'status'=>'published','published_at'=>now()->subDays(16-$n),'post_type'=>'announcement','post_format'=>'standard','card_style'=>['classic','spotlight','gradient'][$n%3],'language'=>'en','audience'=>'channel','cascade'=>false,'options'=>['reactions'=>['like','love','celebrate']]]);
                    foreach(array_slice($guests,0,8+$n%19) as $j=>$guest){
                        $post->reactions()->firstOrCreate(['user_id'=>$guest->id],['type'=>['like','love','celebrate'][$j%3]]);
                        if($j<3+$n%5) $post->comments()->firstOrCreate(['user_id'=>$guest->id,'body'=>$comments[($j+$n)%8]]);
                        $post->receipts()->firstOrCreate(['user_id'=>$guest->id],['delivered_at'=>now()->subHours(3),'first_viewed_at'=>now()->subHours(2),'read_at'=>now()->subHour()]);
                    }
                }
            }
        });
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->command?->info('Hotel presentation data ready. All created demo accounts use 123456789. Interactions are simulated demo data.');
    }

    private function person(string $email,string $first,string $last,string $role,string $scope): User
    {
        $user = (new User)->forceFill(['ipage_id'=>'IP'.str_pad((string)++$this->sequence,6,'0',STR_PAD_LEFT),
            'username'=>Str::before($email,'@'), 'first_name'=>$first,'last_name'=>$last,'email'=>$email,
            'password'=>$this->password,'email_verified_at'=>now(),'mobile'=>'+201000'.str_pad((string)$this->sequence,6,'0',STR_PAD_LEFT),
            'profile_level'=>$role==='member'?'basic':'business','language'=>'en']);
        $user->save();
        $user->syncRoles([$role]);
        $this->accounts[] = [$email,$role,$scope];
        return $user;
    }

    private function employee(string $email,string $first,string $last,string $scope,string $role='staff'): User
    {
        $user = $this->person($email,$first,$last,$role,$scope);
        OrganizationMembership::create(['organization_id'=>$this->organization->id,'user_id'=>$user->id,'role'=>$role,'status'=>'active','job_title'=>$scope,'joined_date'=>now()->subMonths(3)]);
        return $user;
    }

    private function grant(Channel $channel,User $user,string $role,array $permissions=[],string $status='approved'): void
    {
        DB::table('channel_user')->insert(['channel_id'=>$channel->id,'user_id'=>$user->id,'role'=>$role,'status'=>$status,'joined_at'=>now(),'source'=>'invite',
            'can_post'=>$permissions['can_post']??($role==='admin'), 'can_edit'=>$permissions['can_edit']??($role==='admin'), 'can_delete'=>$permissions['can_delete']??($role==='admin')]);
    }

    private function channel(string $name,string $type,Channel $parent): Channel
    {
        $channel = Channel::create(['organization_id'=>$this->organization->id,'name'=>Str::after($name,' · ')==='Guest Lounge'?'Guest':Str::after($name,' · '),'slug'=>Str::slug($name),
            'type'=>$type,'audience_profile'=>$type==='private'?'business':'public','status'=>'active']);
        $channel->parentChannels()->attach($parent->id);
        return $channel;
    }

    private function post(Channel $channel,User $author,string $title,string $body,array $overrides=[]): Post
    {
        return Post::create(array_replace(['organization_id'=>$this->organization->id,'channel_id'=>$channel->id,'author_id'=>$author->id,
            'title'=>$title,'summary'=>Str::limit($body,120),'body'=>$body,'status'=>'published','published_at'=>now()->subHours(2),
            'post_type'=>'announcement','post_format'=>'standard','card_style'=>'classic','language'=>'en',
            'audience'=>'channel','cascade'=>false,'options'=>['reactions'=>['like','love','celebrate']]],$overrides));
    }
}
