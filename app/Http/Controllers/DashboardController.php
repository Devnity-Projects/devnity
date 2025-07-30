<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Estatísticas gerais
        $stats = [
            'clients' => Client::count(),
            'projects' => Project::count(),
            'tasks' => Task::count(),
            'active_projects' => Project::active()->count(),
            'completed_projects' => Project::completed()->count(),
            'overdue_tasks' => Task::overdue()->count(),
            'tasks_in_progress' => Task::byStatus(Task::STATUS_IN_PROGRESS)->count(),
        ];

        // Projetos recentes
        $recentProjects = Project::with('client')
            ->latest()
            ->limit(5)
            ->get();

        // Tarefas urgentes
        $urgentTasks = Task::with(['project', 'assignedUser'])
            ->where('priority', Task::PRIORITY_URGENT)
            ->active()
            ->limit(5)
            ->get();

        // Atividade recente (últimas ações)
        $recentActivity = [
            // Para futuras implementações de logs de atividade
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'urgentTasks' => $urgentTasks,
            'recentActivity' => $recentActivity,
        ]);
    }
}
