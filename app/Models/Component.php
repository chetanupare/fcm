<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Component extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'barcode',
        'supplier_id',
        'reorder_level',
        'reorder_quantity',
        'last_reorder_date',
        'alert_sent',
        'name',
        'sku',
        'description',
        'category_id',
        'brand_id',
        'cost_price',
        'selling_price',
        'stock_quantity',
        'min_stock_level',
        'unit',
        'compatible_devices',
        'compatible_brands',
        'compatible_models',
        'part_number',
        'oem_part_number',
        'image',
        'is_active',
        'is_consumable',
        'total_used',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'compatible_devices' => 'array',
            'compatible_brands' => 'array',
            'compatible_models' => 'array',
            'is_active' => 'boolean',
            'is_consumable' => 'boolean',
            'last_reorder_date' => 'date',
            'alert_sent' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComponentCategory::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ComponentBrand::class, 'brand_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }

    public function needsReorder(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    public function shouldSendAlert(): bool
    {
        return $this->needsReorder() && !$this->alert_sent;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price == 0) {
            return 0;
        }
        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    public function incrementUsage(int $quantity = 1): void
    {
        $this->increment('total_used', $quantity);
    }
}
