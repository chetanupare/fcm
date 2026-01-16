<?php

namespace App\Http\Controllers\Api\Technician;

use App\Http\Controllers\Controller;
use App\Services\Location\LocationTrackingService;
use Illuminate\Http\Request;

/**
 * @tags Technician
 * 
 * Status and location management
 */
class StatusController extends Controller
{
    protected LocationTrackingService $locationTrackingService;

    public function __construct(LocationTrackingService $locationTrackingService)
    {
        $this->locationTrackingService = $locationTrackingService;
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

        return response()->json([
            'message' => 'Location updated',
        ]);
    }
}
