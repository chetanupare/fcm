<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'status',
        'credentials',
        'settings',
        'mapping',
        'last_sync_at',
        'last_error',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'array',
            'settings' => 'array',
            'mapping' => 'array',
            'last_sync_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
