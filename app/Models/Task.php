<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'project_id',
        'assigned_to',
        'status',
        'priority',
        'type',
        'hours_estimated',
        'hours_worked',
        'due_date',
        'order',
        'labels',
        'acceptance_criteria',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'hours_estimated' => 'decimal:2',
        'hours_worked' => 'decimal:2',
        'due_date' => 'date',
        'labels' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_REVIEW = 'review';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    // Priority constants
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    // Type constants
    public const TYPE_FEATURE = 'feature';
    public const TYPE_BUG = 'bug';
    public const TYPE_ENHANCEMENT = 'enhancement';
    public const TYPE_DOCUMENTATION = 'documentation';
    public const TYPE_TESTING = 'testing';

    // Relationships
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Temporarily commented out to avoid autoloading issues
    /*
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TaskComment::class);
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TaskAttachment::class);
    }

    public function checklist(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TaskChecklist::class);
    }

    public function activities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TaskActivity::class);
    }
    */

    // Scopes
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeByProject(Builder $query, int $projectId): Builder
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && 
               $this->due_date < now() && 
               !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_TODO => 'A Fazer',
            self::STATUS_IN_PROGRESS => 'Em Progresso',
            self::STATUS_REVIEW => 'Em Revisão',
            self::STATUS_COMPLETED => 'Concluído',
            self::STATUS_CANCELLED => 'Cancelado',
            default => 'A Fazer',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'Baixa',
            self::PRIORITY_MEDIUM => 'Média',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
            default => 'Média',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_FEATURE => 'Funcionalidade',
            self::TYPE_BUG => 'Bug',
            self::TYPE_ENHANCEMENT => 'Melhoria',
            self::TYPE_DOCUMENTATION => 'Documentação',
            self::TYPE_TESTING => 'Teste',
            default => 'Funcionalidade',
        };
    }

    public function getTimeSpentAttribute(): string
    {
        if (!$this->hours_worked || $this->hours_worked == 0) return '0h';
        
        $hoursWorked = (float) $this->hours_worked;
        $hours = floor($hoursWorked);
        $minutes = ($hoursWorked - $hours) * 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isOverdue(): bool
    {
        return $this->is_overdue;
    }

    public function canBeStarted(): bool
    {
        return $this->status === self::STATUS_TODO;
    }

    public function canBeCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_IN_PROGRESS, self::STATUS_REVIEW]);
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }
}
