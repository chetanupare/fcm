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
            ->with(['device', 'customer', 'jobs.technician.user', 'jobs.quote', 'activeJob.technician.user'])
            ->findOrFail($ticketId);

        $timeline = $this->buildTimeline($ticket);
        $activeJob = $ticket->activeJob;

        return response()->json([
            'id' => $ticket->id,
            'status' => $ticket->status,
            'technician' => $activeJob && $activeJob->technician ? [
                'id' => $activeJob->technician->id,
                'name' => $activeJob->technician->user->name,
                'phone' => $activeJob->technician->user->phone,
                'location' => $activeJob->technician->latitude && $activeJob->technician->longitude ? [
                    'latitude' => $activeJob->technician->latitude,
                    'longitude' => $activeJob->technician->longitude,
                ] : null,
            ] : null,
            'timeline' => $timeline,
            'ticket' => $ticket, // Keep for backward compatibility
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

    public function tickets(Request $request)
    {
        $tickets = Ticket::where('customer_id', $request->user()->id)
            ->with(['device', 'activeJob.technician.user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($ticket) {
                $activeJob = $ticket->activeJob;
                return [
                    'id' => $ticket->id,
                    'device' => $ticket->device->brand . ' ' . $ticket->device->device_type,
                    'issue' => $ticket->issue_description,
                    'status' => $ticket->status,
                    'technician' => $activeJob && $activeJob->technician ? [
                        'name' => $activeJob->technician->user->name,
                        'phone' => $activeJob->technician->user->phone,
                    ] : null,
                    'created_at' => $ticket->created_at->toIso8601String(),
                    'updated_at' => $ticket->updated_at->toIso8601String(),
                ];
            });

        return response()->json([
            'tickets' => $tickets,
        ]);
    }

    protected function buildTimeline(Ticket $ticket): array
    {
        $timeline = [];

        // Request received
        $timeline[] = [
            'status' => 'pending_triage',
            'note' => 'Service request received',
            'created_at' => $ticket->created_at->toISOString(),
        ];

        // Triage handled
        if ($ticket->triage_handled_at) {
            $timeline[] = [
                'status' => 'triage',
                'note' => 'Ticket under review',
                'created_at' => $ticket->triage_handled_at->toISOString(),
            ];
        }

        // Job statuses
        $activeJob = $ticket->activeJob;
        if ($activeJob) {
            if ($activeJob->status === 'offered') {
                $timeline[] = [
                    'status' => 'assigned',
                    'note' => 'Job offered to technician',
                    'created_at' => $activeJob->created_at->toISOString(),
                ];
            }

            if ($activeJob->status === 'accepted') {
                $timeline[] = [
                    'status' => 'accepted',
                    'note' => 'Technician accepted the job',
                    'created_at' => $activeJob->offer_accepted_at?->toISOString() ?? $activeJob->updated_at->toISOString(),
                ];
            }

            if ($activeJob->status === 'en_route') {
                $timeline[] = [
                    'status' => 'en_route',
                    'note' => 'Technician on the way',
                    'created_at' => $activeJob->updated_at->toISOString(),
                ];
            }

            if ($activeJob->status === 'arrived') {
                $timeline[] = [
                    'status' => 'arrived',
                    'note' => 'Technician arrived at location',
                    'created_at' => $activeJob->updated_at->toISOString(),
                ];
            }

            if (in_array($activeJob->status, ['diagnosing', 'repairing', 'quality_check'])) {
                $timeline[] = [
                    'status' => 'in_progress',
                    'note' => 'Repair in progress',
                    'created_at' => $activeJob->updated_at->toISOString(),
                ];
            }

            if ($activeJob->status === 'completed') {
                $timeline[] = [
                    'status' => 'completed',
                    'note' => 'Repair completed',
                    'created_at' => ($activeJob->released_at ?? $activeJob->updated_at)->toISOString(),
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
