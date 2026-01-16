<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'alternate_phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'website',
        'tax_id',
        'payment_terms',
        'credit_limit',
        'status',
        'notes',
        'specializations',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'specializations' => 'array',
        ];
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(Component::class);
    }
}
