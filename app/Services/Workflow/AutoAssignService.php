<?php

namespace App\Services\Workflow;

use App\Models\Ticket;
use App\Models\Technician;
use App\Models\Job;
use App\Jobs\ProcessJobOfferTimeout;
use App\Services\Notification\NotificationService;

class AutoAssignService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function assign(Ticket $ticket): bool
    {
        // Find available technician
        $technician = $this->findAvailableTechnician($ticket);

        if (!$technician) {
            return false;
        }

        // Create job offer
        $offerTimeout = \App\Models\Setting::get('job_offer_timeout_minutes', 5);

        $job = Job::create([
            'ticket_id' => $ticket->id,
            'technician_id' => $technician->id,
            'status' => 'offered',
            'offer_deadline_at' => now()->addMinutes($offerTimeout),
        ]);

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
        $this->notificationService->notifyTechnician(
            $technician->user_id,
            'New Job Offer',
            "You have a new job offer for Ticket #{$ticket->id}",
            [
                'job_id' => $job->id,
                'ticket_id' => $ticket->id,
                'deadline' => $job->offer_deadline_at->toIso8601String(),
            ]
        );

        return true;
    }

    protected function findAvailableTechnician(Ticket $ticket): ?Technician
    {
        // Find technicians who are on duty and have zero active jobs
        $technicians = Technician::where('status', 'on_duty')
            ->where('active_jobs_count', 0)
            ->get();

        if ($technicians->isEmpty()) {
            return null;
        }

        // If multiple, prefer by proximity (if location data available)
        if ($technicians->count() > 1) {
            // TODO: Implement distance-based selection if customer location is available
            // For now, return first available
        }

        return $technicians->first();
    }
}
