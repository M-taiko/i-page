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
     * Layer 1 data: generic organizations across industries (not hotel-specific).
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
                'name' => 'Grand Palace Hotel',
                'slug' => 'grand-palace-hotel',
                'description' => 'A luxury hotel chain with world-class amenities.',
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
     */
    private function seedBrandsAndChannels(array $organizations): void
    {
        $channelAdmin = User::where('email', 'o.o@o.com')->first();

        $brandsByOrg = [
            'cairo-university' => ['Faculty of Engineering', 'Faculty of Medicine'],
            'grand-palace-hotel' => ['Grand Palace Downtown', 'Grand Palace Marina'],
            'megamart' => ['MegaMart Retail', 'MegaMart Wholesale'],
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

    private function seedPosts(array $organizations): void
    {
        $author = User::where('email', 'o.o@o.com')->first();

        foreach ($organizations as $organization) {
            foreach ($organization->channels as $channel) {
                for ($i = 1; $i <= 3; $i++) {
                    Post::factory()->published()->create([
                        'channel_id' => $channel->id,
                        'organization_id' => $organization->id,
                        'brand_id' => $channel->brand_id,
                        'author_id' => $author?->id ?? 1,
                        'body' => "Test post #$i in {$channel->name}.",
                        'audience' => $channel->type === 'public' ? 'all' : 'team',
                    ]);
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
