<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\SlaRule;
use App\Models\SlaEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketService
{
    public function createTicket(array $data, User $creator): Ticket
    {
        return DB::transaction(function () use ($data, $creator) {
            $ticket = Ticket::create([
                'ticket_number' => $this->generateTicketNumber($data['organization_id']),
                'organization_id' => $data['organization_id'],
                'brand_id' => $data['brand_id'] ?? null,
                'location_id' => $data['location_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'created_by' => $creator->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => 'open',
                'type' => $data['type'] ?? 'other',
                'priority' => $data['priority'] ?? 'medium',
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'opened_at' => now(),
            ]);

            // Create SLA events for this ticket
            $this->createSlaEvents($ticket);

            // Add initial message if provided
            if (isset($data['initial_message'])) {
                $this->addMessage($ticket, $creator, $data['initial_message']);
            }

            return $ticket;
        });
    }

    public function assignTicket(Ticket $ticket, User $assignee): Ticket
    {
        return DB::transaction(function () use ($ticket, $assignee) {
            $ticket->update(['assigned_to' => $assignee->id]);

            $this->addSystemMessage($ticket, "Ticket assigned to {$assignee->full_name}");

            return $ticket;
        });
    }

    public function resolveTicket(Ticket $ticket, User $resolver, string $resolution_note): Ticket
    {
        return DB::transaction(function () use ($ticket, $resolver, $resolution_note) {
            $resolution_minutes = now()->diffInMinutes($ticket->opened_at);

            $ticket->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolution_time_minutes' => $resolution_minutes,
            ]);

            $this->addMessage($ticket, $resolver, $resolution_note, 'reply');
            $this->addSystemMessage($ticket, "Ticket resolved");

            // Mark SLA resolution event as complete
            $ticket->slaEvents()
                ->where('event_type', 'resolution')
                ->update(['status' => 'on_track']);

            return $ticket;
        });
    }

    public function closeTicket(Ticket $ticket, User $closer): Ticket
    {
        return DB::transaction(function () use ($ticket, $closer) {
            $ticket->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => $closer->id,
            ]);

            $this->addSystemMessage($ticket, "Ticket closed");

            return $ticket;
        });
    }

    public function reopenTicket(Ticket $ticket, User $reopener, string $reason): Ticket
    {
        return DB::transaction(function () use ($ticket, $reopener, $reason) {
            $ticket->update(['status' => 'reopened']);

            $this->addMessage($ticket, $reopener, $reason, 'reply');
            $this->addSystemMessage($ticket, "Ticket reopened");

            // Recreate SLA events for reopened ticket
            $ticket->slaEvents()->delete();
            $this->createSlaEvents($ticket);

            return $ticket;
        });
    }

    public function addMessage(Ticket $ticket, User $author, string $message, string $type = 'reply', bool $is_internal = false): TicketMessage
    {
        $msg = $ticket->messages()->create([
            'author_id' => $author->id,
            'message' => $message,
            'message_type' => $type,
            'is_internal' => $is_internal,
        ]);

        // Record first response
        if ($ticket->first_response_at === null && !$is_internal) {
            $ticket->update(['first_response_at' => now()]);

            // Mark first response SLA as met
            $ticket->slaEvents()
                ->where('event_type', 'first_response')
                ->update(['status' => 'on_track']);
        }

        return $msg;
    }

    public function addSystemMessage(Ticket $ticket, string $message): TicketMessage
    {
        return $this->addMessage($ticket, $ticket->creator ?? auth()->user(), $message, 'system', true);
    }

    private function createSlaEvents(Ticket $ticket): void
    {
        $applicableRules = SlaRule::where('organization_id', $ticket->organization_id)
            ->where('is_active', true)
            ->get()
            ->filter(fn ($rule) => $rule->appliesToTicket($ticket));

        foreach ($applicableRules as $rule) {
            // Create first response event
            if ($rule->first_response_time) {
                SlaEvent::create([
                    'ticket_id' => $ticket->id,
                    'sla_rule_id' => $rule->id,
                    'event_type' => 'first_response',
                    'status' => 'on_track',
                    'deadline_at' => now()->addMinutes($rule->first_response_time),
                ]);
            }

            // Create resolution event
            if ($rule->resolution_time) {
                SlaEvent::create([
                    'ticket_id' => $ticket->id,
                    'sla_rule_id' => $rule->id,
                    'event_type' => 'resolution',
                    'status' => 'on_track',
                    'deadline_at' => now()->addMinutes($rule->resolution_time),
                ]);
            }
        }
    }

    private function generateTicketNumber(int $organizationId): string
    {
        $prefix = strtoupper(Str::random(2));
        $timestamp = now()->format('ymd');
        $random = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$timestamp}-{$random}";
    }
}
