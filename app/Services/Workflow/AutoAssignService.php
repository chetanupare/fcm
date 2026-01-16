<?php

namespace App\Services\Workflow;

use App\Models\Ticket;
use App\Models\Technician;
use App\Models\Job;
use App\Jobs\ProcessJobOfferTimeout;
use App\Services\Notification\NotificationService;
use App\Services\Location\DistanceCalculationService;
use App\Services\Workflow\SkillMatchingService;

class AutoAssignService
{
    protected NotificationService $notificationService;
    protected DistanceCalculationService $distanceService;
    protected SkillMatchingService $skillMatchingService;

    public function __construct(
        NotificationService $notificationService,
        DistanceCalculationService $distanceService,
        SkillMatchingService $skillMatchingService
    ) {
        $this->notificationService = $notificationService;
        $this->distanceService = $distanceService;
        $this->skillMatchingService = $skillMatchingService;
    }

    public function assign(Ticket $ticket): bool
    {
        // Find available technician
        $technician = $this->findAvailableTechnician($ticket);

        if (!$technician) {
            return false;
        }

        // Calculate distance and duration
        $distanceData = $this->distanceService->calculateTechnicianToTicketDistance($technician, $ticket);

        // Create job offer
        $offerTimeout = \App\Models\Setting::get('job_offer_timeout_minutes', 5);

        $jobData = [
            'ticket_id' => $ticket->id,
            'technician_id' => $technician->id,
            'status' => 'offered',
            'offer_deadline_at' => now()->addMinutes($offerTimeout),
        ];

        // Add distance data if available
        if ($distanceData) {
            $jobData['distance_km'] = $distanceData['distance_km'];
            $jobData['estimated_duration_minutes'] = $distanceData['duration_minutes'];
        }

        $job = Job::create($jobData);

        // Update ticket status
        $ticket->update([
            'status' => 'assigned',
            'triage_handled_at' => now(),
        ]);

        // Increment technician's active jobs count
        $technician->incrementActiveJobs();

        // Dispatch timeout job
        ProcessJobOfferTimeout::dispatch($job->id)
            ->delay(now()->addMinutes($offerTimeout));

        // Notify technician
        $notificationData = [
            'job_id' => $job->id,
            'ticket_id' => $ticket->id,
            'deadline' => $job->offer_deadline_at->toIso8601String(),
        ];

        if ($distanceData) {
            $notificationData['distance_km'] = $distanceData['distance_km'];
            $notificationData['estimated_duration'] = $distanceData['duration_text'];
        }

        $this->notificationService->notifyTechnician(
            $technician->user_id,
            'New Job Offer',
            "You have a new job offer for Ticket #{$ticket->id}" . 
            ($distanceData ? " ({$distanceData['distance_text']}, {$distanceData['duration_text']})" : ''),
            $notificationData
        );

        return true;
    }

    protected function findAvailableTechnician(Ticket $ticket): ?Technician
    {
        // First, try to find on-call technicians (fallback pool)
        $onCallTechnicians = Technician::where('status', 'on_duty')
            ->where('is_on_call', true)
            ->where('active_jobs_count', 0)
            ->get();

        // Find technicians who are on duty and have zero active jobs
        $technicians = Technician::where('status', 'on_duty')
            ->where('active_jobs_count', 0)
            ->get();

        // If no regular technicians available, use on-call pool
        if ($technicians->isEmpty() && !$onCallTechnicians->isEmpty()) {
            $technicians = $onCallTechnicians;
        }

        if ($technicians->isEmpty()) {
            return null;
        }

        // If multiple technicians, use combined scoring (skill + distance)
        if ($technicians->count() > 1) {
            // First, filter by minimum skill match (if device type is known)
            $device = $ticket->device;
            if ($device && $device->device_type_id) {
                $technicians = $this->skillMatchingService->filterByMinScore($technicians, $ticket, 30);
                
                if ($technicians->isEmpty()) {
                    // No technicians meet minimum skill requirement, use all available
                    $technicians = Technician::where('status', 'on_duty')
                        ->where('active_jobs_count', 0)
                        ->get();
                }
            }

            // Calculate combined scores (skill match + distance)
            $scoredTechnicians = $technicians->map(function ($technician) use ($ticket) {
                // Skill match score (0-100)
                $skillScore = $this->skillMatchingService->calculateMatchScore($technician, $ticket);
                $technician->skill_match_score = $skillScore;

                // Distance score (0-100, closer = higher score)
                $distanceData = $this->distanceService->calculateTechnicianToTicketDistance($technician, $ticket);
                $technician->distance_to_ticket = $distanceData;
                $technician->distance_km = $distanceData ? $distanceData['distance_km'] : null;

                // Combined score: 60% skill, 40% distance (if distance available)
                if ($technician->distance_km !== null) {
                    // Normalize distance to 0-100 score (closer = higher)
                    // Assuming max reasonable distance is 50km
                    $maxDistance = 50;
                    $distanceScore = max(0, 100 - (($technician->distance_km / $maxDistance) * 100));
                    $technician->combined_score = ($skillScore * 0.6) + ($distanceScore * 0.4);
                } else {
                    // No distance data, use skill score only
                    $technician->combined_score = $skillScore * 0.6;
                }

                return $technician;
            })->sortByDesc('combined_score')->values();

            // Return best matching technician
            return $scoredTechnicians->first();
        }

        return $technicians->first();
    }
}
