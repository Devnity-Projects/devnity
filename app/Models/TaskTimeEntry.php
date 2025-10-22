<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TaskTimeEntry extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'started_at',
        'ended_at',
        'duration_hours',
        'description',
        'is_running',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_hours' => 'decimal:2',
        'is_running' => 'boolean',
    ];

    // Relationships
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Methods
    /**
     * Calcula a duração em horas baseado nos timestamps
     */
    public function calculateDuration(): float
    {
        if (!$this->ended_at) {
            // Se ainda está rodando, calcula até agora
            return round($this->started_at->diffInMinutes(now()) / 60, 2);
        }
        
        return round($this->started_at->diffInMinutes($this->ended_at) / 60, 2);
    }

    /**
     * Para o timer e calcula a duração final
     */
    public function stop(?string $description = null): void
    {
        $duration = $this->calculateDuration();
        
        $this->update([
            'ended_at' => now(),
            'is_running' => false,
            'duration_hours' => $duration,
            'description' => $description ?? $this->description,
        ]);

        // Atualizar horas trabalhadas da tarefa
        $this->task->updateWorkedHours();
    }

    /**
     * Retorna duração formatada
     */
    public function getFormattedDurationAttribute(): string
    {
        $duration = $this->is_running ? $this->calculateDuration() : $this->duration_hours;
        $hours = floor($duration);
        $minutes = round(($duration - $hours) * 60);
        
        return sprintf('%02d:%02d', $hours, $minutes);
    }
}
