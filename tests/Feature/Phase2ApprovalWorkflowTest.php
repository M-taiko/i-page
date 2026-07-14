<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Post;
use App\Models\User;
use App\Services\PostApprovalService;
use App\Services\AudienceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class Phase2ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    public function post_workflow_transitions(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-draft', 'is_active' => true]);
        $author = User::create(['ipage_id' => 'U001', 'first_name' => 'A', 'last_name' => 'B', 'email' => 'a@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        $post = Post::create(['organization_id' => $org->id, 'author_id' => $author->id, 'body' => 'Test', 'status' => 'draft']);

        $this->assertEquals('draft', $post->status);
        $this->assertNull($post->published_at);
    }

    #[Test]
    public function admin_can_approve_posts(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-appr', 'is_active' => true]);
        $author = User::create(['ipage_id' => 'U002', 'first_name' => 'C', 'last_name' => 'D', 'email' => 'c@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);
        $admin = User::create(['ipage_id' => 'U003', 'first_name' => 'E', 'last_name' => 'F', 'email' => 'e@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $admin->id, 'role' => 'organization_manager', 'status' => 'active']);

        $post = Post::create(['organization_id' => $org->id, 'author_id' => $author->id, 'body' => 'Test', 'status' => 'pending_approval']);

        $service = new PostApprovalService();
        $approved = $service->approve($post, $admin);

        $this->assertEquals('approved', $approved->status);
        $this->assertEquals($admin->id, $approved->approved_by);
        $this->assertNotNull($approved->approved_at);
    }

    #[Test]
    public function admin_can_reject_posts(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-rej', 'is_active' => true]);
        $author = User::create(['ipage_id' => 'U004', 'first_name' => 'G', 'last_name' => 'H', 'email' => 'g@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);
        $admin = User::create(['ipage_id' => 'U005', 'first_name' => 'I', 'last_name' => 'J', 'email' => 'i@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $admin->id, 'role' => 'organization_manager', 'status' => 'active']);

        $post = Post::create(['organization_id' => $org->id, 'author_id' => $author->id, 'body' => 'Test', 'status' => 'pending_approval']);

        $service = new PostApprovalService();
        $rejected = $service->reject($post, $admin, 'Inappropriate');

        $this->assertEquals('rejected', $rejected->status);
        $this->assertEquals($admin->id, $rejected->approved_by);
    }

    #[Test]
    public function admin_can_schedule_posts(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-sched', 'is_active' => true]);
        $author = User::create(['ipage_id' => 'U006', 'first_name' => 'K', 'last_name' => 'L', 'email' => 'k@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);
        $admin = User::create(['ipage_id' => 'U007', 'first_name' => 'M', 'last_name' => 'N', 'email' => 'm@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $admin->id, 'role' => 'organization_manager', 'status' => 'active']);

        $post = Post::create(['organization_id' => $org->id, 'author_id' => $author->id, 'body' => 'Test', 'status' => 'approved']);

        $scheduledFor = now()->addDays(7);
        $service = new PostApprovalService();
        $scheduled = $service->schedule($post, $scheduledFor, $admin);

        $this->assertEquals('scheduled', $scheduled->status);
        $this->assertNotNull($scheduled->scheduled_for);
        $this->assertTrue($scheduled->scheduled_for->diffInSeconds($scheduledFor) < 2);
    }

    #[Test]
    public function staff_cannot_approve_posts(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-perm', 'is_active' => true]);
        $staff = User::create(['ipage_id' => 'U008', 'first_name' => 'O', 'last_name' => 'P', 'email' => 'o@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $staff->id, 'role' => 'staff', 'status' => 'active']);

        $post = Post::create(['organization_id' => $org->id, 'author_id' => $staff->id, 'body' => 'Test', 'status' => 'pending_approval']);

        $this->actingAs($staff);
        $this->assertFalse($staff->can('approvePost', $post));
    }

    #[Test]
    public function audience_filters_posts(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-aud', 'is_active' => true]);
        $author = User::create(['ipage_id' => 'U009', 'first_name' => 'Q', 'last_name' => 'R', 'email' => 'q@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);
        $user1 = User::create(['ipage_id' => 'U010', 'first_name' => 'S', 'last_name' => 'T', 'email' => 's@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $user1->id, 'role' => 'staff', 'status' => 'active']);

        $post = Post::create(['organization_id' => $org->id, 'author_id' => $author->id, 'body' => 'Test', 'status' => 'published', 'published_at' => now()]);

        $service = new AudienceService();
        $this->assertTrue($service->canUserSeePost($user1, $post));
    }

    #[Test]
    public function outsiders_cannot_see_posts(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-out', 'is_active' => true]);
        $author = User::create(['ipage_id' => 'U011', 'first_name' => 'U', 'last_name' => 'V', 'email' => 'u@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);
        $outsider = User::create(['ipage_id' => 'U012', 'first_name' => 'W', 'last_name' => 'X', 'email' => 'w@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        $post = Post::create(['organization_id' => $org->id, 'author_id' => $author->id, 'body' => 'Test', 'status' => 'published', 'published_at' => now()]);

        $service = new AudienceService();
        $this->assertFalse($service->canUserSeePost($outsider, $post));
    }
}
