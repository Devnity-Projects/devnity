<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar permissões por módulo
        $permissions = [
            // Dashboard
            'view-dashboard',
            
            // Clientes
            'view-clients',
            'create-clients',
            'edit-clients',
            'delete-clients',
            
            // Projetos
            'view-projects',
            'create-projects',
            'edit-projects',
            'delete-projects',
            
            // Tarefas
            'view-tasks',
            'create-tasks',
            'edit-tasks',
            'delete-tasks',
            'assign-tasks',
            
            // Financeiro
            'view-financial',
            'create-transactions',
            'edit-transactions',
            'delete-transactions',
            'view-reports',
            
            // Suporte
            'view-tickets',
            'create-tickets',
            'edit-tickets',
            'delete-tickets',
            'assign-tickets',
            
            // Configurações
            'view-settings',
            'manage-users',
            'manage-roles',
            'manage-permissions',
            'impersonate-users',
            
            // Sistema
            'view-logs',
            'manage-system',
        ];

        // Criar todas as permissões
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Criar roles e atribuir permissões

        // 1. Super Admin - Acesso total
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. Admin - Administrador geral (sem acesso a sistema)
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view-dashboard',
            'view-clients', 'create-clients', 'edit-clients', 'delete-clients',
            'view-projects', 'create-projects', 'edit-projects', 'delete-projects',
            'view-tasks', 'create-tasks', 'edit-tasks', 'delete-tasks', 'assign-tasks',
            'view-financial', 'create-transactions', 'edit-transactions', 'delete-transactions', 'view-reports',
            'view-tickets', 'create-tickets', 'edit-tickets', 'delete-tickets', 'assign-tickets',
            'view-settings', 'manage-users',
        ]);

        // 3. Manager - Gerente de projetos
        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'view-dashboard',
            'view-clients', 'create-clients', 'edit-clients',
            'view-projects', 'create-projects', 'edit-projects',
            'view-tasks', 'create-tasks', 'edit-tasks', 'assign-tasks',
            'view-financial', 'view-reports',
            'view-tickets', 'create-tickets', 'edit-tickets', 'assign-tickets',
            'view-settings',
        ]);

        // 4. Developer - Desenvolvedor
        $developer = Role::create(['name' => 'developer']);
        $developer->givePermissionTo([
            'view-dashboard',
            'view-clients',
            'view-projects',
            'view-tasks', 'create-tasks', 'edit-tasks',
            'view-tickets', 'create-tickets',
            'view-settings',
        ]);

        // 5. Client - Cliente externo
        $client = Role::create(['name' => 'client']);
        $client->givePermissionTo([
            'view-dashboard',
            'view-projects',
            'view-tasks',
            'view-tickets', 'create-tickets',
        ]);

        // 6. Support - Suporte técnico
        $support = Role::create(['name' => 'support']);
        $support->givePermissionTo([
            'view-dashboard',
            'view-tickets', 'create-tickets', 'edit-tickets', 'assign-tickets',
            'view-settings',
        ]);

        // 7. Financial - Financeiro
        $financial = Role::create(['name' => 'financial']);
        $financial->givePermissionTo([
            'view-dashboard',
            'view-clients',
            'view-projects',
            'view-financial', 'create-transactions', 'edit-transactions', 'view-reports',
            'view-settings',
        ]);

        $this->command->info('Roles e permissões criadas com sucesso!');
        $this->command->info('Roles criadas:');
        $this->command->info('- super-admin (Acesso Total)');
        $this->command->info('- admin (Administrador)');
        $this->command->info('- manager (Gerente)');
        $this->command->info('- developer (Desenvolvedor)');
        $this->command->info('- client (Cliente)');
        $this->command->info('- support (Suporte)');
        $this->command->info('- financial (Financeiro)');
    }
}
