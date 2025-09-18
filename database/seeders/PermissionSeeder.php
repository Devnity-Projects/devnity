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
            'clients.view', 'clients.create', 'clients.update',
            'clients.view_contact', 'clients.view_address', 'clients.view_notes',
        ]);

        $user->givePermissionTo([
            'view reports',
            'clients.view',
            // por padrão usuários não tem campos sensíveis
        ]);
    }
}
