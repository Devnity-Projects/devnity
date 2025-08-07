<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TaskChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'title',
        'description',
        'is_completed',
        'order',
        'assigned_to',
        'due_date',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'order' => 'integer',
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('created_at');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('is_completed', true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_completed', false);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date < now() && !$this->is_completed;
    }
}
