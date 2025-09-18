<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Definir permissões básicas do sistema + granular de clientes
        $permissions = [
            'manage users',
            'manage projects',
            'view reports',
            'manage finances',

            // Clientes - permissões de módulo
            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',
            'clients.manage', // acesso amplo ao módulo
            'clients.export',

            // Clientes - permissões de campos
            'clients.view_document',
            'clients.view_contact',
            'clients.view_address',
            'clients.view_tax_info',
            'clients.view_notes',

            // Projetos - permissões de módulo
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',
            'projects.manage', // amplo: inclui bulk e status
            'projects.update_status',

            // Tarefas - permissões de módulo
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',
            'tasks.manage', // amplo
            'tasks.update_status',
            // Tarefas - comentários
            'tasks.comments.add',
            'tasks.comments.delete',
            // Tarefas - anexos
            'tasks.attachments.upload',
            'tasks.attachments.download',
            'tasks.attachments.delete',
            // Tarefas - checklist
            'tasks.checklist.add',
            'tasks.checklist.update',
            'tasks.checklist.delete',

            // Kanban
            'kanban.view',
            'kanban.update_status',
            'kanban.move',
            'kanban.reorder',
            'kanban.quick_create',

            // Suporte
            'support.admin',
            'support.tickets.view',
            'support.tickets.create',
            'support.tickets.update',
            'support.tickets.delete',
            'support.responses.create',
            'support.responses.delete',
            'support.categories.view',
            'support.categories.create',
            'support.categories.update',
            'support.categories.delete',

            // Financeiro
            'financial.view', // dashboard
            'financial.export',
            'financial.categories.view',
            'financial.categories.create',
            'financial.categories.update',
            'financial.categories.delete',
            'financial.categories.manage', // bulk e toggle status
            'financial.transactions.view',
            'financial.transactions.create',
            'financial.transactions.update',
            'financial.transactions.delete',
            'financial.transactions.mark_paid',
            'financial.transactions.mark_pending',
            'financial.transactions.cancel',
            'financial.transactions.manage', // bulk
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $user = Role::firstOrCreate(['name' => 'user']);

        // Assign permissions
        $admin->givePermissionTo(Permission::all());

        $manager->givePermissionTo([
            'manage projects', 'view reports',
            // Clientes
            'clients.view', 'clients.create', 'clients.update',
            'clients.view_contact', 'clients.view_address', 'clients.view_notes',
            // Projetos
            'projects.view', 'projects.create', 'projects.update', 'projects.update_status',
            // Tarefas (gerais + comentários e checklist básicos)
            'tasks.view', 'tasks.create', 'tasks.update', 'tasks.update_status',
            'tasks.comments.add', 'tasks.comments.delete',
            'tasks.checklist.add', 'tasks.checklist.update', 'tasks.checklist.delete',
            // Kanban
            'kanban.view', 'kanban.update_status', 'kanban.move', 'kanban.reorder',
            // Suporte
            'support.tickets.view', 'support.tickets.create', 'support.tickets.update',
        ]);

        $user->givePermissionTo([
            'view reports',
            // Clientes
            'clients.view',
            // Projetos e tarefas (visualização básica)
            'projects.view',
            'tasks.view',
            'kanban.view',
            // Suporte (básico)
            'support.tickets.view', 'support.tickets.create',
            // Financeiro (apenas visualização do dashboard se aplicável)
            'financial.view',
            // por padrão usuários não tem campos sensíveis
        ]);
    }
}
