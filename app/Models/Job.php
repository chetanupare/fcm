<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Job extends Model
{
    use HasFactory;

    protected $table = 'service_jobs';

    protected $fillable = [
        'location_id',
        'ticket_id',
        'technician_id',
        'status',
        'distance_km',
        'estimated_duration_minutes',
        'offer_deadline_at',
        'offer_accepted_at',
        'quote_id',
        'contract_signed_at',
        'payment_received_at',
        'released_at',
        'after_photo',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'estimated_duration_minutes' => 'integer',
            'offer_deadline_at' => 'datetime',
            'offer_accepted_at' => 'datetime',
            'contract_signed_at' => 'datetime',
            'payment_received_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function dataRecoveryJob(): HasOne
    {
        return $this->hasOne(DataRecoveryJob::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(JobChecklist::class);
    }

    public function isOffered(): bool
    {
        return $this->status === 'offered';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isReleased(): bool
    {
        return $this->released_at !== null;
    }

    public function isOfferExpired(): bool
    {
        return $this->offer_deadline_at && $this->offer_deadline_at->isPast();
    }
}
