<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DeviceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deviceType) {
            if (empty($deviceType->slug)) {
                $deviceType->slug = Str::slug($deviceType->name);
            }
        });
    }

    public function brands(): HasMany
    {
        return $this->hasMany(DeviceBrand::class);
    }

    public function models(): HasMany
    {
        return $this->hasMany(DeviceModel::class);
    }
}
