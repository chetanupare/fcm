<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'priority',
        'triage_minutes',
        'assignment_minutes',
        'response_minutes',
        'resolution_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'triage_minutes' => 'integer',
            'assignment_minutes' => 'integer',
            'response_minutes' => 'integer',
            'resolution_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public static function getByPriority(string $priority): ?self
    {
        return self::where('priority', $priority)
            ->where('is_active', true)
            ->first();
    }
}
