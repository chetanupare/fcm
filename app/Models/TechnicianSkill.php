<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicianSkill extends Model
{
    use HasFactory;

    protected $fillable = [
        'technician_id',
        'device_type_id',
        'complexity_level',
        'specialization',
        'certifications',
        'experience_years',
        'jobs_completed',
        'success_rate',
        'is_primary',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'certifications' => 'array',
            'experience_years' => 'integer',
            'jobs_completed' => 'integer',
            'success_rate' => 'decimal:2',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class);
    }

    public function isExpertLevel(): bool
    {
        return $this->complexity_level === 'expert';
    }

    public function isAdvancedLevel(): bool
    {
        return in_array($this->complexity_level, ['advanced', 'expert']);
    }

    public function hasCertification(string $certification): bool
    {
        if (!$this->certifications) {
            return false;
        }

        return in_array($certification, $this->certifications);
    }
}
