<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'assigned_to',
        'job_id',
        'ticket_id',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'started_at',
        'completed_at',
        'estimated_hours',
        'actual_hours',
        'tags',
        'attachments',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'tags' => 'array',
            'attachments' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'completed' && 
               $this->due_date && 
               $this->due_date < now()->toDateString();
    }
}
