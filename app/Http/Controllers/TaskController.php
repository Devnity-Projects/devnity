<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Task::with(['project.client', 'assignedUser']);

        // Filtros
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('project_id') && $request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('assigned_to') && $request->assigned_to) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        if ($request->has('overdue') && $request->overdue) {
            $query->overdue();
        }

        // Ordenação
        $orderBy = $request->get('order_by', 'created_at');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($orderBy, $direction);

        $tasks = $query->paginate(15);
        $projects = Project::with('client')->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        // Estatísticas das tarefas
        $stats = [
            'total' => Task::count(),
            'todo' => Task::where('status', Task::STATUS_TODO)->count(),
            'in_progress' => Task::where('status', Task::STATUS_IN_PROGRESS)->count(),
            'review' => Task::where('status', Task::STATUS_REVIEW)->count(),
            'completed' => Task::where('status', Task::STATUS_COMPLETED)->count(),
            'overdue' => Task::where('due_date', '<', now())
                          ->whereNotIn('status', [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED])
                          ->count(),
        ];

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'projects' => $projects,
            'users' => $users,
            'stats' => $stats,
            'filters' => $request->only(['status', 'priority', 'project_id', 'assigned_to', 'search', 'order_by', 'direction', 'overdue']),
            'statuses' => [
                Task::STATUS_TODO => 'A Fazer',
                Task::STATUS_IN_PROGRESS => 'Em Progresso',
                Task::STATUS_REVIEW => 'Em Revisão',
                Task::STATUS_COMPLETED => 'Concluído',
                Task::STATUS_CANCELLED => 'Cancelado',
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
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $projects = Project::with('client')->orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $selectedProject = $request->has('project_id') ? 
            Project::find($request->project_id) : null;

        return Inertia::render('Tasks/Create', [
            'projects' => $projects,
            'users' => $users,
            'selectedProject' => $selectedProject,
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
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:' . implode(',', [
                Task::STATUS_TODO,
                Task::STATUS_IN_PROGRESS,
                Task::STATUS_REVIEW,
                Task::STATUS_COMPLETED,
                Task::STATUS_CANCELLED,
            ]),
            'priority' => 'required|in:' . implode(',', [
                Task::PRIORITY_LOW,
                Task::PRIORITY_MEDIUM,
                Task::PRIORITY_HIGH,
                Task::PRIORITY_URGENT,
            ]),
            'type' => 'required|in:' . implode(',', [
                Task::TYPE_FEATURE,
                Task::TYPE_BUG,
                Task::TYPE_ENHANCEMENT,
                Task::TYPE_DOCUMENTATION,
                Task::TYPE_TESTING,
            ]),
            'hours_estimated' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'labels' => 'nullable|array',
            'acceptance_criteria' => 'nullable|string',
        ]);

        Task::create($validated);

        return redirect()->route('tasks.index')->with('success', 'Tarefa criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $task->load([
            'project.client',
            'assignedUser'
        ]);

        return Inertia::render('Tasks/Show', [
            'task' => $task,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $projects = Project::with('client')->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return Inertia::render('Tasks/Edit', [
            'task' => $task,
            'projects' => $projects,
            'users' => $users,
            'statuses' => [
                Task::STATUS_TODO => 'A Fazer',
                Task::STATUS_IN_PROGRESS => 'Em Progresso',
                Task::STATUS_REVIEW => 'Em Revisão',
                Task::STATUS_COMPLETED => 'Concluído',
                Task::STATUS_CANCELLED => 'Cancelado',
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
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:' . implode(',', [
                Task::STATUS_TODO,
                Task::STATUS_IN_PROGRESS,
                Task::STATUS_REVIEW,
                Task::STATUS_COMPLETED,
                Task::STATUS_CANCELLED,
            ]),
            'priority' => 'required|in:' . implode(',', [
                Task::PRIORITY_LOW,
                Task::PRIORITY_MEDIUM,
                Task::PRIORITY_HIGH,
                Task::PRIORITY_URGENT,
            ]),
            'type' => 'required|in:' . implode(',', [
                Task::TYPE_FEATURE,
                Task::TYPE_BUG,
                Task::TYPE_ENHANCEMENT,
                Task::TYPE_DOCUMENTATION,
                Task::TYPE_TESTING,
            ]),
            'hours_estimated' => 'nullable|numeric|min:0',
            'hours_worked' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'labels' => 'nullable|array',
            'acceptance_criteria' => 'nullable|string',
        ]);

        $task->update($validated);

        return redirect()->route('tasks.show', $task)->with('success', 'Tarefa atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Tarefa excluída com sucesso!');
    }

    /**
     * Update task status quickly
     */
    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', [
                Task::STATUS_TODO,
                Task::STATUS_IN_PROGRESS,
                Task::STATUS_REVIEW,
                Task::STATUS_COMPLETED,
                Task::STATUS_CANCELLED,
            ]),
        ]);

        $task->update($validated);

        return back()->with('success', 'Status da tarefa atualizado!');
    }

    /**
     * Show kanban board
     */
    public function kanban(Request $request)
    {
        $query = Task::with(['project.client', 'assignedUser']);

        if ($request->has('project_id') && $request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('assigned_to') && $request->assigned_to) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        $tasks = $query->get()->groupBy('status');

        $projects = Project::with('client')->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return Inertia::render('Tasks/Kanban', [
            'tasksByStatus' => $tasks,
            'projects' => $projects,
            'users' => $users,
            'filters' => $request->only(['project_id', 'assigned_to']),
            'statuses' => [
                Task::STATUS_TODO => 'A Fazer',
                Task::STATUS_IN_PROGRESS => 'Em Progresso',
                Task::STATUS_REVIEW => 'Em Revisão',
                Task::STATUS_COMPLETED => 'Concluído',
                Task::STATUS_CANCELLED => 'Cancelado',
            ],
        ]);
    }
}
