<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LdapRecord\Container;
use App\Ldap\User as LdapUser;

class TestLdapConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:test {username?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa conexão LDAP e busca usuário (opcional)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔌 Testando conexão com Active Directory...');
        $this->newLine();

        try {
            // Obter conexão LDAP
            $connection = Container::getDefaultConnection();
            
            $this->info('📡 Conectando ao servidor: ' . env('LDAP_HOST'));
            
            // Testar conexão
            $connection->connect();
            
            $this->info('✅ Conexão estabelecida com sucesso!');
            $this->newLine();

            // Se um username foi fornecido, buscar o usuário
            if ($username = $this->argument('username')) {
                $this->info("🔍 Buscando usuário: {$username}");
                $this->newLine();

                // Tentar buscar por uid (OpenLDAP)
                $user = LdapUser::where('uid', '=', $username)->first();
                
                // Se não encontrar por uid, tentar por samaccountname (AD)
                if (!$user) {
                    $user = LdapUser::where('samaccountname', '=', $username)->first();
                }
                
                // Se não encontrar, tentar por email
                if (!$user) {
                    $user = LdapUser::where('mail', '=', $username)->first();
                }

                if ($user) {
                    $this->info('✅ Usuário encontrado!');
                    $this->newLine();

                    $this->table(
                        ['Atributo', 'Valor'],
                        [
                            ['Username', $user->getUsername()],
                            ['Nome Completo', $user->getFullName()],
                            ['Email', $user->getEmail()],
                            ['DN', $user->getDn()],
                        ]
                    );

                    $this->newLine();
                    $this->info('👥 Grupos do usuário:');
                    $groups = $user->getGroups();
                    
                    if (count($groups) > 0) {
                        foreach ($groups as $group) {
                            $this->line('  • ' . $group);
                        }
                    } else {
                        $this->warn('  Nenhum grupo encontrado');
                    }
                } else {
                    $this->error('❌ Usuário não encontrado no Active Directory');
                }
            } else {
                $this->info('💡 Dica: Execute "php artisan ldap:test nome.usuario" para buscar um usuário específico');
            }

        } catch (\LdapRecord\Auth\BindException $e) {
            $this->error('❌ Erro de autenticação LDAP');
            $this->error('Verifique LDAP_USERNAME e LDAP_PASSWORD no .env');
            $this->error('Detalhes: ' . $e->getMessage());
            return 1;
        } catch (\LdapRecord\ConnectionException $e) {
            $this->error('❌ Erro ao conectar ao servidor LDAP');
            $this->error('Verifique LDAP_HOST e LDAP_PORT no .env');
            $this->error('Detalhes: ' . $e->getMessage());
            return 1;
        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            return 1;
        }

        $this->newLine();
        $this->info('✅ Teste concluído!');
        return 0;
    }
}
