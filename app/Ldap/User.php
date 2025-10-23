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
     */
    public function getGroups(): array
    {
        $memberOf = $this->getAttribute('memberof');
        
        if (is_null($memberOf)) {
            return [];
        }

        // Se for string única, converte para array
        if (is_string($memberOf)) {
            return [$memberOf];
        }

        return is_array($memberOf) ? $memberOf : [];
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
