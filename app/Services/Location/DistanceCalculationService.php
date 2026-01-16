<?php

namespace App\Services\Location;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class DistanceCalculationService
{
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = Setting::get('google_maps_api_key');
    }

    /**
     * Calculate distance between two coordinates using Google Maps Distance Matrix API
     * 
     * @param float $lat1 Latitude of first point
     * @param float $lon1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lon2 Longitude of second point
     * @return array|null Returns array with 'distance_km', 'distance_miles', 'duration_minutes', 'duration_text' or null on failure
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): ?array
    {
        if (!$this->apiKey) {
            Log::warning('Google Maps API key not configured, using Haversine formula for distance calculation');
            return $this->calculateHaversineDistance($lat1, $lon1, $lat2, $lon2);
        }

        try {
            $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => "{$lat1},{$lon1}",
                'destinations' => "{$lat2},{$lon2}",
                'key' => $this->apiKey,
                'units' => 'metric',
                'mode' => 'driving',
            ]);

            $data = $response->json();

            if ($response->successful() && $data['status'] === 'OK' && isset($data['rows'][0]['elements'][0])) {
                $element = $data['rows'][0]['elements'][0];

                if ($element['status'] === 'OK') {
                    return [
                        'distance_km' => $element['distance']['value'] / 1000, // Convert meters to km
                        'distance_miles' => $element['distance']['value'] / 1609.34, // Convert meters to miles
                        'duration_minutes' => round($element['duration']['value'] / 60, 1), // Convert seconds to minutes
                        'duration_text' => $element['duration']['text'],
                        'distance_text' => $element['distance']['text'],
                    ];
                }
            }

            // Fallback to Haversine if API fails
            Log::warning('Google Maps API returned error, using Haversine formula', [
                'status' => $data['status'] ?? 'unknown',
                'error_message' => $data['error_message'] ?? null,
            ]);

            return $this->calculateHaversineDistance($lat1, $lon1, $lat2, $lon2);
        } catch (\Exception $e) {
            Log::error('Error calculating distance via Google Maps API', [
                'error' => $e->getMessage(),
            ]);

            // Fallback to Haversine
            return $this->calculateHaversineDistance($lat1, $lon1, $lat2, $lon2);
        }
    }

    /**
     * Calculate straight-line distance using Haversine formula (fallback)
     * 
     * @param float $lat1 Latitude of first point
     * @param float $lon1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lon2 Longitude of second point
     * @return array Returns array with 'distance_km', 'distance_miles', 'duration_minutes' (estimated)
     */
    protected function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): array
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distanceKm = $earthRadius * $c;

        // Estimate duration based on average speed (50 km/h for city driving)
        $averageSpeedKmh = 50;
        $durationMinutes = ($distanceKm / $averageSpeedKmh) * 60;

        return [
            'distance_km' => round($distanceKm, 2),
            'distance_miles' => round($distanceKm * 0.621371, 2),
            'duration_minutes' => round($durationMinutes, 1),
            'duration_text' => round($durationMinutes) . ' mins',
            'distance_text' => round($distanceKm, 1) . ' km',
            'method' => 'haversine', // Indicate this is an estimate
        ];
    }

    /**
     * Calculate distance from technician to ticket location
     * 
     * @param \App\Models\Technician $technician
     * @param \App\Models\Ticket $ticket
     * @return array|null
     */
    public function calculateTechnicianToTicketDistance($technician, $ticket): ?array
    {
        if (!$technician->latitude || !$technician->longitude) {
            return null;
        }

        if (!$ticket->latitude || !$ticket->longitude) {
            // Try to get location from ticket's location model
            if ($ticket->location && $ticket->location->latitude && $ticket->location->longitude) {
                return $this->calculateDistance(
                    $technician->latitude,
                    $technician->longitude,
                    $ticket->location->latitude,
                    $ticket->location->longitude
                );
            }
            return null;
        }

        return $this->calculateDistance(
            $technician->latitude,
            $technician->longitude,
            $ticket->latitude,
            $ticket->longitude
        );
    }

    /**
     * Sort technicians by distance to ticket (closest first)
     * 
     * @param \Illuminate\Support\Collection $technicians
     * @param \App\Models\Ticket $ticket
     * @return \Illuminate\Support\Collection
     */
    public function sortTechniciansByDistance($technicians, $ticket)
    {
        return $technicians->map(function ($technician) use ($ticket) {
            $distance = $this->calculateTechnicianToTicketDistance($technician, $ticket);
            $technician->distance_to_ticket = $distance;
            $technician->distance_km = $distance ? $distance['distance_km'] : null;
            return $technician;
        })->sortBy(function ($technician) {
            return $technician->distance_km ?? PHP_INT_MAX;
        })->values();
    }
}
