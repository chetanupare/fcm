<?php

namespace App\Services\Workflow;

use App\Models\Ticket;
use App\Models\SlaConfiguration;
use App\Models\TicketSlaTracking;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Log;

class SlaTrackingService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Initialize SLA tracking for a ticket
     */
    public function initializeSlaTracking(Ticket $ticket): TicketSlaTracking
    {
        $priority = $ticket->priority ?? 'normal';
        $slaConfig = SlaConfiguration::getByPriority($priority);

        if (!$slaConfig) {
            $slaConfig = SlaConfiguration::getByPriority('normal');
        }

        $now = now();

        $tracking = TicketSlaTracking::create([
            'ticket_id' => $ticket->id,
            'sla_configuration_id' => $slaConfig->id,
            'priority' => $priority,
            'triage_deadline_at' => $now->copy()->addMinutes($slaConfig->triage_minutes),
            'assignment_deadline_at' => $now->copy()->addMinutes($slaConfig->assignment_minutes),
            'response_deadline_at' => $now->copy()->addMinutes($slaConfig->response_minutes),
            'resolution_deadline_at' => $now->copy()->addMinutes($slaConfig->resolution_minutes),
        ]);

        return $tracking;
    }

    /**
     * Update SLA status for a ticket
     */
    public function updateSlaStatus(Ticket $ticket): void
    {
        $tracking = TicketSlaTracking::where('ticket_id', $ticket->id)->first();

        if (!$tracking) {
            $tracking = $this->initializeSlaTracking($ticket);
        }

        $now = now();

        // Update triage status
        if ($ticket->triage_handled_at) {
            $tracking->triage_completed_at = $ticket->triage_handled_at;
            $tracking->triage_status = $this->calculateStatus(
                $ticket->triage_handled_at,
                $tracking->triage_deadline_at
            );
        } else {
            $tracking->triage_status = $this->calculateStatus($now, $tracking->triage_deadline_at);
        }

        // Update assignment status
        if ($ticket->status === 'assigned' || $ticket->status === 'in_progress') {
            $tracking->assignment_completed_at = $now;
            $tracking->assignment_status = $this->calculateStatus($now, $tracking->assignment_deadline_at);
        } else {
            $tracking->assignment_status = $this->calculateStatus($now, $tracking->assignment_deadline_at);
        }

        // Update response status (when technician first updates job status)
        $job = $ticket->activeJob;
        if ($job && $job->status !== 'offered') {
            if (!$tracking->response_completed_at) {
                $tracking->response_completed_at = $now;
            }
            $tracking->response_status = $this->calculateStatus(
                $tracking->response_completed_at ?? $now,
                $tracking->response_deadline_at
            );
        } else {
            $tracking->response_status = $this->calculateStatus($now, $tracking->response_deadline_at);
        }

        // Update resolution status
        if (in_array($ticket->status, ['completed', 'closed'])) {
            $tracking->resolution_completed_at = $now;
            $tracking->resolution_status = $this->calculateStatus($now, $tracking->resolution_deadline_at);
        } else {
            $tracking->resolution_status = $this->calculateStatus($now, $tracking->resolution_deadline_at);
        }

        $tracking->save();

        // Check for escalations
        $this->checkAndEscalate($tracking);
    }

    /**
     * Calculate SLA status (pending, on_time, at_risk, breached)
     */
    protected function calculateStatus($currentTime, $deadline): string
    {
        if (!$deadline) {
            return 'pending';
        }

        $deadlineTime = is_string($deadline) ? \Carbon\Carbon::parse($deadline) : $deadline;
        $current = is_string($currentTime) ? \Carbon\Carbon::parse($currentTime) : $currentTime;

        if ($current->greaterThan($deadlineTime)) {
            return 'breached';
        }

        $minutesRemaining = $current->diffInMinutes($deadlineTime, false);
        $percentageRemaining = ($minutesRemaining / $deadlineTime->diffInMinutes($deadlineTime->copy()->subDays(1))) * 100;

        if ($percentageRemaining <= 10) {
            return 'at_risk';
        }

        if ($current->lessThanOrEqualTo($deadlineTime)) {
            return 'on_time';
        }

        return 'pending';
    }

    /**
     * Check if escalation is needed and escalate
     */
    protected function checkAndEscalate(TicketSlaTracking $tracking): void
    {
        $escalationRules = [
            'triage' => [
                'at_risk' => ['level' => 1, 'minutes' => 5],
                'breached' => ['level' => 2, 'minutes' => 10],
            ],
            'assignment' => [
                'at_risk' => ['level' => 1, 'minutes' => 15],
                'breached' => ['level' => 2, 'minutes' => 20],
            ],
            'response' => [
                'at_risk' => ['level' => 2, 'minutes' => 30],
                'breached' => ['level' => 3, 'minutes' => 60],
            ],
            'resolution' => [
                'at_risk' => ['level' => 2, 'minutes' => 60],
                'breached' => ['level' => 3, 'minutes' => 120],
            ],
        ];

        foreach (['triage', 'assignment', 'response', 'resolution'] as $milestone) {
            $statusField = $milestone . '_status';
            $status = $tracking->$statusField;

            if (isset($escalationRules[$milestone][$status])) {
                $rule = $escalationRules[$milestone][$status];
                
                // Check if we should escalate based on time since last escalation
                if ($tracking->last_escalated_at) {
                    $minutesSinceEscalation = now()->diffInMinutes($tracking->last_escalated_at);
                    if ($minutesSinceEscalation < $rule['minutes']) {
                        continue; // Too soon to escalate again
                    }
                }

                // Check if escalation level is appropriate
                if ($tracking->escalation_level < $rule['level']) {
                    $this->escalate($tracking, $milestone, $status, $rule['level']);
                }
            }
        }
    }

    /**
     * Escalate a ticket
     */
    protected function escalate(TicketSlaTracking $tracking, string $milestone, string $status, int $level): void
    {
        $levelNames = [
            1 => 'supervisor',
            2 => 'manager',
            3 => 'executive',
        ];

        $levelName = $levelNames[$level] ?? 'unknown';

        $reason = "SLA {$milestone} milestone is {$status}";

        $tracking->addEscalation($levelName, $reason);

        // Notify appropriate role
        $this->notifyEscalation($tracking->ticket, $levelName, $reason);

        Log::warning("Ticket {$tracking->ticket_id} escalated to {$levelName}", [
            'milestone' => $milestone,
            'status' => $status,
            'level' => $level,
        ]);
    }

    /**
     * Notify about escalation
     */
    protected function notifyEscalation(Ticket $ticket, string $level, string $reason): void
    {
        // TODO: Implement notification to appropriate role
        // This would notify supervisors, managers, or executives based on level
    }
}
