<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

/**
 * @tags Customer
 * 
 * Ticket tracking and status
 */
class TrackingController extends Controller
{
    public function devices()
    {
        $devices = auth()->user()->devices;
        
        return response()->json([
            'devices' => $devices,
        ]);
    }

    public function tickets()
    {
        $tickets = Ticket::where('customer_id', auth()->id())
            ->with(['device', 'activeJob.technician.user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($ticket) {
                $job = $ticket->activeJob;
                return [
                    'id' => $ticket->id,
                    'status' => $ticket->status,
                    'device' => $ticket->device->brand . ' ' . $ticket->device->device_type,
                    'issue' => $ticket->issue_description,
                    'technician' => $job && $job->technician ? [
                        'name' => $job->technician->user->name,
                    ] : null,
                    'created_at' => $ticket->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'tickets' => $tickets,
        ]);
    }

    public function track(int $ticketId)
    {
        $ticket = Ticket::with(['customer', 'device', 'activeJob.technician.user'])
            ->where('customer_id', auth()->id())
            ->findOrFail($ticketId);

        $job = $ticket->activeJob;
        
        $timeline = [];
        
        if ($job) {
            $timeline[] = [
                'status' => 'offered',
                'created_at' => $job->created_at,
                'note' => 'Job offer sent to technician',
            ];
            
            if ($job->offer_accepted_at) {
                $timeline[] = [
                    'status' => 'accepted',
                    'created_at' => $job->offer_accepted_at,
                    'note' => 'Technician accepted the job',
                ];
            }
            
            $timeline[] = [
                'status' => $job->status,
                'created_at' => $job->updated_at,
                'note' => 'Current status',
            ];
        }

        // Calculate ETA if technician is en route
        $eta = null;
        if ($job && $job->technician && in_array($job->status, ['en_route', 'component_pickup'])) {
            $etaService = app(\App\Services\Location\EtaCalculationService::class);
            $eta = $etaService->getCustomerEta($job);
        }

        // Get technician location for map
        $technicianLocation = null;
        if ($job && $job->technician && in_array($job->status, ['en_route', 'component_pickup', 'arrived'])) {
            // Get latest location from technician location history
            $latestLocation = $job->technician->locationHistory()
                ->latest('created_at')
                ->first();

            if ($latestLocation) {
                $technicianLocation = [
                    'lat' => $latestLocation->latitude,
                    'lng' => $latestLocation->longitude,
                    'updated_at' => $latestLocation->created_at,
                ];
            }
        }

        return response()->json([
            'id' => $ticket->id,
            'status' => $ticket->status,
            'device' => $ticket->device->brand . ' ' . $ticket->device->device_type,
            'issue' => $ticket->issue_description,
            'address' => $ticket->address,
            'customer_location' => $ticket->latitude && $ticket->longitude ? [
                'lat' => $ticket->latitude,
                'lng' => $ticket->longitude,
            ] : null,
            'technician' => $job && $job->technician ? [
                'id' => $job->technician->id,
                'name' => $job->technician->user->name,
                'phone' => $job->technician->user->phone,
                'location' => $technicianLocation,
            ] : null,
            'job' => $job ? [
                'id' => $job->id,
                'status' => $job->status,
                'distance_km' => $job->distance_km,
                'estimated_duration_minutes' => $job->estimated_duration_minutes,
            ] : null,
            'eta' => $eta,
            'timeline' => $timeline,
            'created_at' => $ticket->created_at,
        ]);
    }
}
