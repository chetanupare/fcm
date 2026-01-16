<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmcContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'device_id',
        'contract_number',
        'start_date',
        'end_date',
        'duration_type',
        'contract_amount',
        'service_charge_per_visit',
        'visits_included',
        'visits_used',
        'status',
        'terms_and_conditions',
        'covered_services',
        'excluded_services',
        'auto_renew',
        'last_service_date',
        'next_service_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'last_service_date' => 'date',
            'next_service_date' => 'date',
            'contract_amount' => 'decimal:2',
            'service_charge_per_visit' => 'decimal:2',
            'covered_services' => 'array',
            'excluded_services' => 'array',
            'auto_renew' => 'boolean',
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

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->end_date >= now()->toDateString();
    }

    public function isExpired(): bool
    {
        return $this->end_date < now()->toDateString();
    }
}
