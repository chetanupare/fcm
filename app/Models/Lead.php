<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'assigned_to',
        'name',
        'email',
        'phone',
        'company',
        'address',
        'source',
        'status',
        'priority',
        'estimated_value',
        'description',
        'notes',
        'tags',
        'follow_up_date',
        'converted_to_customer_id',
        'converted_to_ticket_id',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'tags' => 'array',
            'follow_up_date' => 'date',
            'converted_at' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedToCustomer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_to_customer_id');
    }

    public function convertedToTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'converted_to_ticket_id');
    }

    public function isConverted(): bool
    {
        return $this->status === 'converted' && $this->converted_to_customer_id !== null;
    }
}
