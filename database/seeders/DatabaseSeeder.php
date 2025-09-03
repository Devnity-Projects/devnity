<?php 

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuário admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@devnity.com',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
            'avatar' => null,
            'phone' => '+55 11 99999-9999',
            'bio' => 'Administrador do sistema Devnity',
        ]);

        // Seed financial categories
        $this->call([
            FinancialCategorySeeder::class,
        ]);
    }
}