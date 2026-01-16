<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'location_id',
        'device_id',
        'issue_description',
        'address',
        'latitude',
        'longitude',
        'preferred_date',
        'preferred_time',
        'photos',
        'device_images',
        'device_images_uploaded_at',
        'status',
        'priority',
        'triage_deadline_at',
        'triage_handled_at',
        'is_warranty',
    ];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'device_images' => 'array',
            'device_images_uploaded_at' => 'datetime',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'preferred_date' => 'date',
            'preferred_time' => 'datetime',
            'triage_deadline_at' => 'datetime',
            'triage_handled_at' => 'datetime',
            'is_warranty' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function dataRecoveryJob()
    {
        return $this->hasOne(DataRecoveryJob::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function activeJob(): HasOne
    {
        return $this->hasOne(Job::class)->whereNotIn('status', ['cancelled', 'completed']);
    }

    public function isPendingTriage(): bool
    {
        return $this->status === 'pending_triage';
    }

    public function isInTriage(): bool
    {
        return in_array($this->status, ['pending_triage', 'triage']);
    }
}
