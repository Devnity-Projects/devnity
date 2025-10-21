<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'client_id',
        'status',
        'priority',
        'type',
        'budget',
        'hours_estimated',
        'hours_worked',
        'start_date',
        'end_date',
        'deadline',
        'technologies',
        'repository_url',
        'demo_url',
        'production_url',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'deadline' => 'date',
        'budget' => 'decimal:2',
        'hours_estimated' => 'decimal:2',
        'hours_worked' => 'decimal:2',
        'technologies' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constantes para status
    public const STATUS_PLANNING = 'planning';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    // Constantes para prioridade
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    // Constantes para tipo
    public const TYPE_DEVELOPMENT = 'development';
    public const TYPE_MAINTENANCE = 'maintenance';
    public const TYPE_SUPPORT = 'support';
    public const TYPE_CONSULTATION = 'consultation';

    // Relacionamentos
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_PLANNING,
            self::STATUS_IN_PROGRESS
        ]);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('deadline', '<', now())
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopePersonal(Builder $query): Builder
    {
        return $query->whereNull('client_id');
    }

    public function scopeWithClient(Builder $query): Builder
    {
        return $query->whereNotNull('client_id');
    }

    // Métodos auxiliares
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_PLANNING, self::STATUS_IN_PROGRESS]);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isOverdue(): bool
    {
        return $this->deadline < now() && !$this->isCompleted();
    }

    public function isPersonal(): bool
    {
        return is_null($this->client_id);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PLANNING => 'Planejamento',
            self::STATUS_IN_PROGRESS => 'Em Andamento',
            self::STATUS_ON_HOLD => 'Pausado',
            self::STATUS_COMPLETED => 'Concluído',
            self::STATUS_CANCELLED => 'Cancelado',
            default => 'Desconhecido'
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Baixa',
            self::PRIORITY_MEDIUM => 'Média',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
            default => 'Média'
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_DEVELOPMENT => 'Desenvolvimento',
            self::TYPE_MAINTENANCE => 'Manutenção',
            self::TYPE_SUPPORT => 'Suporte',
            self::TYPE_CONSULTATION => 'Consultoria',
            default => 'Desenvolvimento'
        };
    }

    public function getProgressPercentageAttribute(): string
    {
        // Calcular progresso baseado nas horas trabalhadas vs estimadas
        if (!$this->hours_estimated || $this->hours_estimated == 0) {
            return '0%';
        }
        
        $percentage = min(100, ($this->hours_worked / $this->hours_estimated) * 100);
        return number_format($percentage, 0) . '%';
    }
}