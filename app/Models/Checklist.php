<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_type',
        'name',
        'description',
        'is_mandatory',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function jobChecklists(): HasMany
    {
        return $this->hasMany(JobChecklist::class);
    }

    public function scopeForDeviceType($query, $deviceType)
    {
        return $query->where(function ($q) use ($deviceType) {
            $q->where('device_type', $deviceType)
              ->orWhere('device_type', 'universal');
        });
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }
}
