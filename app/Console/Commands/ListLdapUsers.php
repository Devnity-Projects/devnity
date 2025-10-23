<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LdapRecord\Container;
use App\Ldap\User as LdapUser;

class ListLdapUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:list-users {--limit=10 : Limite de usuários a exibir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista todos os usuários do LDAP/Active Directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Listando usuários do LDAP...');
        $this->newLine();

        try {
            $connection = Container::getDefaultConnection();
            $connection->connect();

            $limit = (int) $this->option('limit');
            
            // Buscar todos os usuários com inetOrgPerson (OpenLDAP)
            $users = LdapUser::query()
                ->limit($limit)
                ->get();

            if ($users->count() === 0) {
                $this->warn('❌ Nenhum usuário encontrado no LDAP');
                $this->newLine();
                $this->info('💡 Verifique se há usuários criados no seu servidor LDAP');
                return 0;
            }

            $this->info("✅ Encontrados {$users->count()} usuário(s):");
            $this->newLine();

            $tableData = [];
            foreach ($users as $user) {
                $tableData[] = [
                    $user->getFirstAttribute('uid') ?? $user->getFirstAttribute('samaccountname') ?? 'N/A',
                    $user->getFirstAttribute('cn') ?? 'N/A',
                    $user->getFirstAttribute('mail') ?? 'N/A',
                    $user->getDn() ?? 'N/A',
                ];
            }

            $this->table(
                ['Username (uid)', 'Nome Completo', 'Email', 'DN'],
                $tableData
            );

            $this->newLine();
            $this->info('💡 Para testar login com um usuário específico:');
            $this->line('   php artisan ldap:test [username]');

        } catch (\Exception $e) {
            $this->error('❌ Erro ao listar usuários: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
