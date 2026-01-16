<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataRecoveryJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'job_id',
        'technician_id',
        'recovery_number',
        'recovery_type',
        'failure_type',
        'status',
        'estimated_cost',
        'final_cost',
        'estimated_data_size_gb',
        'recovered_data_size_gb',
        'recovery_percentage',
        'customer_requirements',
        'recovery_notes',
        'recovered_files_list',
        'delivery_method',
        'estimated_completion_date',
        'actual_completion_date',
        'customer_notified',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
            'final_cost' => 'decimal:2',
            'recovery_percentage' => 'decimal:2',
            'recovered_files_list' => 'array',
            'estimated_completion_date' => 'date',
            'actual_completion_date' => 'date',
            'customer_notified' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function getRecoverySuccessRate(): float
    {
        if (!$this->recovery_percentage) {
            return 0;
        }
        return (float) $this->recovery_percentage;
    }
}
