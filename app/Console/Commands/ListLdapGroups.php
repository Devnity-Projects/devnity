<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LdapRecord\Container;
use LdapRecord\Models\Entry;

class ListLdapGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:list-groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista todos os grupos do LDAP/Active Directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Listando grupos do LDAP...');
        $this->newLine();

        try {
            $connection = Container::getDefaultConnection();
            $connection->connect();

            // Buscar grupos (groupOfNames para OpenLDAP)
            $groupsBaseDn = 'ou=groups,dc=devnity,dc=com,dc=br';
            
            $this->line("🔍 Buscando em: {$groupsBaseDn}");
            $this->newLine();
            
            $groups = Entry::query()
                ->setDn($groupsBaseDn)
                ->where('objectclass', '=', 'groupOfNames')
                ->get();

            if ($groups->count() === 0) {
                $this->warn('❌ Nenhum grupo encontrado no LDAP');
                $this->newLine();
                $this->info('💡 Verifique se há grupos criados no seu servidor LDAP');
                $this->info('💡 Para OpenLDAP, os grupos devem ter objectClass: groupOfNames');
                return 0;
            }

            $this->info("✅ Encontrados {$groups->count()} grupo(s):");
            $this->newLine();

            $tableData = [];
            foreach ($groups as $group) {
                $members = $group->getFirstAttribute('member');
                $memberCount = is_array($members) ? count($members) : ($members ? 1 : 0);
                
                $tableData[] = [
                    $group->getFirstAttribute('cn') ?? 'N/A',
                    $group->getDn() ?? 'N/A',
                    $memberCount,
                ];
            }

            $this->table(
                ['Nome do Grupo', 'DN', 'Membros'],
                $tableData
            );

            $this->newLine();
            $this->info('💡 Mapeamento de grupos configurado no .env:');
            $this->line('   LDAP_ADMIN_GROUP = ' . env('LDAP_ADMIN_GROUP'));
            $this->line('   LDAP_MANAGER_GROUP = ' . env('LDAP_MANAGER_GROUP'));
            $this->line('   LDAP_DEVELOPER_GROUP = ' . env('LDAP_DEVELOPER_GROUP'));

        } catch (\Exception $e) {
            $this->error('❌ Erro ao listar grupos: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
