<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\SlaRule;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class Phase3CrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    public function tickets_can_be_created_with_sla_events(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-ticket', 'is_active' => true]);
        $creator = User::create(['ipage_id' => 'U001', 'first_name' => 'A', 'last_name' => 'B', 'email' => 'a@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $creator->id, 'role' => 'staff', 'status' => 'active']);

        // Create SLA rule
        $slaRule = SlaRule::create([
            'organization_id' => $org->id,
            'name' => 'Standard SLA',
            'first_response_time' => 60,
            'resolution_time' => 480,
        ]);

        $service = new TicketService();
        $ticket = $service->createTicket([
            'organization_id' => $org->id,
            'title' => 'Test Issue',
            'description' => 'Test description',
            'type' => 'complaint',
            'priority' => 'high',
        ], $creator);

        $this->assertNotNull($ticket->ticket_number);
        $this->assertEquals('open', $ticket->status);
        $this->assertEquals($creator->id, $ticket->created_by);
        $this->assertTrue($ticket->slaEvents()->exists());
    }

    #[Test]
    public function tickets_can_be_assigned(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-assign', 'is_active' => true]);
        $creator = User::create(['ipage_id' => 'U002', 'first_name' => 'C', 'last_name' => 'D', 'email' => 'c@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);
        $assignee = User::create(['ipage_id' => 'U003', 'first_name' => 'E', 'last_name' => 'F', 'email' => 'e@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $creator->id, 'role' => 'staff', 'status' => 'active']);
        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $assignee->id, 'role' => 'staff', 'status' => 'active']);

        $ticket = Ticket::create([
            'organization_id' => $org->id,
            'ticket_number' => 'TK-001',
            'title' => 'Test',
            'description' => 'Test',
            'created_by' => $creator->id,
            'opened_at' => now(),
        ]);

        $service = new TicketService();
        $assigned = $service->assignTicket($ticket, $assignee);

        $this->assertEquals($assignee->id, $assigned->assigned_to);
        $this->assertTrue($assigned->messages()->exists());
    }

    #[Test]
    public function tickets_can_be_resolved(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-resolve', 'is_active' => true]);
        $creator = User::create(['ipage_id' => 'U004', 'first_name' => 'G', 'last_name' => 'H', 'email' => 'g@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $creator->id, 'role' => 'staff', 'status' => 'active']);

        $ticket = Ticket::create([
            'organization_id' => $org->id,
            'ticket_number' => 'TK-002',
            'title' => 'Test',
            'description' => 'Test',
            'created_by' => $creator->id,
            'opened_at' => now(),
            'status' => 'in_progress',
        ]);

        $service = new TicketService();
        $resolved = $service->resolveTicket($ticket, $creator, 'Issue has been resolved.');

        $this->assertEquals('resolved', $resolved->status);
        $this->assertNotNull($resolved->resolved_at);
        $this->assertNotNull($resolved->resolution_time_minutes);
    }

    #[Test]
    public function tickets_can_be_reopened(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-reopen', 'is_active' => true]);
        $creator = User::create(['ipage_id' => 'U005', 'first_name' => 'I', 'last_name' => 'J', 'email' => 'i@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $creator->id, 'role' => 'staff', 'status' => 'active']);

        // Create SLA rule
        SlaRule::create([
            'organization_id' => $org->id,
            'name' => 'Standard SLA',
            'first_response_time' => 60,
            'resolution_time' => 480,
        ]);

        $ticket = Ticket::create([
            'organization_id' => $org->id,
            'ticket_number' => 'TK-003',
            'title' => 'Test',
            'description' => 'Test',
            'created_by' => $creator->id,
            'opened_at' => now(),
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $service = new TicketService();
        $reopened = $service->reopenTicket($ticket, $creator, 'Issue still persists');

        $this->assertEquals('reopened', $reopened->status);
        $this->assertTrue($reopened->slaEvents()->exists());
    }

    #[Test]
    public function ticket_messages_track_first_response(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-msg', 'is_active' => true]);
        $creator = User::create(['ipage_id' => 'U006', 'first_name' => 'K', 'last_name' => 'L', 'email' => 'k@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);
        $responder = User::create(['ipage_id' => 'U007', 'first_name' => 'M', 'last_name' => 'N', 'email' => 'm@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $creator->id, 'role' => 'staff', 'status' => 'active']);
        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $responder->id, 'role' => 'staff', 'status' => 'active']);

        $ticket = Ticket::create([
            'organization_id' => $org->id,
            'ticket_number' => 'TK-004',
            'title' => 'Test',
            'description' => 'Test',
            'created_by' => $creator->id,
            'opened_at' => now(),
        ]);

        $service = new TicketService();
        $service->addMessage($ticket, $responder, 'We are looking into this.');

        $ticket->refresh();
        $this->assertNotNull($ticket->first_response_at);
    }

    #[Test]
    public function ticket_policy_enforces_permissions(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-policy', 'is_active' => true]);
        $admin = User::create(['ipage_id' => 'U008', 'first_name' => 'O', 'last_name' => 'P', 'email' => 'o@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);
        $member = User::create(['ipage_id' => 'U009', 'first_name' => 'Q', 'last_name' => 'R', 'email' => 'q@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);
        $outsider = User::create(['ipage_id' => 'U010', 'first_name' => 'S', 'last_name' => 'T', 'email' => 's@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $admin->id, 'role' => 'organization_manager', 'status' => 'active']);
        OrganizationMembership::create(['organization_id' => $org->id, 'user_id' => $member->id, 'role' => 'staff', 'status' => 'active']);

        $ticket = Ticket::create([
            'organization_id' => $org->id,
            'ticket_number' => 'TK-005',
            'title' => 'Test',
            'description' => 'Test',
            'created_by' => $member->id,
            'opened_at' => now(),
        ]);

        // Admin can update
        $this->assertTrue($admin->can('update', $ticket));

        // Member (creator) cannot update
        $this->assertFalse($member->can('update', $ticket));

        // Outsider cannot view
        $this->assertFalse($outsider->can('view', $ticket));
    }

    #[Test]
    public function sla_rules_match_tickets_intelligently(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-sla', 'is_active' => true]);
        $creator = User::create(['ipage_id' => 'U011', 'first_name' => 'U', 'last_name' => 'V', 'email' => 'u@test.com', 'password' => bcrypt('x'), 'email_verified_at' => now()]);

        $standardRule = SlaRule::create([
            'organization_id' => $org->id,
            'name' => 'Standard',
            'priority' => null,
            'first_response_time' => 60,
            'resolution_time' => 480,
            'is_active' => true,
        ]);

        $urgentRule = SlaRule::create([
            'organization_id' => $org->id,
            'name' => 'Urgent',
            'priority' => 'urgent',
            'first_response_time' => 15,
            'resolution_time' => 120,
            'is_active' => true,
        ]);

        $standardTicket = Ticket::create([
            'organization_id' => $org->id,
            'ticket_number' => 'TK-006',
            'title' => 'Standard',
            'description' => 'Test',
            'created_by' => $creator->id,
            'opened_at' => now(),
            'priority' => 'medium',
        ]);

        $urgentTicket = Ticket::create([
            'organization_id' => $org->id,
            'ticket_number' => 'TK-007',
            'title' => 'Urgent',
            'description' => 'Test',
            'created_by' => $creator->id,
            'opened_at' => now(),
            'priority' => 'urgent',
        ]);

        $this->assertTrue($standardRule->appliesToTicket($standardTicket));
        $this->assertFalse($urgentRule->appliesToTicket($standardTicket));
        $this->assertTrue($urgentRule->appliesToTicket($urgentTicket));
    }
}
