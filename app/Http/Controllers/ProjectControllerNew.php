<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProjectController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Project::with(['client', 'tasks']);

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        // Client filter
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $validSortColumns = ['name', 'status', 'priority', 'type', 'budget', 'deadline', 'created_at'];
        if (in_array($sortBy, $validSortColumns)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $projects = $query->paginate($request->get('per_page', 12))
            ->withQueryString();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'priority' => $request->priority,
                'client_id' => $request->client_id,
                'type' => $request->type,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ],
            'stats' => [
                'total' => Project::count(),
                'active' => Project::active()->count(),
                'completed' => Project::completed()->count(),
                'overdue' => Project::overdue()->count(),
                'planning' => Project::byStatus(Project::STATUS_PLANNING)->count(),
                'in_progress' => Project::byStatus(Project::STATUS_IN_PROGRESS)->count(),
            ],
            'statuses' => [
                Project::STATUS_PLANNING => 'Planejamento',
                Project::STATUS_IN_PROGRESS => 'Em Andamento',
                Project::STATUS_ON_HOLD => 'Pausado',
                Project::STATUS_COMPLETED => 'Concluído',
                Project::STATUS_CANCELLED => 'Cancelado',
            ],
            'priorities' => [
                Project::PRIORITY_LOW => 'Baixa',
                Project::PRIORITY_MEDIUM => 'Média',
                Project::PRIORITY_HIGH => 'Alta',
                Project::PRIORITY_URGENT => 'Urgente',
            ],
            'types' => [
                Project::TYPE_DEVELOPMENT => 'Desenvolvimento',
                Project::TYPE_MAINTENANCE => 'Manutenção',
                Project::TYPE_SUPPORT => 'Suporte',
                Project::TYPE_CONSULTATION => 'Consultoria',
            ],
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Projects/Create', [
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'statuses' => [
                Project::STATUS_PLANNING => 'Planejamento',
                Project::STATUS_IN_PROGRESS => 'Em Andamento',
                Project::STATUS_ON_HOLD => 'Pausado',
                Project::STATUS_COMPLETED => 'Concluído',
                Project::STATUS_CANCELLED => 'Cancelado',
            ],
            'priorities' => [
                Project::PRIORITY_LOW => 'Baixa',
                Project::PRIORITY_MEDIUM => 'Média',
                Project::PRIORITY_HIGH => 'Alta',
                Project::PRIORITY_URGENT => 'Urgente',
            ],
            'types' => [
                Project::TYPE_DEVELOPMENT => 'Desenvolvimento',
                Project::TYPE_MAINTENANCE => 'Manutenção',
                Project::TYPE_SUPPORT => 'Suporte',
                Project::TYPE_CONSULTATION => 'Consultoria',
            ],
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $project = Project::create($request->validated());

        return Redirect::route('projects.show', $project)
            ->with('success', 'Projeto criado com sucesso!');
    }

    public function show(Project $project): InertiaResponse
    {
        $project->load(['client', 'tasks']);

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'taskStats' => [
                'total' => $project->tasks->count(),
                'completed' => $project->tasks->where('status', 'completed')->count(),
                'in_progress' => $project->tasks->where('status', 'in_progress')->count(),
                'overdue' => $project->tasks->filter(fn($task) => $task->is_overdue ?? false)->count(),
            ],
        ]);
    }

    public function edit(Project $project): InertiaResponse
    {
        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'statuses' => [
                Project::STATUS_PLANNING => 'Planejamento',
                Project::STATUS_IN_PROGRESS => 'Em Andamento',
                Project::STATUS_ON_HOLD => 'Pausado',
                Project::STATUS_COMPLETED => 'Concluído',
                Project::STATUS_CANCELLED => 'Cancelado',
            ],
            'priorities' => [
                Project::PRIORITY_LOW => 'Baixa',
                Project::PRIORITY_MEDIUM => 'Média',
                Project::PRIORITY_HIGH => 'Alta',
                Project::PRIORITY_URGENT => 'Urgente',
            ],
            'types' => [
                Project::TYPE_DEVELOPMENT => 'Desenvolvimento',
                Project::TYPE_MAINTENANCE => 'Manutenção',
                Project::TYPE_SUPPORT => 'Suporte',
                Project::TYPE_CONSULTATION => 'Consultoria',
            ],
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return Redirect::route('projects.show', $project)
            ->with('success', 'Projeto atualizado com sucesso!');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return Redirect::route('projects.index')
            ->with('success', 'Projeto excluído com sucesso!');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:projects,id',
        ]);

        Project::whereIn('id', $request->ids)->delete();

        return Redirect::route('projects.index')
            ->with('success', 'Projetos excluídos com sucesso!');
    }

    public function updateStatus(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', [
                Project::STATUS_PLANNING,
                Project::STATUS_IN_PROGRESS,
                Project::STATUS_ON_HOLD,
                Project::STATUS_COMPLETED,
                Project::STATUS_CANCELLED,
            ]),
        ]);

        $project->update(['status' => $request->status]);

        return back()->with('success', 'Status do projeto atualizado com sucesso!');
    }
}
