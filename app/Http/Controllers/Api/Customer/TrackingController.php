<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

/**
 * @tags Customer
 * 
 * Ticket tracking and device history
 */
class TrackingController extends Controller
{
    public function track(Request $request, int $ticketId)
    {
        $ticket = Ticket::where('customer_id', $request->user()->id)
            ->with(['device', 'jobs.technician.user', 'jobs.quote'])
            ->findOrFail($ticketId);

        $timeline = $this->buildTimeline($ticket);

        return response()->json([
            'ticket' => $ticket,
            'timeline' => $timeline,
            'current_status' => $this->getCurrentStatus($ticket),
        ]);
    }

    public function devices(Request $request)
    {
        $devices = $request->user()->devices()
            ->with(['tickets' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->get();

        return response()->json([
            'devices' => $devices,
        ]);
    }

    protected function buildTimeline(Ticket $ticket): array
    {
        $timeline = [
            [
                'status' => 'received',
                'label' => 'Request Received',
                'timestamp' => $ticket->created_at,
                'completed' => true,
            ],
        ];

        if ($ticket->status !== 'pending_triage') {
            $timeline[] = [
                'status' => 'assigned',
                'label' => 'Technician Assigned',
                'timestamp' => $ticket->triage_handled_at,
                'completed' => true,
            ];
        }

        $activeJob = $ticket->activeJob;
        if ($activeJob) {
            if ($activeJob->status === 'en_route') {
                $timeline[] = [
                    'status' => 'on_way',
                    'label' => 'Technician On Way',
                    'timestamp' => $activeJob->updated_at,
                    'completed' => true,
                ];
            }

            if (in_array($activeJob->status, ['arrived', 'diagnosing', 'repairing', 'quality_check'])) {
                $timeline[] = [
                    'status' => 'working',
                    'label' => 'Repair In Progress',
                    'timestamp' => $activeJob->updated_at,
                    'completed' => true,
                ];
            }

            if ($activeJob->status === 'completed') {
                $timeline[] = [
                    'status' => 'done',
                    'label' => 'Repair Completed',
                    'timestamp' => $activeJob->released_at ?? $activeJob->updated_at,
                    'completed' => true,
                ];
            }
        }

        return $timeline;
    }

    protected function getCurrentStatus(Ticket $ticket): string
    {
        $statusMap = [
            'pending_triage' => 'Waiting for assignment',
            'triage' => 'In review',
            'assigned' => 'Technician assigned',
            'accepted' => 'Technician accepted',
            'in_progress' => 'Repair in progress',
            'on_hold' => 'Waiting for parts',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        return $statusMap[$ticket->status] ?? $ticket->status;
    }
}
