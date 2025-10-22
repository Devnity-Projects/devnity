<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'tasks' => [],
                'projects' => [],
                'clients' => []
            ]);
        }

        // Search tasks
        $tasks = Task::with(['project.client', 'assignedUser'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'priority' => $task->priority,
                    'priority_label' => $task->priority_label,
                    'status' => $task->status,
                    'status_label' => $task->status_label,
                    'project' => [
                        'id' => $task->project->id,
                        'name' => $task->project->name,
                    ],
                    'assigned_user' => $task->assignedUser ? [
                        'id' => $task->assignedUser->id,
                        'name' => $task->assignedUser->name,
                    ] : null,
                ];
            });

        // Search projects - busca abrangente em todos os campos
        $projects = Project::with('client')
            ->where(function ($q) use ($query) {
                // Buscar no nome do projeto
                $q->where('name', 'LIKE', "%{$query}%")
                  // Buscar na descrição
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  // Buscar nas notas
                  ->orWhere('notes', 'LIKE', "%{$query}%")
                  // Buscar nas URLs
                  ->orWhere('repository_url', 'LIKE', "%{$query}%")
                  ->orWhere('demo_url', 'LIKE', "%{$query}%")
                  ->orWhere('production_url', 'LIKE', "%{$query}%")
                  // Buscar nas tecnologias (campo JSON)
                  ->orWhereJsonContains('technologies', $query)
                  // Buscar parcialmente nas tecnologias usando LIKE no JSON
                  ->orWhereRaw('LOWER(JSON_EXTRACT(technologies, "$")) LIKE ?', ['%' . strtolower($query) . '%'])
                  // Buscar no nome do cliente
                  ->orWhereHas('client', function ($clientQuery) use ($query) {
                      $clientQuery->where('name', 'LIKE', "%{$query}%");
                  });
            })
            ->limit(5)
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'client' => $project->client ? [
                        'id' => $project->client->id,
                        'name' => $project->client->name,
                    ] : null,
                ];
            });

        // Search clients
        $clients = Client::where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('responsible', 'LIKE', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                ];
            });

        return response()->json([
            'tasks' => $tasks,
            'projects' => $projects,
            'clients' => $clients
        ]);
    }
}
