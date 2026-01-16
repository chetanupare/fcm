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
        'is_on_call',
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
            'is_on_call' => 'boolean',
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

    public function locationHistory(): HasMany
    {
        return $this->hasMany(TechnicianLocationHistory::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(TechnicianSkill::class)->where('is_active', true);
    }

    public function allSkills(): HasMany
    {
        return $this->hasMany(TechnicianSkill::class);
    }

    public function primarySkills(): HasMany
    {
        return $this->hasMany(TechnicianSkill::class)
            ->where('is_active', true)
            ->where('is_primary', true);
    }

    public function hasSkillForDeviceType(int $deviceTypeId, ?string $complexityLevel = null): bool
    {
        $query = $this->skills()->where('device_type_id', $deviceTypeId);

        if ($complexityLevel) {
            $query->where('complexity_level', $complexityLevel);
        }

        return $query->exists();
    }

    public function getSkillForDeviceType(int $deviceTypeId): ?TechnicianSkill
    {
        return $this->skills()
            ->where('device_type_id', $deviceTypeId)
            ->orderBy('complexity_level', 'desc')
            ->first();
    }
}
