<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Technician;
use App\Services\Workflow\AutoAssignService;
use Illuminate\Http\Request;

/**
 * @tags Admin
 * 
 * Ticket triage and assignment management
 */
class TriageController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::whereIn('status', ['pending_triage', 'triage'])
            ->with(['customer', 'device'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        $tickets = $tickets->map(function ($ticket) {
            $countdown = $ticket->triage_deadline_at 
                ? max(0, $ticket->triage_deadline_at->diffInSeconds(now()))
                : 0;

            return [
                'id' => $ticket->id,
                'customer' => $ticket->customer->name,
                'device' => $ticket->device->brand . ' ' . $ticket->device->device_type,
                'issue' => $ticket->issue_description,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'countdown' => $countdown,
                'created_at' => $ticket->created_at,
            ];
        });

        return response()->json([
            'tickets' => $tickets,
        ]);
    }

    public function assign(Request $request, int $ticketId)
    {
        $request->validate([
            'technician_id' => 'required|exists:technicians,id',
        ]);

        $ticket = Ticket::findOrFail($ticketId);
        $technician = Technician::findOrFail($request->technician_id);

        if (!$technician->isAvailable()) {
            return response()->json([
                'message' => 'Technician is not available',
            ], 422);
        }

        $autoAssignService = app(AutoAssignService::class);
        $assigned = $autoAssignService->assign($ticket);

        if (!$assigned) {
            return response()->json([
                'message' => 'Failed to assign technician',
            ], 500);
        }

        return response()->json([
            'message' => 'Ticket assigned successfully',
        ]);
    }

    public function reject(Request $request, int $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        
        $ticket->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'Ticket rejected',
        ]);
    }
}
