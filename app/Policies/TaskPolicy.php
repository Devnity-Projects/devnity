<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Usuário pode ver a tarefa se for o proprietário do projeto
        return $task->project && $task->project->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Usuário pode editar a tarefa se for o proprietário do projeto
        return $task->project && $task->project->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Usuário pode deletar a tarefa se for o proprietário do projeto
        return $task->project && $task->project->user_id === $user->id;
    }

    /**
     * Determine whether the user can add attachments to the task.
     */
    public function addAttachment(User $user, Task $task): bool
    {
        return $task->project && $task->project->user_id === $user->id;
    }

    /**
     * Determine whether the user can add comments to the task.
     */
    public function addComment(User $user, Task $task): bool
    {
        return $task->project && $task->project->user_id === $user->id;
    }

    /**
     * Determine whether the user can manage checklist items.
     */
    public function manageChecklist(User $user, Task $task): bool
    {
        return $task->project && $task->project->user_id === $user->id;
    }
}