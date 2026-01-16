<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Technician;
use App\Services\Workflow\AutoAssignService;
use Illuminate\Http\Request;

class TriageController extends Controller
{
    public function index()
    {
        $tickets = Ticket::whereIn('status', ['pending_triage', 'triage'])
            ->with(['customer', 'device'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($ticket) {
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
                    'countdown_formatted' => gmdate('i:s', $countdown),
                    'created_at' => $ticket->created_at->format('Y-m-d H:i'),
                ];
            });

        $technicians = Technician::with('user')
            ->where('status', 'on_duty')
            ->get()
            ->map(function ($tech) {
                return [
                    'id' => $tech->id,
                    'name' => $tech->user->name,
                    'active_jobs' => $tech->active_jobs_count,
                    'available' => $tech->isAvailable(),
                ];
            });

        return view('admin.triage.index', compact('tickets', 'technicians'));
    }

    public function assign(Request $request, int $ticketId)
    {
        $request->validate([
            'technician_id' => 'required|exists:technicians,id',
        ]);

        $ticket = Ticket::findOrFail($ticketId);
        $technician = Technician::findOrFail($request->technician_id);

        if (!$technician->isAvailable()) {
            return back()->withErrors(['technician' => 'Technician is not available']);
        }

        $autoAssignService = app(AutoAssignService::class);
        $assigned = $autoAssignService->assign($ticket);

        if (!$assigned) {
            return back()->withErrors(['error' => 'Failed to assign technician']);
        }

        return redirect()->route('admin.triage.index')
            ->with('success', 'Ticket assigned successfully');
    }

    public function reject(int $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->update(['status' => 'cancelled']);

        return redirect()->route('admin.triage.index')
            ->with('success', 'Ticket rejected');
    }
}
