<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with(['assignee', 'convertedToCustomer', 'convertedToTicket']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($leads);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'source' => 'required|in:website,phone_call,walk_in,referral,social_media,advertisement,email,other',
            'priority' => 'required|in:low,normal,high,urgent',
            'estimated_value' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'tags' => 'nullable|array',
        ]);

        $validated['status'] = 'new';

        $lead = Lead::create($validated);

        return response()->json($lead->load(['assignee']), 201);
    }

    public function show($id)
    {
        $lead = Lead::with(['assignee', 'convertedToCustomer', 'convertedToTicket'])->findOrFail($id);
        return response()->json($lead);
    }

    public function update(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'sometimes|string',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'source' => 'sometimes|in:website,phone_call,walk_in,referral,social_media,advertisement,email,other',
            'status' => 'sometimes|in:new,contacted,qualified,quoted,converted,lost,cancelled',
            'priority' => 'sometimes|in:low,normal,high,urgent',
            'estimated_value' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'follow_up_date' => 'nullable|date',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $lead->update($validated);

        return response()->json($lead->load(['assignee', 'convertedToCustomer', 'convertedToTicket']));
    }

    public function convertToCustomer(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'create_ticket' => 'boolean',
        ]);

        // Create customer account
        $customer = User::create([
            'name' => $lead->name,
            'email' => $lead->email ?? $lead->phone . '@temp.com',
            'phone' => $lead->phone,
            'password' => bcrypt(Str::random(12)), // Random password, customer will reset
            'role' => 'customer',
        ]);

        $ticketId = null;
        if ($validated['create_ticket'] ?? false) {
            // Create ticket from lead
            $ticket = Ticket::create([
                'customer_id' => $customer->id,
                'issue_description' => $lead->description ?? 'Converted from lead',
                'status' => 'pending_triage',
                'priority' => $lead->priority === 'urgent' ? 'high' : 'normal',
            ]);
            $ticketId = $ticket->id;
        }

        $lead->update([
            'status' => 'converted',
            'converted_to_customer_id' => $customer->id,
            'converted_to_ticket_id' => $ticketId,
            'converted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Lead converted to customer',
            'customer' => $customer,
            'ticket_id' => $ticketId,
            'lead' => $lead->load(['convertedToCustomer', 'convertedToTicket']),
        ]);
    }
}
