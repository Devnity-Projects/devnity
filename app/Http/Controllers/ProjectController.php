<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Project::with(['client', 'tasks']);

        // Filtros
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Ordenação
        $orderBy = $request->get('order_by', 'created_at');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($orderBy, $direction);

        $projects = $query->paginate(12);
        $clients = Client::orderBy('name')->get();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'clients' => $clients,
            'filters' => $request->only(['status', 'priority', 'client_id', 'search', 'order_by', 'direction']),
            'statuses' => [
                Project::STATUS_PLANNING => 'Planejamento',
                Project::STATUS_IN_PROGRESS => 'Em Progresso',
                Project::STATUS_COMPLETED => 'Concluído',
                Project::STATUS_CANCELLED => 'Cancelado',
                Project::STATUS_ON_HOLD => 'Em Espera',
            ],
            'priorities' => [
                Project::PRIORITY_LOW => 'Baixa',
                Project::PRIORITY_MEDIUM => 'Média',
                Project::PRIORITY_HIGH => 'Alta',
                Project::PRIORITY_URGENT => 'Urgente',
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::orderBy('name')->get();

        return Inertia::render('Projects/Create', [
            'clients' => $clients,
            'types' => [
                Project::TYPE_DEVELOPMENT => 'Desenvolvimento',
                Project::TYPE_MAINTENANCE => 'Manutenção',
                Project::TYPE_SUPPORT => 'Suporte',
                Project::TYPE_CONSULTATION => 'Consultoria',
            ],
            'priorities' => [
                Project::PRIORITY_LOW => 'Baixa',
                Project::PRIORITY_MEDIUM => 'Média',
                Project::PRIORITY_HIGH => 'Alta',
                Project::PRIORITY_URGENT => 'Urgente',
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|in:' . implode(',', [
                Project::STATUS_PLANNING,
                Project::STATUS_IN_PROGRESS,
                Project::STATUS_COMPLETED,
                Project::STATUS_CANCELLED,
                Project::STATUS_ON_HOLD,
            ]),
            'priority' => 'required|in:' . implode(',', [
                Project::PRIORITY_LOW,
                Project::PRIORITY_MEDIUM,
                Project::PRIORITY_HIGH,
                Project::PRIORITY_URGENT,
            ]),
            'type' => 'required|in:' . implode(',', [
                Project::TYPE_DEVELOPMENT,
                Project::TYPE_MAINTENANCE,
                Project::TYPE_SUPPORT,
                Project::TYPE_CONSULTATION,
            ]),
            'budget' => 'nullable|numeric|min:0',
            'hours_estimated' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'deadline' => 'nullable|date',
            'technologies' => 'nullable|array',
            'repository_url' => 'nullable|url',
            'demo_url' => 'nullable|url',
            'production_url' => 'nullable|url',
            'notes' => 'nullable|string',
        ]);

        Project::create($validated);

        return redirect()->route('projects.index')->with('success', 'Projeto criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $project->load(['client', 'tasks.assignedUser']);

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'taskStats' => [
                'total' => $project->tasks->count(),
                'completed' => $project->tasks->where('status', 'completed')->count(),
                'in_progress' => $project->tasks->where('status', 'in_progress')->count(),
                'overdue' => $project->tasks->filter(fn($task) => $task->is_overdue)->count(),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $clients = Client::orderBy('name')->get();

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'clients' => $clients,
            'types' => [
                Project::TYPE_DEVELOPMENT => 'Desenvolvimento',
                Project::TYPE_MAINTENANCE => 'Manutenção',
                Project::TYPE_SUPPORT => 'Suporte',
                Project::TYPE_CONSULTATION => 'Consultoria',
            ],
            'priorities' => [
                Project::PRIORITY_LOW => 'Baixa',
                Project::PRIORITY_MEDIUM => 'Média',
                Project::PRIORITY_HIGH => 'Alta',
                Project::PRIORITY_URGENT => 'Urgente',
            ],
            'statuses' => [
                Project::STATUS_PLANNING => 'Planejamento',
                Project::STATUS_IN_PROGRESS => 'Em Progresso',
                Project::STATUS_COMPLETED => 'Concluído',
                Project::STATUS_CANCELLED => 'Cancelado',
                Project::STATUS_ON_HOLD => 'Em Espera',
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|in:' . implode(',', [
                Project::STATUS_PLANNING,
                Project::STATUS_IN_PROGRESS,
                Project::STATUS_COMPLETED,
                Project::STATUS_CANCELLED,
                Project::STATUS_ON_HOLD,
            ]),
            'priority' => 'required|in:' . implode(',', [
                Project::PRIORITY_LOW,
                Project::PRIORITY_MEDIUM,
                Project::PRIORITY_HIGH,
                Project::PRIORITY_URGENT,
            ]),
            'type' => 'required|in:' . implode(',', [
                Project::TYPE_DEVELOPMENT,
                Project::TYPE_MAINTENANCE,
                Project::TYPE_SUPPORT,
                Project::TYPE_CONSULTATION,
            ]),
            'budget' => 'nullable|numeric|min:0',
            'hours_estimated' => 'nullable|numeric|min:0',
            'hours_worked' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'deadline' => 'nullable|date',
            'technologies' => 'nullable|array',
            'repository_url' => 'nullable|url',
            'demo_url' => 'nullable|url',
            'production_url' => 'nullable|url',
            'notes' => 'nullable|string',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)->with('success', 'Projeto atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Projeto excluído com sucesso!');
    }
}
