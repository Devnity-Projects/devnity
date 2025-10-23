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

        // Definir todas as permissões por módulo
        $permissionsByModule = [
            'dashboard' => [
                'dashboard.view' => 'Visualizar Dashboard',
            ],
            'clients' => [
                'clients.view' => 'Visualizar Clientes',
                'clients.create' => 'Criar Clientes',
                'clients.edit' => 'Editar Clientes',
                'clients.delete' => 'Excluir Clientes',
            ],
            'projects' => [
                'projects.view' => 'Visualizar Projetos',
                'projects.create' => 'Criar Projetos',
                'projects.edit' => 'Editar Projetos',
                'projects.delete' => 'Excluir Projetos',
            ],
            'tasks' => [
                'tasks.view' => 'Visualizar Tarefas',
                'tasks.create' => 'Criar Tarefas',
                'tasks.edit' => 'Editar Tarefas',
                'tasks.delete' => 'Excluir Tarefas',
                'tasks.assign' => 'Atribuir Tarefas',
            ],
            'financial' => [
                'financial.view' => 'Visualizar Financeiro',
                'financial.manage' => 'Gerenciar Financeiro',
            ],
            'support' => [
                'support.view' => 'Visualizar Tickets',
                'support.create' => 'Criar Tickets',
                'support.edit' => 'Editar Tickets',
                'support.delete' => 'Excluir Tickets',
                'support.admin' => 'Administrar Suporte',
            ],
            'users' => [
                'users.view' => 'Visualizar Usuários',
                'users.manage' => 'Gerenciar Usuários',
                'users.impersonate' => 'Visualizar como Usuário',
            ],
            'roles' => [
                'roles.view' => 'Visualizar Roles',
                'roles.manage' => 'Gerenciar Roles',
            ],
            'settings' => [
                'settings.view' => 'Visualizar Configurações',
                'settings.manage' => 'Gerenciar Configurações',
            ],
            'system' => [
                'system.logs' => 'Visualizar Logs',
                'system.manage' => 'Gerenciar Sistema',
            ],
        ];

        // Criar todas as permissões
        $this->command->info('Criando permissões...');
        foreach ($permissionsByModule as $module => $permissions) {
            foreach ($permissions as $name => $description) {
                Permission::firstOrCreate(['name' => $name]);
                $this->command->info("  ✓ {$name}");
            }
        }

        // Criar roles e atribuir permissões
        $this->command->info("\nCriando roles...");

        // 1. Super Admin - Acesso total ao sistema
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions(Permission::all());
        $this->command->info('  ✓ Super Admin (acesso total)');

        // 2. Admin - Administrador geral
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions([
            // Dashboard
            'dashboard.view',
            // Clientes
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            // Projetos
            'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
            // Tarefas
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete', 'tasks.assign',
            // Financeiro
            'financial.view', 'financial.manage',
            // Suporte
            'support.view', 'support.create', 'support.edit', 'support.delete', 'support.admin',
            // Usuários
            'users.view', 'users.manage', 'users.impersonate',
            // Roles
            'roles.view', 'roles.manage',
            // Configurações
            'settings.view', 'settings.manage',
        ]);
        $this->command->info('  ✓ Admin (administrador geral)');

        // 3. Manager - Gerente de projetos
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->syncPermissions([
            'dashboard.view',
            'clients.view', 'clients.create', 'clients.edit',
            'projects.view', 'projects.create', 'projects.edit',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign',
            'financial.view',
            'support.view', 'support.create', 'support.edit',
            'settings.view',
        ]);
        $this->command->info('  ✓ Manager (gerente de projetos)');

        // 4. Developer - Desenvolvedor
        $developer = Role::firstOrCreate(['name' => 'Developer']);
        $developer->syncPermissions([
            'dashboard.view',
            'clients.view',
            'projects.view',
            'tasks.view', 'tasks.create', 'tasks.edit',
            'support.view', 'support.create',
            'settings.view',
        ]);
        $this->command->info('  ✓ Developer (desenvolvedor)');

        // 5. Financial - Responsável financeiro
        $financial = Role::firstOrCreate(['name' => 'Financial']);
        $financial->syncPermissions([
            'dashboard.view',
            'clients.view',
            'projects.view',
            'financial.view', 'financial.manage',
            'settings.view',
        ]);
        $this->command->info('  ✓ Financial (financeiro)');

        // 6. Support - Suporte técnico
        $support = Role::firstOrCreate(['name' => 'Support']);
        $support->syncPermissions([
            'dashboard.view',
            'support.view', 'support.create', 'support.edit', 'support.admin',
            'settings.view',
        ]);
        $this->command->info('  ✓ Support (suporte)');

        // 7. User - Usuário básico
        $user = Role::firstOrCreate(['name' => 'User']);
        $user->syncPermissions([
            'dashboard.view',
            'tasks.view',
            'support.view', 'support.create',
            'settings.view',
        ]);
        $this->command->info('  ✓ User (usuário básico)');

        $this->command->info("\n✅ Roles e permissões criadas com sucesso!");
    }
}
