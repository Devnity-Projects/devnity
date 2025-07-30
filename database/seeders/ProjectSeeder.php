<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Client;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        
        if ($clients->isEmpty()) {
            return; // Não criar projetos se não há clientes
        }

        $projects = [
            [
                'name' => 'Website Institucional',
                'description' => 'Desenvolvimento de website institucional responsivo com painel administrativo.',
                'client_id' => $clients->first()->id,
                'status' => 'in_progress',
                'priority' => 'high',
                'start_date' => now()->subDays(15),
                'deadline' => now()->addDays(30),
                'budget' => 15000.00,
                'hours_estimated' => 120.00,
                'hours_worked' => 45.50,
                'notes' => 'Projeto em andamento, aguardando aprovação do layout.',
            ],
            [
                'name' => 'Sistema de Gestão',
                'description' => 'Sistema completo para gestão de estoque e vendas.',
                'client_id' => $clients->count() > 1 ? $clients->skip(1)->first()->id : $clients->first()->id,
                'status' => 'planning',
                'priority' => 'medium',
                'start_date' => now()->addDays(5),
                'deadline' => now()->addDays(90),
                'budget' => 35000.00,
                'hours_estimated' => 280.00,
                'hours_worked' => 0.00,
                'notes' => 'Projeto em fase de planejamento e levantamento de requisitos.',
            ],
            [
                'name' => 'App Mobile',
                'description' => 'Aplicativo mobile para iOS e Android.',
                'client_id' => $clients->count() > 2 ? $clients->skip(2)->first()->id : $clients->first()->id,
                'status' => 'completed',
                'priority' => 'low',
                'start_date' => now()->subDays(120),
                'deadline' => now()->subDays(10),
                'budget' => 25000.00,
                'hours_estimated' => 200.00,
                'hours_worked' => 185.75,
                'notes' => 'Projeto concluído com sucesso. Cliente muito satisfeito.',
            ],
        ];

        foreach ($projects as $projectData) {
            Project::create($projectData);
        }
    }
}
