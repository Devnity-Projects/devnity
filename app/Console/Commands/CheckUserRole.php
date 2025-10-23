<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:check-role {username?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica a role de um usuário ou lista todos os usuários';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');

        if ($username) {
            // Buscar usuário específico
            $user = User::where('samaccountname', $username)
                ->orWhere('email', $username)
                ->first();

            if (!$user) {
                $this->error("❌ Usuário '{$username}' não encontrado no banco de dados!");
                $this->newLine();
                $this->warn("⚠️  O usuário precisa fazer login pelo menos uma vez para ser criado.");
                $this->newLine();
                $this->info("💡 Faça login em: " . env('APP_URL') . "/login");
                return 1;
            }

            $this->info("👤 Informações do Usuário:");
            $this->newLine();

            $this->table(
                ['Campo', 'Valor'],
                [
                    ['ID', $user->id],
                    ['Nome', $user->name],
                    ['Username', $user->samaccountname ?? 'N/A'],
                    ['Email', $user->email ?? 'N/A'],
                    ['GUID', $user->guid ?? 'N/A'],
                    ['Domain', $user->domain ?? 'N/A'],
                ]
            );

            $this->newLine();
            $roles = $user->getRoleNames();
            
            if ($roles->isEmpty()) {
                $this->warn("⚠️  Nenhuma role atribuída!");
            } else {
                $this->info("🔐 Roles:");
                foreach ($roles as $role) {
                    $this->line("  • {$role}");
                }
            }

            $this->newLine();
            $this->info("💡 Para atribuir uma role:");
            $this->line("   php artisan user:assign-role {$username} [role]");

        } else {
            // Listar todos os usuários
            $this->info("👥 Todos os Usuários:");
            $this->newLine();

            $users = User::all();

            if ($users->isEmpty()) {
                $this->warn("⚠️  Nenhum usuário encontrado no banco de dados!");
                $this->newLine();
                $this->info("💡 Os usuários são criados automaticamente no primeiro login.");
                return 1;
            }

            $tableData = [];
            foreach ($users as $user) {
                $roles = $user->getRoleNames()->join(', ') ?: 'Nenhuma';
                $tableData[] = [
                    $user->id,
                    $user->name,
                    $user->samaccountname ?? 'N/A',
                    $user->email ?? 'N/A',
                    $roles,
                ];
            }

            $this->table(
                ['ID', 'Nome', 'Username', 'Email', 'Roles'],
                $tableData
            );

            $this->newLine();
            $this->info("💡 Para ver detalhes de um usuário:");
            $this->line("   php artisan user:check-role [username]");
        }

        return 0;
    }
}
