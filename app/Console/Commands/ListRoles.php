<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class ListRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista todas as roles disponíveis no sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📋 Roles disponíveis no sistema:');
        $this->newLine();

        $roles = Role::all();

        if ($roles->isEmpty()) {
            $this->warn('⚠️  Nenhuma role encontrada!');
            $this->info('💡 Execute o seeder: php artisan db:seed --class=RolesAndPermissionsSeeder');
            return 1;
        }

        $tableData = [];
        foreach ($roles as $role) {
            $tableData[] = [
                $role->name,
                $role->guard_name,
                $role->users_count ?? $role->users()->count(),
            ];
        }

        $this->table(
            ['Role', 'Guard', 'Usuários'],
            $tableData
        );

        $this->newLine();
        $this->info('💡 Para atribuir uma role a um usuário:');
        $this->line('   php artisan user:assign-role [username] [role]');

        return 0;
    }
}
