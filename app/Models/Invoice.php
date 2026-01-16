<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'job_id',
        'quote_id',
        'amc_contract_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'currency',
        'notes',
        'terms',
        'line_items',
        'pdf_file',
        'sent_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'line_items' => 'array',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function amcContract(): BelongsTo
    {
        return $this->belongsTo(AmcContract::class);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date < now()->toDateString();
    }

    public function getRemainingAmount(): float
    {
        return $this->total_amount - $this->paid_amount;
    }
}
