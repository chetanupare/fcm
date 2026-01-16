<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Technician extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'user_id',
        'status',
        'active_jobs_count',
        'latitude',
        'longitude',
        'commission_rate',
        'total_revenue',
        'last_location_update',
    ];

    protected function casts(): array
    {
        return [
            'active_jobs_count' => 'integer',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'commission_rate' => 'decimal:2',
            'total_revenue' => 'decimal:2',
            'last_location_update' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function activeJobs(): HasMany
    {
        return $this->hasMany(Job::class)->whereNull('released_at');
    }

    public function isOnDuty(): bool
    {
        return $this->status === 'on_duty';
    }

    public function isAvailable(): bool
    {
        return $this->isOnDuty() && $this->active_jobs_count === 0;
    }

    public function incrementActiveJobs(): void
    {
        $this->increment('active_jobs_count');
    }

    public function decrementActiveJobs(): void
    {
        $this->decrement('active_jobs_count');
    }
}
