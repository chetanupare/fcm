<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicianPerformance extends Model
{
    use HasFactory;

    protected $table = 'technician_performance';

    protected $fillable = [
        'technician_id',
        'period_start',
        'period_end',
        'total_jobs',
        'completed_jobs',
        'on_time_completions',
        'late_completions',
        'average_rating',
        'total_ratings',
        'total_hours_worked',
        'revenue_generated',
        'customer_satisfaction_score',
        'first_time_fix_rate',
        'rework_rate',
        'metrics',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'average_rating' => 'decimal:2',
            'revenue_generated' => 'decimal:2',
            'metrics' => 'array',
        ];
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function getCompletionRate(): float
    {
        if ($this->total_jobs === 0) {
            return 0;
        }
        return ($this->completed_jobs / $this->total_jobs) * 100;
    }

    public function getOnTimeRate(): float
    {
        if ($this->completed_jobs === 0) {
            return 0;
        }
        return ($this->on_time_completions / $this->completed_jobs) * 100;
    }
}
