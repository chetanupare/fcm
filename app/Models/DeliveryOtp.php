<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'customer_id',
        'otp',
        'type',
        'status',
        'expires_at',
        'verified_at',
        'verified_by',
        'verification_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    public function isValid(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }
}
