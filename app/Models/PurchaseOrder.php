<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'created_by',
        'po_number',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'status',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'currency',
        'notes',
        'terms',
        'line_items',
        'pdf_file',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'expected_delivery_date' => 'date',
            'actual_delivery_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'line_items' => 'array',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'received' && 
               $this->expected_delivery_date && 
               $this->expected_delivery_date < now()->toDateString();
    }
}
