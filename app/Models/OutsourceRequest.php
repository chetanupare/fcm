<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutsourceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'job_id',
        'ticket_id',
        'created_by',
        'request_number',
        'title',
        'description',
        'status',
        'quoted_amount',
        'final_amount',
        'requested_date',
        'completion_date',
        'vendor_notes',
        'internal_notes',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'quoted_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'requested_date' => 'date',
            'completion_date' => 'date',
            'attachments' => 'array',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(OutsourceVendor::class, 'vendor_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
