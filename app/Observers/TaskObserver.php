<?php

namespace App\Observers;

use App\Models\Task;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        $this->syncProjectHours($task);
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        // Verificar se as horas foram alteradas
        if ($task->wasChanged(['hours_estimated', 'hours_worked'])) {
            $this->syncProjectHours($task);
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        $this->syncProjectHours($task);
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        $this->syncProjectHours($task);
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        $this->syncProjectHours($task);
    }

    /**
     * Sincroniza as horas do projeto com base nas tarefas
     */
    private function syncProjectHours(Task $task): void
    {
        if ($task->project) {
            $task->project->syncHoursFromTasks();
        }
    }
}
