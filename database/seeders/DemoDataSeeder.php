<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Channel;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Post;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = $this->seedOrganizations();
        $this->seedUsers($organizations);
        $this->seedBrandsAndChannels($organizations);
        $this->seedPosts($organizations);
        $this->seedUserPreferences();
    }

    /**
     * Layer 1 data: generic organizations across industries (not hotel-specific),
     * with a heavier weighting toward hotel groups since hospitality is the
     * primary reference vertical for demos.
     */
    private function seedOrganizations(): array
    {
        $data = [
            [
                'name' => 'Cairo International University',
                'slug' => 'cairo-university',
                'description' => 'A leading university with multiple faculties and campuses.',
                'email' => 'info@cairo-univ.edu',
                'phone' => '+20-100-000-0001',
                'address' => '123 University Road',
                'city' => 'Cairo',
                'country' => 'Egypt',
            ],
            [
                'name' => 'Grand Palace Hotels & Resorts',
                'slug' => 'grand-palace-hotel',
                'description' => 'A luxury hotel group with properties across Egypt\'s Red Sea and Mediterranean coasts.',
                'email' => 'info@grandpalace.com',
                'phone' => '+20-100-000-0002',
                'address' => '456 Beach Road',
                'city' => 'Alexandria',
                'country' => 'Egypt',
            ],
            [
                'name' => 'MegaMart Hypermarket',
                'slug' => 'megamart',
                'description' => 'A nationwide hypermarket with weekly offers and multiple branches.',
                'email' => 'info@megamart.com',
                'phone' => '+20-100-000-0003',
                'address' => '789 Commerce Ave',
                'city' => 'Giza',
                'country' => 'Egypt',
            ],
            [
                'name' => 'Nile Breeze Resorts',
                'slug' => 'nile-breeze-resorts',
                'description' => 'Boutique riverside resorts and floating hotels along the Nile.',
                'email' => 'info@nilebreeze.com',
                'phone' => '+20-100-000-0004',
                'address' => '12 Corniche El Nil',
                'city' => 'Luxor',
                'country' => 'Egypt',
            ],
            [
                'name' => 'Royal Oasis Hotels',
                'slug' => 'royal-oasis-hotels',
                'description' => 'Desert oasis resorts offering spa retreats and eco-tourism experiences.',
                'email' => 'info@royaloasis.com',
                'phone' => '+20-100-000-0005',
                'address' => '5 Oasis Highway',
                'city' => 'Siwa',
                'country' => 'Egypt',
            ],
        ];

        $organizations = [];
        foreach ($data as $row) {
            $organizations[] = Organization::firstOrCreate(
                ['slug' => $row['slug']],
                array_merge($row, ['is_active' => true, 'status' => 'active'])
            );
        }

        return $organizations;
    }

    /**
     * Each organization gets brands; each brand gets public + private channels.
     * Hotel groups get one brand per physical property, matching how a real
     * hotel chain would structure its channels (each property manages its own
     * announcements/events/staff room, but rolls up under the group).
     */
    private function seedBrandsAndChannels(array $organizations): void
    {
        $channelAdmin = User::where('email', 'o.o@o.com')->first();

        $brandsByOrg = [
            'cairo-university' => ['Faculty of Engineering', 'Faculty of Medicine'],
            'grand-palace-hotel' => ['Grand Palace Downtown Cairo', 'Grand Palace Marina Alexandria', 'Grand Palace Red Sea Hurghada'],
            'megamart' => ['MegaMart Retail', 'MegaMart Wholesale'],
            'nile-breeze-resorts' => ['Nile Breeze Luxor', 'Nile Breeze Aswan Floating Hotel'],
            'royal-oasis-hotels' => ['Royal Oasis Siwa Spa Resort', 'Royal Oasis Bahariya Eco-Lodge'],
        ];

        foreach ($organizations as $organization) {
            $brandNames = $brandsByOrg[$organization->slug] ?? ['Main Brand'];

            foreach ($brandNames as $brandName) {
                $brand = Brand::firstOrCreate(
                    ['organization_id' => $organization->id, 'slug' => Str::slug($brandName)],
                    ['name' => $brandName, 'is_active' => true]
                );

                $channels = [
                    ['name' => 'Announcements', 'type' => 'public'],
                    ['name' => 'Events', 'type' => 'public'],
                    ['name' => 'Staff Room', 'type' => 'private'],
                ];

                foreach ($channels as $channelData) {
                    Channel::firstOrCreate(
                        [
                            'organization_id' => $organization->id,
                            'brand_id' => $brand->id,
                            'name' => $channelData['name'] . ' — ' . $brandName,
                        ],
                        [
                            'slug' => Str::slug($channelData['name']) . '-' . Str::random(6),
                            'type' => $channelData['type'],
                            'status' => 'active',
                            'admin_user_id' => $channelAdmin?->id ?? 1,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Three test users, one per layer, wired through organization_memberships.
     */
    private function seedUsers(array $organizations): void
    {
        // Layer 1 — Super Admin (I-PAGE)
        $superAdmin = User::firstOrCreate(
            ['email' => 'donia.a5ra2019@gmail.com'],
            [
                'ipage_id' => 'IP000001',
                'first_name' => 'Donia',
                'last_name' => 'Admin',
                'password' => bcrypt('123456789'),
                'mobile' => '+201000000001',
                'gender' => 'female',
                'nationality' => 'Egypt',
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->syncRoles(['super_admin']);

        // Layer 2 — Organization Admin of the first organization
        $orgAdmin = User::firstOrCreate(
            ['email' => 'o.o@o.com'],
            [
                'ipage_id' => 'IP000002',
                'first_name' => 'Organization',
                'last_name' => 'Admin',
                'password' => bcrypt('123456789'),
                'mobile' => '+201000000002',
                'gender' => 'male',
                'nationality' => 'Egypt',
                'email_verified_at' => now(),
            ]
        );
        $orgAdmin->syncRoles(['organization_admin']);

        OrganizationMembership::updateOrCreate(
            ['organization_id' => $organizations[0]->id, 'user_id' => $orgAdmin->id],
            ['role' => 'organization_admin', 'status' => 'active', 'joined_date' => now()]
        );

        // Layer 3 — End user (no org membership; discovers/follows/subscribes)
        $member = User::firstOrCreate(
            ['email' => 'p.p@p.com'],
            [
                'ipage_id' => 'IP000003',
                'first_name' => 'User',
                'last_name' => 'Member',
                'password' => bcrypt('123456789'),
                'mobile' => '+201000000003',
                'gender' => 'male',
                'nationality' => 'Egypt',
                'email_verified_at' => now(),
            ]
        );
        $member->syncRoles(['member']);
    }

    /**
     * Realistic, hand-written post copy per channel type — deliberately avoids
     * Faker/fake() so seeding works in production where dev dependencies
     * (fakerphp/faker) are not installed.
     */
    private function seedPosts(array $organizations): void
    {
        $author = User::where('email', 'o.o@o.com')->first();

        $announcementPosts = [
            ['title' => 'Renovated Lobby Now Open', 'body' => "We're delighted to unveil our newly renovated lobby, featuring a redesigned reception area, a dedicated concierge desk, and a coffee lounge for our guests and visitors. Stop by and experience the new look!"],
            ['title' => 'New Contactless Check-In', 'body' => 'Starting this week, all guests can complete check-in through our mobile app — skip the front desk queue and go straight to your room. Ask our staff for the app download link.'],
            ['title' => 'Extended Wi-Fi Coverage', 'body' => 'High-speed Wi-Fi is now available across all outdoor terraces, the pool deck, and the parking area, in addition to every room and public space.'],
            ['title' => 'Holiday Schedule Update', 'body' => 'Please note that our front office, spa, and restaurant will operate on adjusted holiday hours next week. Full schedule is posted at the reception desk and on our app.'],
        ];

        $eventPosts = [
            ['title' => 'Live Jazz Night This Friday', 'body' => 'Join us this Friday from 8 PM at the poolside terrace for a live jazz performance, accompanied by a curated tapas menu from our head chef.'],
            ['title' => 'Weekend Brunch Buffet', 'body' => "Our signature weekend brunch returns this Saturday and Sunday, 12 PM to 4 PM, featuring international cuisine stations, a live pasta bar, and free-flowing juices for the whole family."],
            ['title' => 'Kids Summer Activity Camp', 'body' => 'Registration is now open for our supervised kids activity camp, running daily from 10 AM to 1 PM throughout the summer season. Includes arts & crafts, swimming lessons, and beach games.'],
            ['title' => 'Sunset Yoga Sessions', 'body' => 'Unwind with a complimentary sunset yoga session on the beach every Tuesday and Thursday at 6 PM. Mats provided, all levels welcome.'],
        ];

        $staffPosts = [
            ['title' => 'Shift Handover Reminder', 'body' => 'Please make sure all shift handover notes are logged in the system before clocking out. This includes VIP arrivals, maintenance requests, and any guest complaints still open.'],
            ['title' => 'Monthly Team Meeting', 'body' => 'The monthly all-staff meeting is scheduled for next Monday at 9 AM in the main conference room. Attendance is mandatory for all department heads.'],
            ['title' => 'Updated Uniform Policy', 'body' => 'A reminder that the updated uniform and grooming policy takes effect starting next week. Please collect your new name badges from HR by Thursday.'],
            ['title' => 'Safety Training Session', 'body' => 'Fire safety and emergency evacuation refresher training will be held for all staff this Wednesday at 2 PM. Please confirm your attendance with your supervisor.'],
        ];

        $postsByChannelName = [
            'Announcements' => $announcementPosts,
            'Events' => $eventPosts,
            'Staff Room' => $staffPosts,
        ];

        foreach ($organizations as $organization) {
            foreach ($organization->channels as $channel) {
                $baseName = trim(explode('—', $channel->name)[0]);
                $pool = $postsByChannelName[$baseName] ?? $announcementPosts;

                foreach ($pool as $index => $postData) {
                    Post::firstOrCreate(
                        [
                            'channel_id' => $channel->id,
                            'title' => $postData['title'],
                        ],
                        [
                            'organization_id' => $organization->id,
                            'brand_id' => $channel->brand_id,
                            'author_id' => $author?->id ?? 1,
                            'body' => $postData['body'],
                            'summary' => Str::limit($postData['body'], 120),
                            'post_type' => 'announcement',
                            'priority' => 'medium',
                            'language' => 'en',
                            'audience' => $channel->type === 'public' ? 'all' : 'team',
                            'status' => 'published',
                            'published_at' => now()->subDays(count($pool) - $index),
                        ]
                    );
                }
            }
        }
    }

    private function seedUserPreferences(): void
    {
        User::all()->each(function ($user) {
            UserPreference::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'color_scheme' => 'navy',
                    'font_size' => 'medium',
                    'language' => 'en',
                    'compact_mode' => false,
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'sms_notifications' => false,
                    'notify_new_guest' => true,
                    'notify_channel_updates' => true,
                    'notify_system_alerts' => false,
                    'notify_weekly_report' => true,
                ]
            );
        });
    }
}
