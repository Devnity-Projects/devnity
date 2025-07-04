<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Pegue os totais reais do banco
        $stats = [
            'clients'   => Client::count(),
            // 'projects'  => Project::count(),
            // 'tasks'     => Task::count(),
            // 'tickets'   => Ticket::count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }
}
