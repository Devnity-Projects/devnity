<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KanbanController extends Controller
{
    public function index(Request $request): Response
    {
        $projectId = $request->get('project_id');
        $assignedTo = $request->get('assigned_to');
        $search = $request->get('search');
        $priority = $request->get('priority');
        $type = $request->get('type');
        $overdue = $request->boolean('overdue');

        // Base query with eager loading
        $query = Task::with(['project', 'assignedUser'])
            ->when($projectId, fn($q) => $q->byProject($projectId))
            ->when($assignedTo, function($q) use ($assignedTo) {
                // Handle multiple assigned_to values separated by comma
                $assignedToValues = is_string($assignedTo) ? explode(',', $assignedTo) : [$assignedTo];
                $assignedToValues = array_filter($assignedToValues); // Remove empty values
                
                if (empty($assignedToValues)) {
                    return $q;
                }
                
                return $q->where(function($query) use ($assignedToValues) {
                    foreach ($assignedToValues as $value) {
                        if (trim($value) === 'unassigned') {
                            $query->orWhereNull('assigned_to');
                        } else {
                            $query->orWhere('assigned_to', trim($value));
                        }
                    }
                });
            })
            ->when($search, fn($q) => $q->search($search))
            ->when($priority, fn($q) => $q->where('priority', $priority))
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($overdue, fn($q) => $q->overdue())
            ->orderBy('order')
            ->orderBy('created_at', 'desc');

        // Group tasks by status for Kanban columns
        $tasks = $query->get()->groupBy('status');

        // Kanban columns configuration
        $columns = [
            Task::STATUS_TODO => [
                'title' => 'A Fazer',
                'color' => 'blue',
                'tasks' => $tasks->get(Task::STATUS_TODO, collect())
            ],
            Task::STATUS_IN_PROGRESS => [
                'title' => 'Em Progresso',
                'color' => 'yellow',
                'tasks' => $tasks->get(Task::STATUS_IN_PROGRESS, collect())
            ],
            Task::STATUS_REVIEW => [
                'title' => 'Em Revisão',
                'color' => 'purple',
                'tasks' => $tasks->get(Task::STATUS_REVIEW, collect())
            ],
            Task::STATUS_COMPLETED => [
                'title' => 'Concluído',
                'color' => 'green',
                'tasks' => $tasks->get(Task::STATUS_COMPLETED, collect())
            ],
        ];

        // Statistics
        $stats = [
            'total' => $tasks->flatten()->count(),
            'todo' => $tasks->get(Task::STATUS_TODO, collect())->count(),
            'in_progress' => $tasks->get(Task::STATUS_IN_PROGRESS, collect())->count(),
            'review' => $tasks->get(Task::STATUS_REVIEW, collect())->count(),
            'completed' => $tasks->get(Task::STATUS_COMPLETED, collect())->count(),
            'overdue' => $tasks->flatten()->where('is_overdue', true)->count(),
        ];

        return Inertia::render('Tasks/Kanban', [
            'columns' => $columns,
            'stats' => $stats,
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'project_id' => $projectId,
                'assigned_to' => $assignedTo,
                'search' => $search,
                'priority' => $priority,
                'type' => $type,
                'overdue' => $overdue,
            ],
            'statuses' => [
                Task::STATUS_TODO => 'A Fazer',
                Task::STATUS_IN_PROGRESS => 'Em Progresso',
                Task::STATUS_REVIEW => 'Em Revisão',
                Task::STATUS_COMPLETED => 'Concluído',
            ],
            'priorities' => [
                Task::PRIORITY_LOW => 'Baixa',
                Task::PRIORITY_MEDIUM => 'Média',
                Task::PRIORITY_HIGH => 'Alta',
                Task::PRIORITY_URGENT => 'Urgente',
            ],
            'types' => [
                Task::TYPE_FEATURE => 'Funcionalidade',
                Task::TYPE_BUG => 'Bug',
                Task::TYPE_ENHANCEMENT => 'Melhoria',
                Task::TYPE_DOCUMENTATION => 'Documentação',
                Task::TYPE_TESTING => 'Teste',
            ]
        ]);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', [
                Task::STATUS_TODO,
                Task::STATUS_IN_PROGRESS,
                Task::STATUS_REVIEW,
                Task::STATUS_COMPLETED,
            ]),
            'order' => 'nullable|integer|min:0'
        ]);

        $oldStatus = $task->status;
        $newStatus = $request->input('status');

        // Update status and order
        $task->update([
            'status' => $newStatus,
            'order' => $request->input('order', 0),
        ]);

        // Handle status transitions
        if ($oldStatus !== $newStatus) {
            switch ($newStatus) {
                case Task::STATUS_IN_PROGRESS:
                    if (!$task->started_at) {
                        $task->update(['started_at' => now()]);
                    }
                    break;
                case Task::STATUS_COMPLETED:
                    $task->update(['completed_at' => now()]);
                    break;
                case Task::STATUS_TODO:
                    // If moved back to todo, reset timestamps
                    $task->update([
                        'started_at' => null,
                        'completed_at' => null,
                    ]);
                    break;
            }
        }

        // Just return success status for Inertia
        return back();
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.status' => 'required|string',
            'tasks.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->input('tasks') as $taskData) {
            Task::where('id', $taskData['id'])->update([
                'status' => $taskData['status'],
                'order' => $taskData['order'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ordem das tarefas atualizada com sucesso!'
        ]);
    }

    public function quickCreate(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'status' => 'required|in:' . implode(',', [
                Task::STATUS_TODO,
                Task::STATUS_IN_PROGRESS,
                Task::STATUS_REVIEW,
                Task::STATUS_COMPLETED,
            ]),
            'priority' => 'nullable|in:' . implode(',', [
                Task::PRIORITY_LOW,
                Task::PRIORITY_MEDIUM,
                Task::PRIORITY_HIGH,
                Task::PRIORITY_URGENT,
            ]),
        ]);

        // Get next order for the column
        $maxOrder = Task::where('status', $request->input('status'))->max('order') ?? 0;

        $task = Task::create([
            'title' => $request->input('title'),
            'project_id' => $request->input('project_id'),
            'status' => $request->input('status'),
            'priority' => $request->input('priority', Task::PRIORITY_MEDIUM),
            'type' => Task::TYPE_FEATURE,
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'task' => $task->load(['project', 'assignedUser']),
            'message' => 'Tarefa criada com sucesso!'
        ]);
    }

    public function moveTask(Request $request, Task $task)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', [
                Task::STATUS_TODO,
                Task::STATUS_IN_PROGRESS,
                Task::STATUS_REVIEW,
                Task::STATUS_COMPLETED,
            ]),
            'order' => 'nullable|integer|min:0',
        ]);

        $newStatus = $request->input('status');
        $newOrder = $request->input('order', 0);

        // If status is changing, update the order in the new column
        if ($task->status !== $newStatus) {
            // Get max order in the target column
            $maxOrder = Task::where('status', $newStatus)->max('order') ?? 0;
            $task->order = $maxOrder + 1;
        } else {
            // Same column, update order
            $task->order = $newOrder;
        }

        $task->status = $newStatus;
        $task->save();

        // Log the activity
        $task->activities()->create([
            'user_id' => auth()->id(),
            'type' => 'status_changed',
            'description' => "Status alterado para: {$task->status_label}",
            'changes' => [
                'status' => [
                    'old' => $task->getOriginal('status'),
                    'new' => $task->status
                ]
            ]
        ]);

        return response()->json([
            'success' => true,
            'task' => $task->fresh(['project', 'assignedUser']),
            'message' => 'Tarefa movida com sucesso!'
        ]);
    }
}
