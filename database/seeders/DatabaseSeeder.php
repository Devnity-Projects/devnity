<?php 

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Rodar seeder de permissões/roles primeiro
        $this->call([
            PermissionSeeder::class,
        ]);

        // Criar/garantir usuário admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@devnity.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'avatar' => null,
                'phone' => '+55 11 99999-9999',
                'bio' => 'Administrador do sistema Devnity',
            ]
        );

        // Atribuir role admin
        $admin->assignRole('admin');

        // Criar/garantir usuário de testes
        $test = User::firstOrCreate(
            ['email' => 'test@devnity.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('test123'),
                'email_verified_at' => now(),
                'avatar' => null,
                'phone' => '+55 11 98888-7777',
                'bio' => 'Usuário de testes',
            ]
        );
        // Atribuir role básica
        $test->assignRole('user');

        // Seeders financeiros removidos conforme solicitado
    }
}