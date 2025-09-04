<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-test-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um usuário teste para desenvolvimento';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Verificar se já existe um usuário com este email
        $existingUser = User::where('email', 'teste@devnity.com')->first();
        
        if ($existingUser) {
            $this->info('Usuário teste já existe!');
            $this->line('Email: ' . $existingUser->email);
            $this->line('ID: ' . $existingUser->id);
            return;
        }

        // Criar o usuário teste
        $user = User::create([
            'name' => 'Usuário Teste',
            'email' => 'teste@devnity.com',
            'password' => Hash::make('123456789'),
            'email_verified_at' => now(),
        ]);

        $this->info('Usuário teste criado com sucesso!');
        $this->line('Email: ' . $user->email);
        $this->line('Senha: 123456789');
        $this->line('ID: ' . $user->id);
    }
}
