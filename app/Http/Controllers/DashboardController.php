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
        try {
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
            $urgentTasks = Task::with('project')
                ->where('priority', Task::PRIORITY_URGENT)
                ->active()
                ->limit(5)
                ->get();

        } catch (\Exception $e) {
            // Em caso de erro, retornar dados padrão
            $stats = [
                'clients' => Client::count(),
                'projects' => 0,
                'tasks' => 0,
                'active_projects' => 0,
                'completed_projects' => 0,
                'overdue_tasks' => 0,
                'tasks_in_progress' => 0,
            ];

            $recentProjects = collect();
            $urgentTasks = collect();
        }

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
