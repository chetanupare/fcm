<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'device_type', // Legacy field - keep for backward compatibility
        'brand', // Legacy field
        'model', // Legacy field
        'device_type_id',
        'device_brand_id',
        'device_model_id',
        'serial_number',
        'purchase_date',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class);
    }

    public function deviceBrand(): BelongsTo
    {
        return $this->belongsTo(DeviceBrand::class);
    }

    public function deviceModel(): BelongsTo
    {
        return $this->belongsTo(DeviceModel::class);
    }

    // Accessor to get device type name (from relationship or legacy field)
    public function getDeviceTypeNameAttribute()
    {
        return $this->deviceType ? $this->deviceType->name : $this->device_type;
    }

    // Accessor to get brand name (from relationship or legacy field)
    public function getBrandNameAttribute()
    {
        return $this->deviceBrand ? $this->deviceBrand->name : $this->brand;
    }

    // Accessor to get model name (from relationship or legacy field)
    public function getModelNameAttribute()
    {
        return $this->deviceModel ? $this->deviceModel->name : $this->model;
    }
}
