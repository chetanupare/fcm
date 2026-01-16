<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketSlaTracking extends Model
{
    use HasFactory;

    protected $table = 'ticket_sla_tracking';

    protected $fillable = [
        'ticket_id',
        'sla_configuration_id',
        'priority',
        'triage_deadline_at',
        'triage_completed_at',
        'assignment_deadline_at',
        'assignment_completed_at',
        'response_deadline_at',
        'response_completed_at',
        'resolution_deadline_at',
        'resolution_completed_at',
        'triage_status',
        'assignment_status',
        'response_status',
        'resolution_status',
        'escalation_level',
        'last_escalated_at',
        'escalation_history',
    ];

    protected function casts(): array
    {
        return [
            'triage_deadline_at' => 'datetime',
            'triage_completed_at' => 'datetime',
            'assignment_deadline_at' => 'datetime',
            'assignment_completed_at' => 'datetime',
            'response_deadline_at' => 'datetime',
            'response_completed_at' => 'datetime',
            'resolution_deadline_at' => 'datetime',
            'resolution_completed_at' => 'datetime',
            'escalation_level' => 'integer',
            'last_escalated_at' => 'datetime',
            'escalation_history' => 'array',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function slaConfiguration(): BelongsTo
    {
        return $this->belongsTo(SlaConfiguration::class);
    }

    public function addEscalation(string $level, string $reason, ?int $escalatedBy = null): void
    {
        $history = $this->escalation_history ?? [];
        $history[] = [
            'level' => $level,
            'reason' => $reason,
            'escalated_by' => $escalatedBy,
            'escalated_at' => now()->toIso8601String(),
        ];

        $this->update([
            'escalation_history' => $history,
            'last_escalated_at' => now(),
            'escalation_level' => $this->escalation_level + 1,
        ]);
    }
}
