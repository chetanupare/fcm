<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutsourceVendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'specialization',
        'services_offered',
        'status',
        'rating',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'services_offered' => 'array',
            'rating' => 'decimal:2',
        ];
    }

    public function requests(): HasMany
    {
        return $this->hasMany(OutsourceRequest::class, 'vendor_id');
    }
}
