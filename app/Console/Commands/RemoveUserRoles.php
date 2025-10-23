<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RemoveUserRoles extends Command
{
    protected $signature = 'user:remove-roles {user_id}';
    protected $description = 'Remove todas as roles de um usuário';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("Usuário #{$userId} não encontrado");
            return 1;
        }
        
        $currentRoles = $user->getRoleNames()->toArray();
        $this->info("Roles atuais: " . implode(', ', $currentRoles));
        
        $user->syncRoles([]);
        
        $this->info("✓ Todas as roles removidas do usuário {$user->name}");
        return 0;
    }
}
