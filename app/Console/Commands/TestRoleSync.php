<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TestRoleSync extends Command
{
    protected $signature = 'test:role-sync {user_id}';
    protected $description = 'Testa a sincronização de roles LDAP para um usuário';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("Usuário #{$userId} não encontrado");
            return 1;
        }
        
        $this->info("=== Teste de Sincronização de Roles ===");
        $this->info("Usuário: {$user->name} ({$user->email})");
        $this->info("Roles atuais: " . implode(', ', $user->getRoleNames()->toArray()));
        
        // Buscar usuário LDAP diretamente
        $this->info("\nBuscando usuário no LDAP...");
        
        $username = $user->samaccountname ?? $user->email;
        $ldapUser = \App\Ldap\User::where('uid', '=', $username)->first();
        
        if (!$ldapUser) {
            $this->error("Usuário LDAP não encontrado com uid={$username}");
            return 1;
        }
        
        $this->info("✓ Usuário LDAP encontrado: {$ldapUser->getDn()}");
        
        // Obter grupos
        $this->info("\nObtendo grupos LDAP...");
        $groups = $ldapUser->getGroups();
        
        $this->info("Grupos encontrados: " . count($groups));
        foreach ($groups as $group) {
            $this->line("  - {$group}");
        }
        
        // Sincronizar roles
        $this->info("\nSincronizando roles...");
        $user->syncRolesFromLdap($groups);
        
        $this->info("\n✓ Sincronização concluída!");
        $this->info("Roles após sincronização: " . implode(', ', $user->fresh()->getRoleNames()->toArray()));
        
        return 0;
    }
}
