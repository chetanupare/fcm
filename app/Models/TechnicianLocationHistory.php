<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicianLocationHistory extends Model
{
    use HasFactory;

    protected $table = 'technician_location_history';

    protected $fillable = [
        'technician_id',
        'latitude',
        'longitude',
        'recorded_at',
        'source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'recorded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }
}
