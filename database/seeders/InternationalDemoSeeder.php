<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Post;
use App\Models\User;
use App\Models\UserPreference;
use Database\Seeders\Demo\DemoContent as C;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * International multi-industry demo (ADR-016): hotels, hypermarkets and schools.
 *
 *   Tenant (Hilton) → Country (Hilton Egypt) → Branch (Hilton Hurghada) → Departments → Sub-departments
 *
 * Every account uses the password 123456789. Posts get realistic views, reactions, comments,
 * team replies and poll votes. Deterministic (fixed random seed) so demos look the same each time.
 * The full account list is written to storage/app/demo-accounts.csv.
 */
class InternationalDemoSeeder extends Seeder
{
    private const PASSWORD = '123456789';
    private const SEED = 2026;

    private string $hash;
    private int $idCounter = 100;
    private int $staffNameIndex = 0;

    /** @var array<int, array{0:string,1:string,2:string}> email, role, scope */
    private array $accounts = [];
    /** @var array<string, User[]> visitors by country */
    private array $visitors = [];
    /** Posts to fill with interactions: [Post, sector, private, team ids]. */
    private array $postMeta = [];
    private array $cardStyles = ['classic', 'classic', 'gradient', 'spotlight'];

    public function run(): void
    {
        mt_srand(self::SEED);
        $this->hash = bcrypt(self::PASSWORD);

        $this->superAdmin();
        $this->createVisitors();

        foreach (C::TENANTS as $key => $tenant) {
            $this->tenant($key, $tenant);
        }

        $this->interactions();
        $this->preferences();
        $this->writeAccounts();
    }

    // ------------------------------------------------------------------ people

    private function superAdmin(): void
    {
        $superAdmin = User::firstOrCreate(['email' => 'donia.a5ra2019@gmail.com'], [
            'ipage_id' => 'IP000001', 'first_name' => 'Donia', 'last_name' => 'Admin', 'password' => $this->hash,
            'mobile' => '+201000000001', 'gender' => 'female', 'nationality' => 'Egypt', 'email_verified_at' => now(),
        ]);
        $superAdmin->syncRoles(['super_admin']);
        $this->accounts[] = [$superAdmin->email, 'Super admin', 'Platform'];
    }

    /** Visitors per country: the hand-picked names, then first/last combinations of the same country. */
    private const VISITORS_PER_COUNTRY = ['Egypt' => 45, 'Saudi Arabia' => 32, 'United Arab Emirates' => 36, 'Jordan' => 12];

    private function createVisitors(): void
    {
        $byCountry = collect(C::VISITORS)->groupBy(2);
        foreach (self::VISITORS_PER_COUNTRY as $country => $target) {
            $people = $byCountry[$country] ?? collect();
            $firsts = $people->pluck(0)->all();
            $lasts = $people->pluck(1)->all();
            $names = $people->map(fn ($p) => [$p[0], $p[1]])->all();
            for ($i = 0; count($names) < $target && $i < count($firsts) * count($lasts); $i++) {
                $candidate = [$firsts[$i % count($firsts)], $lasts[intdiv($i, count($firsts)) % count($lasts)]];
                if (! in_array($candidate, $names, true)) {
                    $names[] = $candidate;
                }
            }

            foreach ($names as [$first, $last]) {
                $email = Str::slug($first . '.' . $last, '.') . '@demo.test';
                $user = $this->user($email, $first, $last, 'member', $country);
                $this->visitors[$country][] = $user;
                $this->accounts[] = [$email, 'Visitor', $country];
            }
        }
    }

    private function user(string $email, string $first, string $last, string $role, string $country = 'Egypt'): User
    {
        $user = User::firstOrCreate(['email' => $email], [
            'ipage_id' => 'IP' . str_pad((string) $this->idCounter++, 6, '0', STR_PAD_LEFT),
            'first_name' => $first, 'last_name' => $last, 'password' => $this->hash,
            'mobile' => '+9' . str_pad((string) (100000000 + $this->idCounter * 7919), 11, '0', STR_PAD_LEFT),
            'nationality' => $country, 'email_verified_at' => now(),
        ]);
        $user->syncRoles([$role]);

        return $user;
    }

