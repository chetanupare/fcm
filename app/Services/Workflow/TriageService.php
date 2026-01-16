<?php

namespace App\Services\Workflow;

use App\Models\Ticket;
use App\Models\Setting;
use App\Jobs\ProcessTriageTimeout;
use Carbon\Carbon;

class TriageService
{
    public function createTicket(array $data): Ticket
    {
        $triageTimeout = Setting::get('triage_timeout_minutes', 5) ?? 5;
        
        $ticket = Ticket::create([
            'customer_id' => $data['customer_id'],
            'device_id' => $data['device_id'],
            'issue_description' => $data['issue_description'],
            'address' => $data['address'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'preferred_date' => $data['preferred_date'] ?? null,
            'preferred_time' => $data['preferred_time'] ?? null,
            'photos' => $data['photos'] ?? [],
            'status' => 'pending_triage',
            'priority' => 'normal',
            'triage_deadline_at' => now()->addMinutes($triageTimeout),
        ]);

        // Dispatch job to handle timeout
        ProcessTriageTimeout::dispatch($ticket->id)
            ->delay(now()->addMinutes($triageTimeout));

        return $ticket;
    }

    public function handleTriageTimeout(int $ticketId): void
    {
        $ticket = Ticket::find($ticketId);
        
        if (!$ticket || $ticket->status !== 'pending_triage') {
            return;
        }

        // Auto-assign if still pending
        $autoAssignService = app(AutoAssignService::class);
        $assigned = $autoAssignService->assign($ticket);

        if (!$assigned) {
            // No technician available - escalate to admin
            $ticket->update([
                'status' => 'triage',
                'priority' => 'high',
                'triage_handled_at' => now(),
            ]);

            // Notify admin
            $notificationService = app(\App\Services\Notification\NotificationService::class);
            $notificationService->notifyAdmin(
                'Auto-assign Failed',
                "Ticket #{$ticket->id} could not be auto-assigned. No available technicians.",
                ['ticket_id' => $ticket->id]
            );
        }
    }
}
