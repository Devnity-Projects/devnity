<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing projects and users
        $projects = Project::all();
        $users = User::all();

        if ($projects->isEmpty()) {
            $this->command->warn('No projects found. Please run ProjectSeeder first.');
            return;
        }

        $taskData = [
            // TO DO Tasks
            [
                'title' => 'Implementar autenticação social com Google',
                'description' => 'Adicionar login e registro usando conta do Google para melhorar a experiência do usuário.',
                'status' => Task::STATUS_TODO,
                'priority' => Task::PRIORITY_HIGH,
                'type' => Task::TYPE_FEATURE,
                'hours_estimated' => 8.0,
                'due_date' => now()->addDays(5),
                'labels' => ['autenticação', 'google', 'frontend'],
                'order' => 1,
            ],
            [
                'title' => 'Configurar ambiente de testes automatizados',
                'description' => 'Configurar PHPUnit, Pest e testes de frontend para garantir qualidade do código.',
                'status' => Task::STATUS_TODO,
                'priority' => Task::PRIORITY_MEDIUM,
                'type' => Task::TYPE_TESTING,
                'hours_estimated' => 12.0,
                'due_date' => now()->addDays(10),
                'labels' => ['testing', 'phpunit', 'pest'],
                'order' => 2,
            ],
            [
                'title' => 'Criar documentação da API',
                'description' => 'Documentar todos os endpoints da API usando OpenAPI/Swagger.',
                'status' => Task::STATUS_TODO,
                'priority' => Task::PRIORITY_LOW,
                'type' => Task::TYPE_DOCUMENTATION,
                'hours_estimated' => 6.0,
                'due_date' => now()->addDays(15),
                'labels' => ['documentação', 'api', 'swagger'],
                'order' => 3,
            ],

            // IN PROGRESS Tasks
            [
                'title' => 'Implementar sistema de notificações',
                'description' => 'Criar sistema completo de notificações in-app e por email para eventos importantes.',
                'status' => Task::STATUS_IN_PROGRESS,
                'priority' => Task::PRIORITY_HIGH,
                'type' => Task::TYPE_FEATURE,
                'hours_estimated' => 16.0,
                'hours_worked' => 8.5,
                'due_date' => now()->addDays(3),
                'labels' => ['notificações', 'email', 'realtime'],
                'order' => 1,
                'started_at' => now()->subDays(2),
            ],
            [
                'title' => 'Otimizar performance do dashboard',
                'description' => 'Melhorar tempo de carregamento do dashboard principal através de otimizações de query e cache.',
                'status' => Task::STATUS_IN_PROGRESS,
                'priority' => Task::PRIORITY_MEDIUM,
                'type' => Task::TYPE_ENHANCEMENT,
                'hours_estimated' => 10.0,
                'hours_worked' => 4.0,
                'due_date' => now()->addDays(7),
                'labels' => ['performance', 'cache', 'dashboard'],
                'order' => 2,
                'started_at' => now()->subDays(1),
            ],

            // REVIEW Tasks
            [
                'title' => 'Corrigir bug no filtro de projetos',
                'description' => 'Resolver problema onde filtros não são aplicados corretamente na listagem de projetos.',
                'status' => Task::STATUS_REVIEW,
                'priority' => Task::PRIORITY_URGENT,
                'type' => Task::TYPE_BUG,
                'hours_estimated' => 4.0,
                'hours_worked' => 3.5,
                'due_date' => now()->subDays(1), // Overdue
                'labels' => ['bug', 'filtros', 'projetos'],
                'order' => 1,
                'started_at' => now()->subDays(3),
            ],
            [
                'title' => 'Implementar paginação avançada',
                'description' => 'Adicionar paginação com busca e filtros mantendo estado na URL.',
                'status' => Task::STATUS_REVIEW,
                'priority' => Task::PRIORITY_MEDIUM,
                'type' => Task::TYPE_ENHANCEMENT,
                'hours_estimated' => 6.0,
                'hours_worked' => 6.0,
                'due_date' => now()->addDays(2),
                'labels' => ['paginação', 'frontend', 'ux'],
                'order' => 2,
                'started_at' => now()->subDays(4),
            ],

            // COMPLETED Tasks
            [
                'title' => 'Configurar CI/CD pipeline',
                'description' => 'Implementar pipeline completo de integração e deploy contínuo usando GitHub Actions.',
                'status' => Task::STATUS_COMPLETED,
                'priority' => Task::PRIORITY_HIGH,
                'type' => Task::TYPE_FEATURE,
                'hours_estimated' => 12.0,
                'hours_worked' => 14.0,
                'due_date' => now()->subDays(5),
                'labels' => ['cicd', 'deploy', 'github-actions'],
                'order' => 1,
                'started_at' => now()->subDays(10),
                'completed_at' => now()->subDays(2),
            ],
            [
                'title' => 'Implementar tema dark mode',
                'description' => 'Adicionar suporte completo ao tema escuro em toda a aplicação.',
                'status' => Task::STATUS_COMPLETED,
                'priority' => Task::PRIORITY_MEDIUM,
                'type' => Task::TYPE_FEATURE,
                'hours_estimated' => 8.0,
                'hours_worked' => 7.5,
                'due_date' => now()->subDays(10),
                'labels' => ['ui', 'dark-mode', 'acessibilidade'],
                'order' => 2,
                'started_at' => now()->subDays(12),
                'completed_at' => now()->subDays(8),
            ],
            [
                'title' => 'Atualizar dependências do projeto',
                'description' => 'Atualizar todas as dependências para suas versões mais recentes.',
                'status' => Task::STATUS_COMPLETED,
                'priority' => Task::PRIORITY_LOW,
                'type' => Task::TYPE_ENHANCEMENT,
                'hours_estimated' => 3.0,
                'hours_worked' => 2.5,
                'due_date' => now()->subDays(15),
                'labels' => ['manutenção', 'dependências'],
                'order' => 3,
                'started_at' => now()->subDays(16),
                'completed_at' => now()->subDays(14),
            ],
        ];

        foreach ($taskData as $index => $data) {
            Task::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'project_id' => $projects->random()->id,
                'assigned_to' => $users->isNotEmpty() ? $users->random()->id : null,
                'status' => $data['status'],
                'priority' => $data['priority'],
                'type' => $data['type'],
                'hours_estimated' => $data['hours_estimated'],
                'hours_worked' => $data['hours_worked'] ?? 0,
                'due_date' => $data['due_date'],
                'order' => $data['order'],
                'labels' => $data['labels'],
                'started_at' => $data['started_at'] ?? null,
                'completed_at' => $data['completed_at'] ?? null,
            ]);
        }

        $this->command->info('Created ' . count($taskData) . ' sample tasks successfully!');
    }
}
