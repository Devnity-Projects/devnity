<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Garantir que a role 'admin' exista
        $role = Role::firstOrCreate(['name' => 'admin']);

        // Criar/atualizar usuário admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@devnity.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                // Campos opcionais caso existam na tabela
                'avatar' => null,
                'phone' => '+55 11 99999-9999',
                'bio' => 'Administrador do sistema Devnity',
            ]
        );

        // Atribuir role admin (idempotente)
        if (! $admin->hasRole('admin')) {
            $admin->assignRole($role);
        }
    }
}
