<?php

namespace App\Ldap;

use LdapRecord\Models\Model;

class User extends Model
{
    /**
     * The object classes of the LDAP model.
     * Para OpenLDAP usar: inetOrgPerson
     * Para Active Directory usar: user
     */
    public static array $objectClasses = [
        'top',
        'person',
        'organizationalperson',
        'inetorgperson', // OpenLDAP
    ];

    /**
     * The attribute key for the LDAP GUID.
     * Para OpenLDAP usar: entryuuid
     * Para Active Directory usar: objectguid
     */
    protected string $guidKey = 'entryuuid';

    /**
     * Obtém os grupos do usuário
     * Suporta:
     * - Active Directory: atributo memberof
     * - OpenLDAP groupOfNames: atributo member (DN completo)
     * - OpenLDAP posixGroup: atributo memberUid (apenas username)
     */
    public function getGroups(): array
    {
        // Tentar primeiro o atributo memberof (Active Directory)
        $memberOf = $this->getAttribute('memberof');
        
        if (!is_null($memberOf)) {
            // Se for string única, converte para array
            if (is_string($memberOf)) {
                return [$memberOf];
            }
            return is_array($memberOf) ? $memberOf : [];
        }

        // OpenLDAP: buscar grupos onde este usuário é membro
        try {
            $userDn = $this->getDn();
            $username = $this->getUsername(); // uid do usuário
            
            \Log::info('Buscando grupos OpenLDAP', [
                'user_dn' => $userDn,
                'username' => $username
            ]);
            
            // Buscar todos os grupos (posixGroup, groupOfNames, etc)
            $connection = \LdapRecord\Container::getDefaultConnection();
            $allGroups = $connection->query()
                ->where('objectclass', '=', 'posixGroup')
                ->orWhere('objectclass', '=', 'groupOfNames')
                ->orWhere('objectclass', '=', 'groupOfUniqueNames')
                ->get();

            \Log::info('Grupos disponíveis no LDAP', [
                'count' => count($allGroups)
            ]);
            
            // Filtrar grupos que contém este usuário
            $groupDns = [];
            
            foreach ($allGroups as $group) {
                $groupCn = $group['cn'][0] ?? 'unknown';
                $objectClasses = $group['objectclass'] ?? [];
                
                // posixGroup: usa memberUid (apenas username)
                if (in_array('posixGroup', $objectClasses) || in_array('posixgroup', $objectClasses)) {
                    $memberUids = $group['memberuid'] ?? [];
                    if (!is_array($memberUids)) {
                        $memberUids = [$memberUids];
                    }
                    
                    // Verificar se o username está no grupo
                    foreach ($memberUids as $memberUid) {
                        if (strcasecmp($memberUid, $username) === 0) {
                            $groupDns[] = $group['dn'];
                            \Log::info("Usuário encontrado no posixGroup", [
                                'group' => $groupCn,
                                'memberUid' => $memberUid
                            ]);
                            break;
                        }
                    }
                }
                
                // groupOfNames/groupOfUniqueNames: usa member/uniqueMember (DN completo)
                $memberAttrs = ['member', 'uniquemember'];
                foreach ($memberAttrs as $attr) {
                    if (isset($group[$attr])) {
                        $members = $group[$attr];
                        if (!is_array($members)) {
                            $members = [$members];
                        }
                        
                        // Verificar se o DN do usuário está no grupo
                        foreach ($members as $memberDn) {
                            if (strcasecmp($memberDn, $userDn) === 0) {
                                $groupDns[] = $group['dn'];
                                \Log::info("Usuário encontrado no grupo", [
                                    'group' => $groupCn,
                                    'attribute' => $attr
                                ]);
                                break 2;
                            }
                        }
                    }
                }
            }

            \Log::info('Grupos do usuário encontrados', [
                'count' => count($groupDns),
                'groups' => $groupDns
            ]);

            return $groupDns;
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar grupos do usuário LDAP', [
                'user_dn' => $this->getDn() ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obtém o nome de usuário (uid para OpenLDAP, samaccountname para AD)
     */
    public function getUsername(): ?string
    {
        // Tenta uid primeiro (OpenLDAP), depois samaccountname (AD)
        return $this->getFirstAttribute('uid') ?? $this->getFirstAttribute('samaccountname');
    }

    /**
     * Obtém o email
     */
    public function getEmail(): ?string
    {
        return $this->getFirstAttribute('mail');
    }

    /**
     * Obtém o nome completo
     */
    public function getFullName(): ?string
    {
        return $this->getFirstAttribute('cn');
    }

    /**
     * Obtém o primeiro nome
     */
    public function getFirstName(): ?string
    {
        return $this->getFirstAttribute('givenname');
    }

    /**
     * Obtém o sobrenome
     */
    public function getLastName(): ?string
    {
        return $this->getFirstAttribute('sn');
    }
}
