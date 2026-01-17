<?php

namespace App\Http\Controllers\Api\Technician;

use App\Http\Controllers\Controller;
use App\Services\Location\LocationTrackingService;
use App\Services\Notification\EtaNotificationService;
use Illuminate\Http\Request;

/**
 * @tags Technician
 * 
 * Status and location management
 */
class StatusController extends Controller
{
    protected LocationTrackingService $locationTrackingService;
    protected EtaNotificationService $etaNotificationService;

    public function __construct(
        LocationTrackingService $locationTrackingService,
        EtaNotificationService $etaNotificationService
    ) {
        $this->locationTrackingService = $locationTrackingService;
        $this->etaNotificationService = $etaNotificationService;
    }

    public function index(Request $request)
    {
        $technician = $request->user()->technician;
        
        if (!$technician) {
            return response()->json([
                'message' => 'Technician profile not found',
            ], 404);
        }

        return response()->json([
            'status' => $technician->status,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'status' => 'required|in:on_duty,off_duty',
        ]);

        $technician = $request->user()->technician;
        
        if (!$technician) {
            return response()->json([
                'message' => 'Technician profile not found',
            ], 404);
        }

        $technician->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Status updated',
            'status' => $technician->status,
        ]);
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'source' => 'nullable|in:manual,gps,api',
        ]);

        $technician = $request->user()->technician;

        // Record location update using service
        $this->locationTrackingService->recordLocationUpdate(
            $technician,
            $request->latitude,
            $request->longitude,
            $request->source ?? 'manual',
            $request->has('metadata') ? $request->metadata : null
        );

        // Check for approaching notifications
        $this->checkForApproachingNotifications($technician);

        return response()->json([
            'message' => 'Location updated',
        ]);
    }

    /**
     * Check if technician is approaching customer and send notification
     */
    protected function checkForApproachingNotifications($technician)
    {
        // Get current active job
        $activeJob = $technician->jobs()
            ->whereIn('status', ['en_route', 'component_pickup'])
            ->where('offer_accepted_at', '!=', null)
            ->first();

        if (!$activeJob || !$activeJob->ticket) {
            return;
        }

        // Calculate distance to customer location
        $customerLat = $activeJob->ticket->latitude;
        $customerLng = $activeJob->ticket->longitude;
        $technicianLat = $technician->latitude;
        $technicianLng = $technician->longitude;

        if (!$customerLat || !$customerLng || !$technicianLat || !$technicianLng) {
            return;
        }

        // Calculate distance in km
        $distance = $this->calculateDistance($customerLat, $customerLng, $technicianLat, $technicianLng);

        // Send approaching notification if within 2km and ETA is less than 10 minutes
        if ($distance <= 2.0) {
            $etaMinutes = ($distance * 2) + ($activeJob->estimated_duration_minutes ?? 0); // Rough ETA calculation

            if ($etaMinutes <= 10) {
                // Check if we already sent this notification recently (within last 30 minutes)
                $lastNotification = $activeJob->notifications()
                    ->where('type', 'technician_en_route')
                    ->where('created_at', '>=', now()->subMinutes(30))
                    ->exists();

                if (!$lastNotification) {
                    $this->etaNotificationService->sendTechnicianApproaching($activeJob);
                }
            }
        }
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    protected function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Earth radius in kilometers

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
