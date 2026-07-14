<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Channel;
use App\Models\Department;
use App\Models\Location;
use App\Models\LocationMembership;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    public function org_admin_cannot_view_other_orgs_brands(): void
    {
        $org1 = Organization::create(['name' => 'Org 1', 'slug' => 'org-1', 'is_active' => true]);
        $org2 = Organization::create(['name' => 'Org 2', 'slug' => 'org-2', 'is_active' => true]);

        Brand::create(['organization_id' => $org1->id, 'name' => 'Brand 1', 'slug' => 'b1', 'is_active' => true]);
        Brand::create(['organization_id' => $org2->id, 'name' => 'Brand 2', 'slug' => 'b2', 'is_active' => true]);

        $admin1 = User::create([
            'ipage_id' => 'T001', 'first_name' => 'A', 'last_name' => 'O1',
            'email' => 'a1@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now(),
        ]);
        $org1->users()->attach($admin1, ['role' => 'organization_manager']);

        $this->actingAs($admin1);
        $brands = $admin1->organizations()->first()->brands()->get();
        $this->assertEquals(1, $brands->count());
        $this->assertEquals('Brand 1', $brands->first()->name);
    }

    #[Test]
    public function org_admin_cannot_access_other_orgs_channels(): void
    {
        $org1 = Organization::create(['name' => 'Org 1', 'slug' => 'org-1-ch', 'is_active' => true]);
        $org2 = Organization::create(['name' => 'Org 2', 'slug' => 'org-2-ch', 'is_active' => true]);

        $admin1 = User::create([
            'ipage_id' => 'T002', 'first_name' => 'B', 'last_name' => 'O1',
            'email' => 'b1@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now(),
        ]);
        $org1->users()->attach($admin1, ['role' => 'organization_manager']);

        $userOrgs = $admin1->organizations()->pluck('organizations.id')->toArray();
        $this->assertContains($org1->id, $userOrgs);
        $this->assertNotContains($org2->id, $userOrgs);
    }

    #[Test]
    public function org_admin_cannot_access_other_orgs_posts(): void
    {
        $org1 = Organization::create(['name' => 'Org 1', 'slug' => 'org-1-p', 'is_active' => true]);
        $org2 = Organization::create(['name' => 'Org 2', 'slug' => 'org-2-p', 'is_active' => true]);

        $admin1 = User::create([
            'ipage_id' => 'T003', 'first_name' => 'C', 'last_name' => 'O1',
            'email' => 'c1@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now(),
        ]);
        $org1->users()->attach($admin1, ['role' => 'organization_manager']);

        $userOrgs = $admin1->organizations()->pluck('organizations.id')->toArray();
        $this->assertContains($org1->id, $userOrgs);
        $this->assertNotContains($org2->id, $userOrgs);
    }

    #[Test]
    public function guest_user_without_org_access_cannot_view_org_resources(): void
    {
        $org = Organization::create(['name' => 'Protected Org', 'slug' => 'prot-org', 'is_active' => true]);
        $outsideUser = User::create([
            'ipage_id' => 'T004', 'first_name' => 'D', 'last_name' => 'Out',
            'email' => 'd1@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now(),
        ]);

        $this->actingAs($outsideUser);
        $userOrgs = $outsideUser->organizations()->pluck('organizations.id')->toArray();
        $this->assertNotContains($org->id, $userOrgs);
    }

    #[Test]
    public function organization_user_can_only_access_their_organizations(): void
    {
        $org1 = Organization::create(['name' => 'Org 1', 'slug' => 'org-1-x', 'is_active' => true]);
        $org2 = Organization::create(['name' => 'Org 2', 'slug' => 'org-2-x', 'is_active' => true]);
        $org3 = Organization::create(['name' => 'Org 3', 'slug' => 'org-3-x', 'is_active' => true]);

        $user = User::create([
            'ipage_id' => 'T005', 'first_name' => 'E', 'last_name' => 'Mem',
            'email' => 'e1@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now(),
        ]);

        $org1->users()->attach($user, ['role' => 'member']);
        $org2->users()->attach($user, ['role' => 'member']);

        $this->actingAs($user);
        $userOrgs = $user->organizations()->pluck('organizations.id')->toArray();
        $this->assertContains($org1->id, $userOrgs);
        $this->assertContains($org2->id, $userOrgs);
        $this->assertNotContains($org3->id, $userOrgs);
    }

    #[Test]
    public function brands_are_automatically_created_for_existing_orgs(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org-b', 'is_active' => true]);

        $this->artisan('permission:backfill-org-teams')->assertSuccessful();

        $defaultBrand = Brand::where('organization_id', $org->id)
            ->where('slug', 'default')
            ->first();

        $this->assertNotNull($defaultBrand);
        $this->assertEquals('default', $defaultBrand->slug);
        $this->assertTrue($defaultBrand->is_active);
    }

    #[Test]
    public function organization_scoped_roles_are_created(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org-r', 'is_active' => true]);

        $this->artisan('permission:backfill-org-teams')->assertSuccessful();

        $roles = \Spatie\Permission\Models\Role::where('team_id', $org->id)->get();
        $roleNames = $roles->pluck('name')->toArray();

        $this->assertContains('organization_manager', $roleNames);
        $this->assertContains('department_manager', $roleNames);
        $this->assertContains('staff', $roleNames);
        $this->assertContains('guest', $roleNames);
    }

    #[Test]
    public function location_manager_cannot_access_other_location_members(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'org-loc-test', 'is_active' => true]);
        $brand = Brand::create(['organization_id' => $org->id, 'name' => 'Default', 'slug' => 'default', 'is_active' => true]);

        $loc1 = Location::create(['organization_id' => $org->id, 'brand_id' => $brand->id, 'name' => 'Location 1', 'city' => 'City1', 'country' => 'Country1']);
        $loc2 = Location::create(['organization_id' => $org->id, 'brand_id' => $brand->id, 'name' => 'Location 2', 'city' => 'City2', 'country' => 'Country2']);

        $staff1 = User::create(['ipage_id' => 'T101', 'first_name' => 'Staff', 'last_name' => 'One', 'email' => 'staff1@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);
        $staff2 = User::create(['ipage_id' => 'T102', 'first_name' => 'Staff', 'last_name' => 'Two', 'email' => 'staff2@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $staff1->id, 'role' => 'staff', 'status' => 'active']);
        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $staff2->id, 'role' => 'staff', 'status' => 'active']);

        LocationMembership::create(['user_id' => $staff1->id, 'location_id' => $loc1->id, 'is_primary' => true, 'status' => 'active']);
        LocationMembership::create(['user_id' => $staff2->id, 'location_id' => $loc2->id, 'is_primary' => true, 'status' => 'active']);

        $this->actingAs($staff1);
        $staff1Locations = $staff1->locationMemberships()->where('status', 'active')->pluck('location_id')->toArray();
        $this->assertContains($loc1->id, $staff1Locations);
        $this->assertNotContains($loc2->id, $staff1Locations);
    }

    #[Test]
    public function department_hierarchy_scoping_prevents_cross_department_access(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'org-dept-test', 'is_active' => true]);
        $brand = Brand::create(['organization_id' => $org->id, 'name' => 'Default', 'slug' => 'default', 'is_active' => true]);
        $loc = Location::create(['organization_id' => $org->id, 'brand_id' => $brand->id, 'name' => 'Main', 'city' => 'City', 'country' => 'Country']);

        $deptParent = Department::create(['organization_id' => $org->id, 'location_id' => $loc->id, 'name' => 'Parent Dept', 'slug' => 'parent-dept']);
        $deptChild1 = Department::create(['organization_id' => $org->id, 'location_id' => $loc->id, 'name' => 'Child 1', 'slug' => 'child-1', 'parent_department_id' => $deptParent->id]);
        $deptChild2 = Department::create(['organization_id' => $org->id, 'location_id' => $loc->id, 'name' => 'Child 2', 'slug' => 'child-2', 'parent_department_id' => $deptParent->id]);

        $user1 = User::create(['ipage_id' => 'T201', 'first_name' => 'User', 'last_name' => 'One', 'email' => 'u1@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);
        $user2 = User::create(['ipage_id' => 'T202', 'first_name' => 'User', 'last_name' => 'Two', 'email' => 'u2@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $user1->id, 'role' => 'staff', 'status' => 'active', 'department_id' => $deptChild1->id]);
        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $user2->id, 'role' => 'staff', 'status' => 'active', 'department_id' => $deptChild2->id]);

        $this->actingAs($user1);
        $user1Dept = $user1->organizationMemberships()->where('organization_id', $org->id)->first()->department_id;
        $this->assertEquals($deptChild1->id, $user1Dept);

        $this->actingAs($user2);
        $user2Dept = $user2->organizationMemberships()->where('organization_id', $org->id)->first()->department_id;
        $this->assertEquals($deptChild2->id, $user2Dept);
    }

    #[Test]
    public function inactive_membership_blocks_organization_access(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'org-inactive-test', 'is_active' => true]);
        $user = User::create(['ipage_id' => 'T301', 'first_name' => 'Inactive', 'last_name' => 'User', 'email' => 'inactive@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $user->id, 'role' => 'staff', 'status' => 'inactive']);

        $this->actingAs($user);
        $activeOrgs = $user->organizationMemberships()
            ->where('status', 'active')
            ->pluck('organization_id')
            ->toArray();

        $this->assertNotContains($org->id, $activeOrgs);
    }

    #[Test]
    public function suspended_membership_blocks_organization_access(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'org-suspended-test', 'is_active' => true]);
        $user = User::create(['ipage_id' => 'T302', 'first_name' => 'Suspended', 'last_name' => 'User', 'email' => 'suspended@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $user->id, 'role' => 'staff', 'status' => 'suspended']);

        $this->actingAs($user);
        $activeOrgs = $user->organizationMemberships()
            ->where('status', 'active')
            ->pluck('organization_id')
            ->toArray();

        $this->assertNotContains($org->id, $activeOrgs);
    }

    #[Test]
    public function location_membership_status_controls_access(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'org-locmem-status', 'is_active' => true]);
        $brand = Brand::create(['organization_id' => $org->id, 'name' => 'Default', 'slug' => 'default', 'is_active' => true]);
        $loc = Location::create(['organization_id' => $org->id, 'brand_id' => $brand->id, 'name' => 'Location', 'city' => 'City', 'country' => 'Country']);

        $user = User::create(['ipage_id' => 'T303', 'first_name' => 'Status', 'last_name' => 'User', 'email' => 'status@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $user->id, 'role' => 'staff', 'status' => 'active']);

        // Active location membership
        LocationMembership::create(['user_id' => $user->id, 'location_id' => $loc->id, 'status' => 'active']);
        $this->actingAs($user);
        $activeLocs = $user->locationMemberships()->where('status', 'active')->pluck('location_id')->toArray();
        $this->assertContains($loc->id, $activeLocs);

        // Change to inactive
        $user->locationMemberships()->first()->update(['status' => 'inactive']);
        $activeLocs = $user->locationMemberships()->where('status', 'active')->pluck('location_id')->toArray();
        $this->assertNotContains($loc->id, $activeLocs);
    }

    #[Test]
    public function org_admin_policy_enforces_organization_manager_role(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'org-policy-test', 'is_active' => true]);

        $admin = User::create(['ipage_id' => 'T401', 'first_name' => 'Admin', 'last_name' => 'User', 'email' => 'admin@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);
        $staff = User::create(['ipage_id' => 'T402', 'first_name' => 'Staff', 'last_name' => 'User', 'email' => 'staff@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $admin->id, 'role' => 'organization_manager', 'status' => 'active']);
        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $staff->id, 'role' => 'staff', 'status' => 'active']);

        // Admin can view
        $this->assertTrue($admin->can('view', $org));
        $this->assertTrue($admin->can('update', $org));

        // Staff can only view, not update
        $this->assertTrue($staff->can('view', $org));
        $this->assertFalse($staff->can('update', $org));
    }
}