    /** A team member of $org with a realistic name. */
    private function teamMember(Organization $org, string $email, string $role, string $country, ?array $name = null): User
    {
        $name ??= C::STAFF_NAMES[$this->staffNameIndex++ % count(C::STAFF_NAMES)];
        $user = $this->user($email, $name[0], $name[1], $role, $country);
        OrganizationMembership::updateOrCreate(['organization_id' => $org->id, 'user_id' => $user->id],
            ['role' => $role, 'status' => 'active', 'joined_date' => now()->subMonths(mt_rand(2, 30))]);

        return $user;
    }

    // ------------------------------------------------------------------ structure

    private function tenant(string $key, array $t): void
    {
        $sector = $t['sector'];
        $domain = $key . '.test';
        $vars = ['{brand}' => $t['name']];

        $org = Organization::create([
            'name' => $t['name'], 'slug' => $key, 'description' => $t['description'],
            'email' => 'info@' . $domain, 'phone' => '+971-4-' . mt_rand(2000000, 4999999),
            'address' => $t['hq'][0] . ', ' . $t['hq'][1], 'city' => $t['hq'][0], 'country' => $t['hq'][1],
            'is_active' => true, 'status' => 'active',
        ]);

        $admin = $this->teamMember($org, 'admin@' . $domain, 'organization_admin', $t['hq'][1], [$t['name'], 'Admin']);
        $manager = $this->teamMember($org, 'manager@' . $domain, 'manager', $t['hq'][1]);
        $this->accounts[] = [$admin->email, 'Tenant admin', $t['name'] . ' — everything'];
        $this->accounts[] = [$manager->email, 'Tenant manager', $t['name'] . ' — everything'];

        $deptChannels = [];          // department name => [Channel, ...] across the tenant
        $tenantAudienceCountries = array_keys($t['countries']);

        foreach ($t['countries'] as $countryName => $cities) {
            $cc = C::COUNTRIES[$countryName];
            $countryVars = $vars + ['{country}' => $cc['short']];
            $country = $this->channel($org, $t['name'] . ' ' . $cc['short'], 'public', $admin);

            $countryAdmin = $this->teamMember($org, "{$cc['code']}.admin@{$domain}", 'staff', $countryName);
            $this->join($country, $countryAdmin, 'admin');
            $this->accounts[] = [$countryAdmin->email, 'Country admin', "{$t['name']} {$cc['short']} + all its branches"];

            foreach (C::COUNTRY_POSTS[$sector] as $i => [$title, $body]) {
                $this->post(['channel_id' => $country->id, 'cascade' => true, 'pinned_until' => $i === 0 ? now()->addYears(10) : null],
                    $org, $countryAdmin, strtr($title, $countryVars), strtr($body, $countryVars), mt_rand(12, 40), $sector, false, [$countryAdmin->id]);
            }

            foreach ($cities as $city) {
                $this->branch($org, $sector, $domain, $t['name'], $countryName, $cc, $city, $country, $deptChannels);
            }
        }

        // Visitors follow the tenant page.
        foreach ($tenantAudienceCountries as $countryName) {
            foreach ($this->visitors[$countryName] ?? [] as $visitor) {
                if (mt_rand(1, 100) <= 75) {
                    DB::table('organization_followers')->insertOrIgnore(['organization_id' => $org->id, 'user_id' => $visitor->id, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        }

        // Tenant posts: page only, every channel, staff rooms only, and a chosen department everywhere.
        $tp = C::TENANT_POSTS[$sector];
        $this->post(['placement' => 'page', 'show_on_page' => true], $org, $admin, strtr($tp['page'][0], $vars), strtr($tp['page'][1], $vars), 45, $sector, false, [$admin->id]);
        $this->post(['placement' => 'all', 'show_on_page' => true, 'pinned_until' => now()->addYears(10), 'badge_key' => 'featured'],
            $org, $admin, strtr($tp['all'][0], $vars), strtr($tp['all'][1], $vars), 6, $sector, false, [$admin->id]);

        $staffRoom = $sector === 'school' ? 'Teachers Room' : 'Staff Room';
        $staffPost = $this->post(['placement' => 'selected', 'show_on_page' => false], $org, $manager,
            strtr($tp['staff'][0], $vars), strtr($tp['staff'][1], $vars), 3, $sector, true, [$manager->id]);
        $staffPost->targetChannels()->sync(collect($deptChannels[$staffRoom] ?? [])->pluck('id')->all());

        [$deptName, $title, $body] = match ($sector) {
            'hotel' => ['Restaurants', 'Taste of the region festival', "A month-long food festival in every {brand} restaurant: regional dishes, live cooking and special family prices."],
            'market' => ['Fresh Food', 'Fresh food festival', "Two weeks of the freshest produce at {brand}: daily tastings, farmer's market corners and special prices."],
            default => ['Activities & Sports', 'Inter-school sports championship', "{brand} schools compete in football, basketball and swimming this month. Come and cheer for your school!"],
        };
        $festival = $this->post(['placement' => 'selected', 'show_on_page' => true, 'cascade' => true], $org, $admin,
            $title, strtr($body, $vars), 9, $sector, false, [$admin->id]);
        $festival->targetChannels()->sync(collect($deptChannels[$deptName] ?? [])->pluck('id')->all());
    }

    private function branch(Organization $org, string $sector, string $domain, string $brand, string $countryName, array $cc, string $city, Channel $country, array &$deptChannels): void
    {
        $slug = Str::slug($city);
        $vars = ['{brand}' => $brand, '{city}' => $city, '{country}' => $cc['short']];
        $branch = $this->channel($org, $brand . ' ' . $city, 'public', User::where('email', 'admin@' . $domain)->firstOrFail(), $country);

        $branchAdmin = $this->teamMember($org, "{$slug}.admin@{$domain}", 'staff', $countryName);
        $moderator = $this->teamMember($org, "{$slug}.moderator@{$domain}", 'moderator', $countryName);
        $frontLine = $this->teamMember($org, "{$slug}.staff@{$domain}", 'staff', $countryName);
        $staff2 = $this->teamMember($org, "{$slug}.staff2@{$domain}", 'staff', $countryName);
        $this->join($branch, $branchAdmin, 'admin');
        $this->join($branch, $moderator, 'moderator');
        $team = [$branchAdmin->id, $moderator->id, $frontLine->id, $staff2->id];

        $this->accounts[] = [$branchAdmin->email, 'Branch admin', "{$brand} {$city} + all its departments"];
        $this->accounts[] = [$moderator->email, 'Branch moderator', "{$brand} {$city} + all its departments"];

        // Departments and sub-departments.
        $depts = [];
        foreach (C::DEPARTMENTS[$sector] as [$name, $type, $children]) {
            $dept = $this->channel($org, $name, $type, $branchAdmin, $branch);
            $depts[$name] = $dept;
            $deptChannels[$name][] = $dept;
            foreach ($children as $childName) {
                $child = $this->channel($org, $childName, 'public', $branchAdmin, $dept);
                $depts[$childName] = $child;
                $deptChannels[$childName][] = $child;
            }
        }

        $frontLineDept = C::FRONT_LINE[$sector];
        $this->join($depts[$frontLineDept], $frontLine, 'member', ['can_post' => true]);
        foreach ($depts as $dept) {
            if ($dept->type === 'private') {
                $this->join($dept, $frontLine, 'member');
                $this->join($dept, $staff2, 'member');
            }
        }
        $this->accounts[] = [$frontLine->email, 'Staff (can post)', "{$brand} {$city} › {$frontLineDept}"];
        $this->accounts[] = [$staff2->email, 'Staff', "{$brand} {$city} › private channels"];

        // Visitors from this country subscribe to the branch and a few departments.
        $publicDepts = array_values(array_filter($depts, fn (Channel $c) => $c->type === 'public'));
        foreach ($this->visitors[$countryName] ?? [] as $visitor) {
            if (mt_rand(1, 100) > 65) {
                continue;
            }
            $this->join($branch, $visitor, 'member');
            if (mt_rand(1, 100) <= 40) {
                $this->join($country, $visitor, 'member');
            }
            foreach ($this->pick($publicDepts, mt_rand(2, 4)) as $dept) {
                $this->join($dept, $visitor, 'member', [], mt_rand(1, 100) <= 6);   // a few muted
            }
        }

        // Branch welcome (cascades into every department).
        $this->post(['channel_id' => $branch->id, 'cascade' => true], $org, $branchAdmin,
            "Welcome to {$brand} {$city}", "Follow this channel for everything happening at {$brand} {$city}: news, offers and events from every department.",
            mt_rand(20, 35), $sector, false, $team);

        // Department posts.
        foreach ($depts as $name => $dept) {
            $library = C::POSTS[$sector][$name] ?? [];
            foreach ($this->pick($library, min(count($library), mt_rand(1, 2))) as $entry) {
                [$title, $body] = $entry;
                $lang = $entry[2] ?? 'en';
                $author = $name === $frontLineDept && mt_rand(0, 1) ? $frontLine : (mt_rand(0, 3) ? $branchAdmin : $moderator);
                $attrs = ['channel_id' => $dept->id, 'language' => $lang];
                if (str_contains($name, 'Emergency')) {
                    $attrs += ['post_type' => 'emergency', 'priority' => 'high', 'badge_key' => 'important'];
                    if (str_contains($title, 'exits')) {
                        $attrs['pinned_until'] = now()->addYears(10);
                    }
                } elseif (str_contains($name, 'Offers') || str_contains(strtolower($title), 'offer') || str_contains($title, 'عروض')) {
                    $attrs += ['post_type' => 'offer', 'badge_key' => 'new'];
                }
                $this->post($attrs, $org, $author, strtr($title, $vars), strtr($body, $vars), mt_rand(0, 30), $sector, $dept->type === 'private', $team);
            }
        }

        // One poll per branch.
        [$pollDept, $question, $options] = C::POLLS[$sector][mt_rand(0, count(C::POLLS[$sector]) - 1)];
        $poll = $this->post(['channel_id' => $depts[$pollDept]->id, 'post_format' => 'poll',
            'options' => ['poll' => ['multiple' => false, 'ends_at' => null, 'show_results' => 'always'], 'reactions' => ['like']]],
            $org, $branchAdmin, $question, $question . ' Vote below — the most popular choice wins!', mt_rand(2, 10), $sector, false, $team);
        foreach ($options as $i => $label) {
            $poll->pollOptions()->create(['label' => $label, 'sort_order' => $i]);
        }
    }

    private function channel(Organization $org, string $name, string $type, User $owner, ?Channel $parent = null): Channel
    {
        $channel = (new Channel())->forceFill([
            'organization_id' => $org->id, 'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(5)),
            'type' => $type, 'status' => 'active', 'admin_user_id' => $owner->id,
            'created_at' => now()->subMonths(mt_rand(3, 18)),
        ]);
        $channel->save();
        $parent && $channel->parentChannels()->attach($parent->id);

        return $channel;
    }

    private function join(Channel $channel, User $user, string $role, array $permissions = [], bool $muted = false): void
    {
        DB::table('channel_user')->updateOrInsert(['channel_id' => $channel->id, 'user_id' => $user->id],
            ['role' => $role, 'status' => 'approved', 'joined_at' => now()->subDays(mt_rand(5, 200)), 'muted_at' => $muted ? now() : null]
            + array_merge(Channel::ROLE_PERMISSIONS[$role], $permissions));
    }

    private function post(array $attrs, Organization $org, User $author, string $title, string $body, int $daysAgo, string $sector, bool $private, array $team): Post
    {
        // Half of the public posts offer the full reaction set; the rest a simple like.
        $reactions = (! $private && mt_rand(0, 1)) ? ['like', 'love', 'haha', 'wow', 'celebrate'] : ['like'];
        $options = $attrs['options'] ?? [];
        $options['reactions'] ??= $reactions;
        unset($attrs['options']);

        $published = now()->subDays($daysAgo)->setTime(mt_rand(7, 21), mt_rand(0, 59));
        if ($published->isFuture()) {
            $published = now()->subMinutes(mt_rand(20, 300));
        }

        $post = (new Post())->forceFill($attrs + [
            'channel_id' => null, 'organization_id' => $org->id, 'author_id' => $author->id,
            'title' => $title, 'summary' => Str::limit($body, 120), 'body' => $body,
            'post_type' => 'announcement', 'post_format' => 'standard',
            'card_style' => $private ? 'classic' : $this->cardStyles[mt_rand(0, count($this->cardStyles) - 1)],
            'priority' => 'medium', 'language' => 'en', 'audience' => 'all', 'status' => 'published',
            'options' => $options, 'published_at' => $published, 'created_at' => $published, 'updated_at' => $published,
        ]);
        $post->save();
        $this->postMeta[] = [$post, $sector, $private, $team];

        return $post;
    }

    // ------------------------------------------------------------------ interactions

    private function interactions(): void
    {
        $reactions = $comments = $receipts = $votes = [];
        $now = now();

        foreach ($this->postMeta as [$post, $sector, $private, $team]) {
            $post->refresh();
            $audience = $this->audienceOf($post, $team);
            if (! $audience) {
                continue;
            }
            shuffle($audience);
            $published = Carbon::parse($post->published_at);
            $after = function (int $maxHours) use ($published, $now) {
                $at = $published->copy()->addMinutes(mt_rand(5, $maxHours * 60));

                return $at->greaterThan($now) ? $now->copy()->subMinutes(mt_rand(1, 30)) : $at;
            };

            // Views
            $viewers = array_slice($audience, 0, max(1, (int) round(count($audience) * mt_rand(55, 95) / 100)));
            foreach ($viewers as $uid) {
                $seen = $after(48);
                $receipts[] = ['post_id' => $post->id, 'user_id' => $uid, 'delivered_at' => $published, 'first_viewed_at' => $seen, 'read_at' => mt_rand(0, 1) ? $seen : null, 'created_at' => $seen, 'updated_at' => $seen];
            }

            // Reactions (one per person, weighted toward like/love)
            $allowed = $post->enabledReactions();
            $weights = ['like' => 50, 'love' => 25, 'celebrate' => 10, 'wow' => 8, 'haha' => 5, 'sad' => 2];
            foreach (array_slice($viewers, 0, (int) round(count($viewers) * mt_rand(35, 80) / 100)) as $uid) {
                $reactions[] = ['post_id' => $post->id, 'user_id' => $uid, 'type' => $this->weighted(array_intersect_key($weights, array_flip($allowed))), 'created_at' => $after(72), 'updated_at' => $now];
            }

            // Poll votes
            if ($post->post_format === 'poll') {
                $optionIds = $post->pollOptions()->pluck('id')->all();
                $bias = array_map(fn () => mt_rand(1, 10), $optionIds);
                foreach (array_slice($viewers, 0, (int) round(count($viewers) * mt_rand(40, 80) / 100)) as $uid) {
                    $at = $after(96);
                    $votes[] = ['post_id' => $post->id, 'option_id' => $this->weighted(array_combine($optionIds, $bias)), 'user_id' => $uid, 'created_at' => $at, 'updated_at' => $at];
                }
            }

            // Comments, then sometimes a team reply
            $library = $private ? C::STAFF_COMMENTS : C::COMMENTS[$sector];
            // Most posts get a handful of comments, some none, a few become lively threads.
            $commentCount = $private ? mt_rand(0, 2) : (int) $this->weighted([0 => 22, 1 => 30, 2 => 22, 3 => 14, 4 => 7, 6 => 5]);
            $commenters = array_slice(array_reverse($viewers), 0, min($commentCount, (int) ceil(count($viewers) / 3)));
            $lastAt = $published;
            foreach ($commenters as $uid) {
                $lastAt = $after(72);
                $comments[] = ['post_id' => $post->id, 'user_id' => $uid, 'body' => $library[mt_rand(0, count($library) - 1)],
                    'status' => mt_rand(1, 100) <= 8 ? 'pending' : 'approved', 'created_at' => $lastAt, 'updated_at' => $lastAt];
            }
            if (! $private && $commenters && $team && mt_rand(1, 100) <= 45) {
                $replyAt = $lastAt->copy()->addMinutes(mt_rand(10, 240));
                $replyAt = $replyAt->greaterThan($now) ? $now->copy()->subMinute() : $replyAt;
                $comments[] = ['post_id' => $post->id, 'user_id' => $team[mt_rand(0, min(1, count($team) - 1))], 'body' => C::REPLIES[mt_rand(0, count(C::REPLIES) - 1)],
                    'status' => 'approved', 'created_at' => $replyAt, 'updated_at' => $replyAt];
            }
        }

        foreach (['post_receipts' => $receipts, 'reactions' => $reactions, 'post_poll_votes' => $votes, 'comments' => $comments] as $table => $rows) {
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table($table)->insertOrIgnore($chunk);
            }
        }

        $this->command?->info(sprintf('Interactions: %d views, %d reactions, %d comments, %d poll votes on %d posts.',
            count($receipts), count($reactions), count($comments), count($votes), count($this->postMeta)));
    }

    /** People who can see a post: subscribers of its receiving channels, the tenant's followers for page posts, plus the team. */
    private function audienceOf(Post $post, array $team): array
    {
        $channelIds = $post->receivingChannelIds();
        $ids = $channelIds ? DB::table('channel_user')->whereIn('channel_id', $channelIds)->where('status', 'approved')->distinct()->pluck('user_id')->all() : [];
        if ($post->isTenantPost() && $post->show_on_page) {
            $ids = array_merge($ids, DB::table('organization_followers')->where('organization_id', $post->organization_id)->pluck('user_id')->all());
        }

        return array_values(array_diff(array_unique(array_merge($ids, $team)), [$post->author_id]));
    }

    // ------------------------------------------------------------------ helpers

    private function pick(array $items, int $n): array
    {
        $keys = array_keys($items);
        for ($i = count($keys) - 1; $i > 0; $i--) {   // seeded Fisher–Yates
            $j = mt_rand(0, $i);
            [$keys[$i], $keys[$j]] = [$keys[$j], $keys[$i]];
        }

        return array_map(fn ($k) => $items[$k], array_slice($keys, 0, max(0, $n)));
    }

    private function weighted(array $weights): int|string
    {
        $roll = mt_rand(1, max(1, array_sum($weights)));
        foreach ($weights as $key => $weight) {
            if (($roll -= $weight) <= 0) {
                return $key;
            }
        }

        return array_key_first($weights);
    }

    private function preferences(): void
    {
        User::doesntHave('preferences')->get()->each(fn (User $user) => UserPreference::create([
            'user_id' => $user->id, 'color_scheme' => 'navy', 'font_size' => 'medium', 'language' => 'en', 'compact_mode' => false,
            'email_notifications' => true, 'push_notifications' => true, 'sms_notifications' => false,
            'notify_new_guest' => true, 'notify_channel_updates' => true, 'notify_system_alerts' => false, 'notify_weekly_report' => true,
        ]));
    }

    private function writeAccounts(): void
    {
        $csv = "email,role,scope,password\n" . collect($this->accounts)
            ->map(fn ($a) => implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', $v) . '"', [...$a, self::PASSWORD])))->implode("\n") . "\n";
        file_put_contents(storage_path('app/demo-accounts.csv'), $csv);

        $this->command?->info('All accounts use the password ' . self::PASSWORD . ' — full list: storage/app/demo-accounts.csv (' . count($this->accounts) . ' accounts)');
        $this->command?->table(['Email', 'Role', 'Scope'], array_values(array_filter($this->accounts,
            fn ($a) => in_array($a[1], ['Super admin', 'Tenant admin', 'Country admin'], true) || str_starts_with($a[0], 'hurghada.') || str_starts_with($a[0], 'maadi.'))));
    }
}
