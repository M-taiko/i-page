<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(private TicketService $ticketService)
    {
    }

    public function index(Request $request)
    {
        // Super admin sees all tickets, regular users see only their own organization's tickets
        if (auth()->user()->hasRole('super_admin')) {
            $query = Ticket::query();
        } else {
            $organization = auth()->user()->currentOrganization;
            abort_unless($organization, 403, 'No organization context');
            $query = $organization->tickets();
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $tickets = $query->latest('opened_at')->paginate(15);

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        if (auth()->user()->hasRole('super_admin')) {
            $organization = auth()->user()->organizations()->first();
        } else {
            $organization = auth()->user()->currentOrganization;
        }

        if (!$organization) {
            abort(403, 'No organization context');
        }

        $categories = $organization->ticketCategories()->where('is_active', true)->get();

        return view('tickets.form', compact('organization', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'type' => 'required|in:complaint,feedback,suggestion,request,bug,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string|max:20',
            'category_id' => 'nullable|exists:ticket_categories,id',
        ]);

        if (auth()->user()->hasRole('super_admin')) {
            $organization = auth()->user()->organizations()->first();
        } else {
            $organization = auth()->user()->currentOrganization;
        }

        if (!$organization) {
            abort(403, 'No organization context');
        }

        $validated['organization_id'] = $organization->id;
        $validated['created_by'] = auth()->id();
        $validated['customer_email'] ??= auth()->user()->email;

        $ticket = $this->ticketService->createTicket($validated, auth()->user(), $request->input('initial_message'));

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        return view('tickets.edit', compact('ticket'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'priority' => 'required|in:low,medium,high,urgent',
            'category_id' => 'nullable|exists:ticket_categories,id',
        ]);

        $ticket->update($validated);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket deleted successfully.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $status = $request->input('status');
        if (!in_array($status, ['open', 'in_progress', 'waiting', 'resolved', 'closed', 'reopened'])) {
            abort(422);
        }

        $ticket->update(['status' => $status]);

        return back()->with('success', 'Ticket status updated.');
    }

    public function resolve(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $this->ticketService->resolveTicket($ticket, auth()->user(), $request->input('note'));

        return back()->with('success', 'Ticket marked as resolved.');
    }

    public function close(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $this->ticketService->closeTicket($ticket, auth()->user());

        return back()->with('success', 'Ticket closed.');
    }

    public function reopen(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $this->ticketService->reopenTicket($ticket, auth()->user(), $request->input('reason', ''));

        return back()->with('success', 'Ticket reopened.');
    }

    public function addMessage(Request $request, Ticket $ticket)
    {
        $this->authorize('addMessage', $ticket);

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'is_internal' => 'boolean',
        ]);

        $this->ticketService->addMessage(
            $ticket,
            auth()->user(),
            $validated['message'],
            'reply',
            $validated['is_internal'] ?? false
        );

        return back()->with('success', 'Message added.');
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $assignee = \App\Models\User::findOrFail($validated['assigned_to']);
        $this->ticketService->assignTicket($ticket, $assignee);

        return back()->with('success', 'Ticket assigned.');
    }
}
