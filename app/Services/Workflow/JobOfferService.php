<?php

namespace App\Services\Workflow;

use App\Models\Job;
use App\Models\Ticket;
use App\Models\Checklist;
use App\Models\JobChecklist;
use App\Services\Notification\NotificationService;

class JobOfferService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function accept(Job $job, bool $allowExpired = false): array
    {
        if (!$job->isOffered()) {
            return [
                'success' => false,
                'message' => "Job offer is no longer available. Current status: {$job->status}",
            ];
        }

        $isExpired = $job->isOfferExpired();
        $expiredMinutes = $isExpired && $job->offer_deadline_at 
            ? now()->diffInMinutes($job->offer_deadline_at) 
            : 0;

        // Allow accepting expired offers if:
        // 1. allowExpired flag is true (from API with confirmation)
        // 2. Or expired less than 30 minutes ago (grace period)
        if ($isExpired && !$allowExpired && $expiredMinutes > 30) {
            return [
                'success' => false,
                'message' => "Job offer has expired. The deadline was " . $job->offer_deadline_at->format('M d, Y h:i A'),
                'expired' => true,
                'expired_minutes' => $expiredMinutes,
            ];
        }

        // If expired but within grace period, extend the deadline
        if ($isExpired && $expiredMinutes <= 30) {
            $offerTimeout = \App\Models\Setting::get('job_offer_timeout_minutes', 5);
            $job->update([
                'offer_deadline_at' => now()->addMinutes($offerTimeout),
            ]);
        }

        $job->update([
            'status' => 'accepted',
            'offer_accepted_at' => now(),
        ]);

        $job->ticket->update([
            'status' => 'accepted',
        ]);

        // Auto-initialize checklists for this device type
        $this->initializeChecklists($job);

        // Notify customer using Laravel Notifications
        $customer = $job->ticket->customer;
        if ($customer) {
            $customer->notify(new \App\Notifications\JobStatusNotification(
                $job,
                'accepted',
                "A technician has been assigned to your repair request for Ticket #{$job->ticket->id}"
            ));
        }

        return [
            'success' => true,
            'message' => 'Job offer accepted successfully',
        ];
    }

    public function reject(Job $job, ?string $reason = null): bool
    {
        if (!$job->isOffered()) {
            return false;
        }

        // Decrement technician's active jobs
        $job->technician->decrementActiveJobs();

        // Return ticket to triage
        $ticket = $job->ticket;
        $ticket->update([
            'status' => 'triage',
            'priority' => 'high',
        ]);

        // Cancel the job
        $job->update([
            'status' => 'cancelled',
        ]);

        // Notify admin
        $this->notificationService->notifyAdmin(
            'Job Offer Rejected',
            "Technician rejected job offer for Ticket #{$ticket->id}" . ($reason ? ": {$reason}" : ''),
            ['ticket_id' => $ticket->id, 'job_id' => $job->id]
        );

        // Try to auto-assign again (with cooldown to prevent loops)
        $autoAssignService = app(AutoAssignService::class);
        $autoAssignService->assign($ticket);

        return true;
    }

    public function handleOfferTimeout(int $jobId): void
    {
        $job = Job::find($jobId);

        if (!$job || !$job->isOffered()) {
            return;
        }

        // Treat as rejection
        $this->reject($job, 'Offer timeout - no response from technician');
    }

    protected function initializeChecklists(Job $job): void
    {
        $deviceType = $job->ticket->device->device_type;

        $checklists = Checklist::forDeviceType($deviceType)->get();

        foreach ($checklists as $checklist) {
            JobChecklist::firstOrCreate(
                [
                    'job_id' => $job->id,
                    'checklist_id' => $checklist->id,
                ],
                [
                    'is_completed' => false,
                ]
            );
        }
    }
}
