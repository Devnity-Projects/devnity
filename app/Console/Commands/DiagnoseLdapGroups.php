<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LdapRecord\Container;

class DiagnoseLdapGroups extends Command
{
    protected $signature = 'ldap:diagnose-groups';
    protected $description = 'Diagnostica a estrutura de grupos no LDAP';

    public function handle()
    {
        $this->info('=== Diagnóstico de Grupos LDAP ===');
        
        $connection = Container::getDefaultConnection();
        
        // Teste 1: Buscar grupo específico "Administradores"
        $this->info("\n1. Buscando grupo 'Administradores' diretamente:");
        try {
            $adminGroups = $connection->query()
                ->where('cn', '=', 'Administradores')
                ->get();
            
            $this->info("Grupos encontrados: " . count($adminGroups));
            
            foreach ($adminGroups as $adminGroup) {
                $this->info("✓ Grupo encontrado!");
                $this->line("DN: " . $adminGroup['dn']);
                $classes = $adminGroup['objectclass'] ?? [];
                $this->line("objectClass: " . (is_array($classes) ? implode(', ', $classes) : $classes));
                $this->line("CN: " . ($adminGroup['cn'][0] ?? 'N/A'));
                
                $members = $adminGroup['member'] ?? null;
                if ($members) {
                    $this->line("Members:");
                    if (is_array($members)) {
                        foreach ($members as $member) {
                            $this->line("  - $member");
                        }
                    } else {
                        $this->line("  - $members");
                    }
                } else {
                    $this->warn("Grupo não tem membros (atributo 'member' vazio)");
                }
            }
            
            if (count($adminGroups) == 0) {
                $this->error("✗ Grupo Administradores NÃO encontrado");
            }
        } catch (\Exception $e) {
            $this->error("Erro: " . $e->getMessage());
        }
        
        // Teste 2: Buscar por diferentes objectClass de grupo
        $this->info("\n2. Testando diferentes objectClass:");
        $groupClasses = ['groupOfNames', 'groupOfUniqueNames', 'posixGroup', 'group'];
        
        foreach ($groupClasses as $class) {
            $this->line("  Buscando objectClass={$class}...");
            try {
                $results = $connection->query()
                    ->where('objectclass', '=', $class)
                    ->get();
                $this->info("    ✓ Encontrados: " . count($results));
                foreach ($results as $r) {
                    $cn = $r['cn'][0] ?? 'N/A';
                    $dn = $r['dn'] ?? 'N/A';
                    $this->line("      * CN: {$cn} | DN: {$dn}");
                    
                    // Mostrar atributos de membros
                    if (isset($r['member'])) {
                        $this->line("        Atributo 'member': " . (is_array($r['member']) ? count($r['member']) . " membros" : $r['member']));
                    }
                    if (isset($r['memberuid'])) {
                        $memberUids = $r['memberuid'];
                        $this->line("        Atributo 'memberUid': " . (is_array($memberUids) ? implode(', ', $memberUids) : $memberUids));
                    }
                    if (isset($r['uniquemember'])) {
                        $this->line("        Atributo 'uniqueMember': " . (is_array($r['uniquemember']) ? count($r['uniquemember']) . " membros" : $r['uniquemember']));
                    }
                }
            } catch (\Exception $e) {
                $this->error("    ✗ Erro: " . $e->getMessage());
            }
        }
        
        // Teste 3: Listar tudo em ou=groups
        $this->info("\n3. Listando tudo em ou=groups:");
        try {
            // Buscar diretamente no DN especificado
            $results = $connection->query()
                ->setDn('ou=groups,dc=devnity,dc=com,dc=br')
                ->read()
                ->get();
                
            $this->info("Encontrados: " . count($results));
            foreach ($results as $entry) {
                $this->line("  - " . $entry->getDn());
            }
        } catch (\Exception $e) {
            $this->error("Erro: " . $e->getMessage());
        }
        
        return 0;
    }
}
