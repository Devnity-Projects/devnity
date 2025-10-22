<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskTimeEntry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TaskTimerController extends Controller
{
    /**
     * Inicia o timer para uma tarefa
     */
    public function start(Task $task): JsonResponse
    {
        $entry = $task->startTimer(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Timer iniciado com sucesso',
            'entry' => [
                'id' => $entry->id,
                'started_at' => $entry->started_at->toIso8601String(),
                'is_running' => true,
            ],
        ]);
    }

    /**
     * Para o timer ativo
     */
    public function stop(Task $task, Request $request): JsonResponse
    {
        $request->validate([
            'description' => 'nullable|string|max:1000',
        ]);

        $entry = $task->stopActiveTimer(Auth::id(), $request->description);

        if (!$entry) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum timer ativo encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Timer parado com sucesso',
            'entry' => [
                'id' => $entry->id,
                'duration' => $entry->formatted_duration,
                'duration_hours' => $entry->duration_hours,
            ],
            'task' => [
                'hours_worked' => $task->fresh()->hours_worked,
            ],
        ]);
    }

    /**
     * Retorna o status do timer atual
     */
    public function status(Task $task): JsonResponse
    {
        $activeEntry = $task->getActiveTimer(Auth::id());

        return response()->json([
            'has_active_timer' => $activeEntry !== null,
            'entry' => $activeEntry ? [
                'id' => $activeEntry->id,
                'started_at' => $activeEntry->started_at->toIso8601String(),
                'elapsed_seconds' => $activeEntry->started_at->diffInSeconds(now()),
                'elapsed_formatted' => $activeEntry->formatted_duration,
            ] : null,
            'total_hours_worked' => $task->hours_worked ?? 0,
        ]);
    }

    /**
     * Lista todas as sessões de timer da tarefa
     */
    public function sessions(Task $task): JsonResponse
    {
        return response()->json([
            'sessions' => $task->getTimerSessions(),
            'total_hours' => $task->hours_worked,
        ]);
    }

    /**
     * Deleta uma sessão de timer
     */
    public function deleteSession(Task $task, TaskTimeEntry $entry): JsonResponse
    {
        // Verificar se a entrada pertence à tarefa
        if ($entry->task_id !== $task->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão não encontrada',
            ], 404);
        }

        // Não permitir deletar timer em execução
        if ($entry->is_running) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível deletar um timer em execução. Pare-o primeiro.',
            ], 400);
        }

        $entry->delete();
        
        // Atualizar horas trabalhadas
        $task->updateWorkedHours();

        return response()->json([
            'success' => true,
            'message' => 'Sessão deletada com sucesso',
            'task' => [
                'hours_worked' => $task->fresh()->hours_worked,
            ],
        ]);
    }
}
