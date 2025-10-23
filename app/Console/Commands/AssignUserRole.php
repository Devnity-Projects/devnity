<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-role {username} {role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atribui uma role a um usuário';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $roleName = $this->argument('role');

        // Buscar usuário por samaccountname ou email
        $user = User::where('samaccountname', $username)
            ->orWhere('email', $username)
            ->first();

        if (!$user) {
            $this->error("❌ Usuário '{$username}' não encontrado!");
            $this->info("💡 O usuário precisa fazer login pelo menos uma vez para ser criado no banco.");
            return 1;
        }

        // Verificar se a role existe
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            $this->error("❌ Role '{$roleName}' não existe!");
            $this->newLine();
            $this->info("📋 Roles disponíveis:");
            foreach (Role::all() as $r) {
                $this->line("  • {$r->name}");
            }
            return 1;
        }

        // Remover roles antigas e atribuir a nova
        $oldRoles = $user->getRoleNames()->toArray();
        $user->syncRoles([$roleName]);

        $this->info("✅ Role '{$roleName}' atribuída ao usuário '{$user->name}' ({$user->samaccountname})!");
        
        if (count($oldRoles) > 0) {
            $this->line("   Roles anteriores: " . implode(', ', $oldRoles));
        }

        return 0;
    }
}
