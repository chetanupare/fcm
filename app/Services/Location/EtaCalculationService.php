<?php

namespace App\Services\Location;

use App\Models\Technician;
use App\Models\Ticket;
use App\Models\Job;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class EtaCalculationService
{
    protected ?string $apiKey;
    protected DistanceCalculationService $distanceService;

    public function __construct(DistanceCalculationService $distanceService)
    {
        $this->apiKey = Setting::get('google_maps_api_key');
        $this->distanceService = $distanceService;
    }

    /**
     * Calculate ETA from technician to customer location
     * 
     * @param Technician $technician
     * @param Ticket|Job $destination
     * @return array|null Returns array with 'eta_minutes', 'eta_text', 'arrival_time', 'arrival_window'
     */
    public function calculateEta($technician, $destination): ?array
    {
        if (!$technician->latitude || !$technician->longitude) {
            return null;
        }

        $destinationLat = null;
        $destinationLon = null;

        if ($destination instanceof Ticket) {
            $destinationLat = $destination->latitude;
            $destinationLon = $destination->longitude;
            
            if (!$destinationLat || !$destinationLon) {
                if ($destination->location && $destination->location->latitude && $destination->location->longitude) {
                    $destinationLat = $destination->location->latitude;
                    $destinationLon = $destination->location->longitude;
                } else {
                    return null;
                }
            }
        } elseif ($destination instanceof Job) {
            $ticket = $destination->ticket;
            if ($ticket) {
                $destinationLat = $ticket->latitude;
                $destinationLon = $ticket->longitude;
                
                if (!$destinationLat || !$destinationLon) {
                    if ($ticket->location && $ticket->location->latitude && $ticket->location->longitude) {
                        $destinationLat = $ticket->location->latitude;
                        $destinationLon = $ticket->location->longitude;
                    } else {
                        return null;
                    }
                }
            } else {
                return null;
            }
        }

        if (!$destinationLat || !$destinationLon) {
            return null;
        }

        // Use distance service to get duration
        $distanceData = $this->distanceService->calculateDistance(
            $technician->latitude,
            $technician->longitude,
            $destinationLat,
            $destinationLon
        );

        if (!$distanceData) {
            return null;
        }

        $etaMinutes = $distanceData['duration_minutes'];
        $now = now();
        $arrivalTime = $now->copy()->addMinutes($etaMinutes);
        
        // Create time window (±10% of ETA)
        $windowMinutes = max(5, round($etaMinutes * 0.1));
        $arrivalWindowStart = $arrivalTime->copy()->subMinutes($windowMinutes);
        $arrivalWindowEnd = $arrivalTime->copy()->addMinutes($windowMinutes);

        return [
            'eta_minutes' => round($etaMinutes, 1),
            'eta_text' => $distanceData['duration_text'] ?? round($etaMinutes) . ' mins',
            'arrival_time' => $arrivalTime->toIso8601String(),
            'arrival_window_start' => $arrivalWindowStart->toIso8601String(),
            'arrival_window_end' => $arrivalWindowEnd->toIso8601String(),
            'arrival_window_text' => $arrivalWindowStart->format('g:i A') . ' - ' . $arrivalWindowEnd->format('g:i A'),
            'distance_km' => $distanceData['distance_km'] ?? null,
            'distance_text' => $distanceData['distance_text'] ?? null,
        ];
    }

    /**
     * Update ETA for a job (called when technician location updates)
     * 
     * @param Job $job
     * @return array|null
     */
    public function updateJobEta(Job $job): ?array
    {
        if (!$job->technician) {
            return null;
        }

        $eta = $this->calculateEta($job->technician, $job);
        
        if ($eta) {
            // Store ETA in job or cache for real-time updates
            // For now, we'll return it - can be stored in cache or job metadata
            return $eta;
        }

        return null;
    }

    /**
     * Check if ETA has changed significantly (>10 minutes)
     * 
     * @param array $oldEta
     * @param array $newEta
     * @return bool
     */
    public function hasEtaChangedSignificantly(array $oldEta, array $newEta): bool
    {
        $oldMinutes = $oldEta['eta_minutes'] ?? 0;
        $newMinutes = $newEta['eta_minutes'] ?? 0;
        
        return abs($oldMinutes - $newMinutes) > 10;
    }

    /**
     * Get ETA for customer display
     * 
     * @param Job $job
     * @return array|null
     */
    public function getCustomerEta(Job $job): ?array
    {
        if (!$job->technician) {
            return null;
        }

        $eta = $this->calculateEta($job->technician, $job);
        
        if ($eta) {
            return [
                'eta_minutes' => $eta['eta_minutes'],
                'eta_text' => $eta['eta_text'],
                'arrival_window' => $eta['arrival_window_text'],
                'arrival_time' => $eta['arrival_time'],
                'distance_km' => $eta['distance_km'],
            ];
        }

        return null;
    }
}
