<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;

class SyncProjectHours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:sync-hours';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza as horas estimadas e trabalhadas dos projetos com base nas suas tarefas';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando sincronização de horas dos projetos...');
        
        $projects = Project::with('tasks')->get();
        $bar = $this->output->createProgressBar($projects->count());
        $bar->start();

        $synced = 0;
        $errors = 0;

        foreach ($projects as $project) {
            try {
                $oldEstimated = $project->hours_estimated;
                $oldWorked = $project->hours_worked;

                $project->syncHoursFromTasks();

                $newEstimated = $project->hours_estimated;
                $newWorked = $project->hours_worked;

                if ($oldEstimated != $newEstimated || $oldWorked != $newWorked) {
                    $synced++;
                    $this->newLine();
                    $this->comment("Projeto #{$project->id} - {$project->name}:");
                    $this->line("  Estimadas: {$oldEstimated}h → {$newEstimated}h");
                    $this->line("  Trabalhadas: {$oldWorked}h → {$newWorked}h");
                }

                $bar->advance();
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("Erro ao sincronizar projeto #{$project->id}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Sincronização concluída!");
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total de Projetos', $projects->count()],
                ['Projetos Sincronizados', $synced],
                ['Projetos sem Alteração', $projects->count() - $synced - $errors],
                ['Erros', $errors],
            ]
        );

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
