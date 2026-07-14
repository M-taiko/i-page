<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Post;
use App\Models\PostReceipt;
use App\Models\AudienceSegment;
use App\Models\User;
use App\Services\PostReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class Phase2CommunicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    public function posts_can_have_extended_metadata(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-ext', 'is_active' => true]);
        $brand = Brand::create(['organization_id' => $org->id, 'name' => 'Brand', 'slug' => 'brand', 'is_active' => true]);
        $user = User::create(['ipage_id' => 'U001', 'first_name' => 'A', 'last_name' => 'B', 'email' => 'a@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        $post = Post::create([
            'organization_id' => $org->id,
            'brand_id' => $brand->id,
            'author_id' => $user->id,
            'title' => 'Emergency Evacuation',
            'summary' => 'Building evacuation required',
            'body' => 'Please evacuate immediately',
            'post_type' => 'emergency',
            'priority' => 'critical',
            'requires_acknowledgment' => true,
            'is_emergency' => true,
            'status' => 'published',
        ]);

        $this->assertEquals('Emergency Evacuation', $post->title);
        $this->assertEquals('emergency', $post->post_type);
        $this->assertEquals('critical', $post->priority);
        $this->assertTrue($post->is_emergency);
        $this->assertTrue($post->requires_acknowledgment);
    }

    #[Test]
    public function post_receipts_track_delivery_and_engagement(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-receipt', 'is_active' => true]);
        $user = User::create(['ipage_id' => 'U002', 'first_name' => 'C', 'last_name' => 'D', 'email' => 'c@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);
        $post = Post::create(['organization_id' => $org->id, 'author_id' => $user->id, 'body' => 'Test', 'status' => 'published']);

        $service = new PostReceiptService();
        
        $delivery = $service->recordDelivery($post, $user);
        $this->assertNotNull($delivery->delivered_at);

        $view = $service->recordView($post, $user);
        $this->assertNotNull($view->first_viewed_at);

        $read = $service->recordRead($post, $user);
        $this->assertNotNull($read->read_at);

        $ack = $service->recordAcknowledgment($post, $user);
        $this->assertNotNull($ack->acknowledged_at);
    }

    #[Test]
    public function audience_segments_match_users_by_rules(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-aud', 'is_active' => true]);
        $user = User::create(['ipage_id' => 'U003', 'first_name' => 'E', 'last_name' => 'F', 'email' => 'e@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now(), 'language' => 'en']);
        
        \App\Models\OrganizationMembership::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'role' => 'staff',
            'status' => 'active',
        ]);

        $segment = AudienceSegment::create([
            'organization_id' => $org->id,
            'name' => 'English Speakers',
            'rules' => [['scope' => 'language', 'value' => ['en']]],
            'is_active' => true,
        ]);

        $this->assertTrue($segment->matchesUser($user));
    }

    #[Test]
    public function post_receipts_service_generates_stats(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-stats', 'is_active' => true]);
        $author = User::create(['ipage_id' => 'U004', 'first_name' => 'G', 'last_name' => 'H', 'email' => 'g@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);
        $post = Post::create(['organization_id' => $org->id, 'author_id' => $author->id, 'body' => 'Test', 'status' => 'published', 'requires_acknowledgment' => true]);

        $users = User::factory(10)->create();
        $service = new PostReceiptService();

        foreach ($users as $i => $user) {
            if ($i < 8) $service->recordDelivery($post, $user);
            if ($i < 6) $service->recordView($post, $user);
            if ($i < 4) $service->recordRead($post, $user);
            if ($i < 2) $service->recordAcknowledgment($post, $user);
        }

        $stats = $service->getPostStats($post);

        $this->assertEquals(8, $stats['delivered']);
        $this->assertEquals(6, $stats['viewed']);
        $this->assertEquals(4, $stats['read']);
        $this->assertEquals(2, $stats['acknowledged']);
        $this->assertEquals(75.0, $stats['view_rate']);
        $this->assertEquals(50.0, $stats['read_rate']);
        $this->assertEquals(25.0, $stats['acknowledgment_rate']);
    }
}
