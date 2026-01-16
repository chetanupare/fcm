<?php

namespace App\Services\Location;

use App\Models\Technician;
use App\Models\TechnicianLocationHistory;
use Illuminate\Support\Facades\Log;

class LocationTrackingService
{
    /**
     * Record technician location update
     * 
     * @param Technician $technician
     * @param float $latitude
     * @param float $longitude
     * @param string $source
     * @param array|null $metadata
     * @return void
     */
    public function recordLocationUpdate(
        Technician $technician,
        float $latitude,
        float $longitude,
        string $source = 'manual',
        ?array $metadata = null
    ): void {
        // Update technician's current location
        $technician->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'last_location_update' => now(),
        ]);

        // Record in history
        TechnicianLocationHistory::create([
            'technician_id' => $technician->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'recorded_at' => now(),
            'source' => $source,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Check if technician location is recent (within threshold)
     * 
     * @param Technician $technician
     * @param int $thresholdMinutes
     * @return bool
     */
    public function isLocationRecent(Technician $technician, int $thresholdMinutes = 15): bool
    {
        if (!$technician->last_location_update) {
            return false;
        }

        return $technician->last_location_update->diffInMinutes(now()) <= $thresholdMinutes;
    }

    /**
     * Get location update frequency for a technician
     * 
     * @param Technician $technician
     * @param int $hours
     * @return array
     */
    public function getLocationUpdateFrequency(Technician $technician, int $hours = 24): array
    {
        $since = now()->subHours($hours);
        
        $updates = TechnicianLocationHistory::where('technician_id', $technician->id)
            ->where('recorded_at', '>=', $since)
            ->orderBy('recorded_at', 'asc')
            ->get();

        $totalUpdates = $updates->count();
        $averageInterval = null;
        $lastUpdate = $updates->last();

        if ($totalUpdates > 1) {
            $firstUpdate = $updates->first();
            $timeSpan = $firstUpdate->recorded_at->diffInMinutes($lastUpdate->recorded_at);
            $averageInterval = $timeSpan > 0 ? round($timeSpan / ($totalUpdates - 1), 1) : null;
        }

        return [
            'total_updates' => $totalUpdates,
            'average_interval_minutes' => $averageInterval,
            'last_update' => $lastUpdate ? $lastUpdate->recorded_at : null,
            'is_active' => $this->isLocationRecent($technician),
        ];
    }

    /**
     * Monitor location update frequency and log warnings
     * 
     * @param Technician $technician
     * @param int $expectedIntervalMinutes
     * @return void
     */
    public function monitorLocationFrequency(Technician $technician, int $expectedIntervalMinutes = 5): void
    {
        $frequency = $this->getLocationUpdateFrequency($technician, 1);

        if ($frequency['total_updates'] === 0) {
            Log::warning("Technician {$technician->id} has no location updates in the last hour");
            return;
        }

        if ($frequency['average_interval_minutes'] && 
            $frequency['average_interval_minutes'] > ($expectedIntervalMinutes * 2)) {
            Log::warning("Technician {$technician->id} location updates are infrequent", [
                'average_interval' => $frequency['average_interval_minutes'],
                'expected_interval' => $expectedIntervalMinutes,
            ]);
        }
    }

    /**
     * Get recent location history for a technician
     * 
     * @param Technician $technician
     * @param int $hours
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentLocationHistory(Technician $technician, int $hours = 24)
    {
        return TechnicianLocationHistory::where('technician_id', $technician->id)
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at', 'desc')
            ->get();
    }
}
