# 🚀 LDAP/AD - Referência Rápida do Devnity

**Documento de referência rápida das customizações específicas que fizemos no projeto.**

---

## ⚡ Mudanças Críticas (Resumo de 5 Minutos)

### 1. OpenLDAP vs Active Directory

```php
// ✅ DEVNITY usa OpenLDAP
// app/Ldap/User.php
public static array $objectClasses = ['inetorgperson'];
protected string $guidKey = 'entryuuid';

// ❌ Active Directory usaria:
public static array $objectClasses = ['user'];
protected string $guidKey = 'objectguid';
```

### 2. Login com Username (não email)

```php
// ✅ DEVNITY: app/Providers/FortifyServiceProvider.php
$loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'uid';
```

### 3. Busca Manual de Grupos (OpenLDAP)

```php
// ✅ DEVNITY: OpenLDAP não tem memberOf, busca manual
// app/Ldap/User.php -> getGroups()
$allGroups = $connection->query()
    ->where('objectclass', '=', 'posixGroup')
    ->orWhere('objectclass', '=', 'groupOfNames')
    ->get();
```

### 4. Comparação Case-Insensitive

```php
// ✅ DEVNITY: app/Models/User.php
if (strcasecmp($group, $ldapGroup) === 0) {  // IMPORTANTE!
    $assignRoles[] = $role;
}

// ❌ NÃO USE: if ($group === $ldapGroup)
```

### 5. Sincronização Apenas no Login

```php
// ✅ DEVNITY: Sincroniza só no login (não em toda requisição)
// ❌ EVITE: Route::middleware(['auth', 'sync.ldap.groups'])
```

---

## 📋 Variáveis de Ambiente (.env)

```env
# Conexão LDAP
LDAP_HOST=ldap://192.168.1.100
LDAP_PORT=389
LDAP_BASE_DN="dc=devnity,dc=com,dc=br"
LDAP_USERNAME="cn=admin,dc=devnity,dc=com,dc=br"
LDAP_PASSWORD="senha_admin"
LDAP_SSL=false
LDAP_TLS=false

# Mapeamento de Grupos (DN COMPLETO!)
LDAP_ADMIN_GROUP="cn=Administradores,ou=groups,dc=devnity,dc=com,dc=br"
LDAP_MANAGER_GROUP="cn=Gerentes,ou=groups,dc=devnity,dc=com,dc=br"
LDAP_DEVELOPER_GROUP="cn=Desenvolvedores,ou=groups,dc=devnity,dc=com,dc=br"
LDAP_SUPPORT_GROUP="cn=Suporte,ou=groups,dc=devnity,dc=com,dc=br"
LDAP_FINANCIAL_GROUP="cn=Financeiro,ou=groups,dc=devnity,dc=com,dc=br"
LDAP_CLIENT_GROUP="cn=Clientes,ou=groups,dc=devnity,dc=com,dc=br"
```

---

## 📁 Arquivos Modificados

### 1. app/Ldap/User.php

```php
<?php

namespace App\Ldap;

use LdapRecord\Models\Model;

class User extends Model
{
    // OpenLDAP específico
    public static array $objectClasses = [
        'top',
        'person',
        'organizationalperson',
        'inetorgperson',
    ];

    protected string $guidKey = 'entryuuid';  // OpenLDAP

    // Busca manual de grupos (OpenLDAP não tem memberOf)
    public function getGroups(): array
    {
        $connection = \LdapRecord\Container::getDefaultConnection();
        $username = $this->getUsername();
        $userDn = $this->getDn();
        
        $allGroups = $connection->query()
            ->where('objectclass', '=', 'posixGroup')
            ->orWhere('objectclass', '=', 'groupOfNames')
            ->get();

        $groupDns = [];
        
        foreach ($allGroups as $group) {
            // posixGroup: usa memberUid
            if (isset($group['memberuid'])) {
                $memberUids = is_array($group['memberuid']) ? $group['memberuid'] : [$group['memberuid']];
                if (in_array($username, $memberUids)) {
                    $groupDns[] = $group['dn'];
                }
            }
            
            // groupOfNames: usa member (DN completo)
            if (isset($group['member'])) {
                $members = is_array($group['member']) ? $group['member'] : [$group['member']];
                if (in_array($userDn, $members)) {
                    $groupDns[] = $group['dn'];
                }
            }
        }
        
        return $groupDns;
    }

    public function getUsername(): string
    {
        return $this->getFirstAttribute('uid') ?? 
               $this->getFirstAttribute('samaccountname') ?? '';
    }
}
```

### 2. app/Models/User.php

```php
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;

class User extends Authenticatable implements LdapAuthenticatable
{
    use AuthenticatesWithLdap, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'bio',
        'domain', 'guid', 'samaccountname',  // LDAP
    ];

    // Sincronização de grupos com comparação case-insensitive
    public function syncRolesFromLdap(array $groups): void
    {
        $roleMappings = [
            env('LDAP_ADMIN_GROUP') => 'admin',
            env('LDAP_MANAGER_GROUP') => 'manager',
            env('LDAP_DEVELOPER_GROUP') => 'developer',
            env('LDAP_SUPPORT_GROUP') => 'support',
            env('LDAP_FINANCIAL_GROUP') => 'financial',
            env('LDAP_CLIENT_GROUP') => 'client',
        ];

        $assignRoles = [];

        foreach ($groups as $group) {
            foreach ($roleMappings as $ldapGroup => $role) {
                if (strcasecmp($group, $ldapGroup) === 0) {  // Case-insensitive!
                    $assignRoles[] = $role;
                    break;
                }
            }
        }

        \Log::info("Mapeamento de grupos LDAP", [
            'user' => $this->email,
            'ldap_groups' => $groups,
            'mapped_roles' => $assignRoles
        ]);

        if (!empty($assignRoles)) {
            $this->syncRoles($assignRoles);
        }
    }
}
```

### 3. app/Providers/FortifyServiceProvider.php

```php
Fortify::authenticateUsing(function (Request $request) {
    $credentials = ['password' => $request->password];

    // Detectar se é email ou username (uid)
    $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'uid';
    $credentials[$loginField] = $request->email;

    if (\Auth::attempt($credentials, $request->filled('remember'))) {
        return \Auth::user();
    }

    return null;
});
```

### 4. config/auth.php

```php
'providers' => [
    'users' => [
        'driver' => 'ldap',  // ← Mudança principal
        'model' => App\Ldap\User::class,
        'database' => [
            'model' => App\Models\User::class,
            'sync_passwords' => false,
            'sync_attributes' => [
                'name' => 'cn',
                'email' => 'mail',
                'username' => 'uid',
            ],
        ],
    ],
],
```

### 5. Migration

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('domain')->nullable();
    $table->string('guid')->unique()->nullable();
    $table->string('samaccountname')->unique()->nullable();
    $table->string('password')->nullable()->change();  // Permitir null
});
```

---

## 🔍 Comandos de Debug

### Testar Conexão LDAP

```bash
php artisan ldap:test
```

### Ver Logs em Tempo Real

```powershell
# Windows PowerShell
Get-Content storage/logs/laravel.log -Wait | Select-String -Pattern "ldap"
```

```bash
# Linux/Mac
tail -f storage/logs/laravel.log | grep -i ldap
```

### Testar no Tinker

```bash
php artisan tinker
```

```php
// Testar autenticação
auth()->attempt(['uid' => 'usuario', 'password' => 'senha']);

// Ver grupos LDAP de um usuário
$user = User::find(1);
$ldapUser = \App\Ldap\User::findByGuid($user->guid);
$ldapUser->getGroups();

// Ver roles sincronizadas
$user->roles->pluck('name');

// Forçar sincronização
$user->syncRolesFromLdap($ldapUser->getGroups());
```

---

## ⚠️ Erros Comuns e Soluções

### Erro: "Groups not syncing"

**Causa:** DNs dos grupos não coincidem (case-sensitive ou incompleto)

**Solução:**
```bash
# Ver quais grupos o usuário tem no LDAP
php artisan tinker
>>> $user = User::find(1);
>>> $ldap = \App\Ldap\User::findByGuid($user->guid);
>>> $ldap->getGroups();  // Ver DNs exatos

# Copiar DN completo para .env
LDAP_ADMIN_GROUP="cn=Administradores,ou=groups,dc=devnity,dc=com,dc=br"
```

### Erro: "Invalid credentials"

**Causa:** Campo de login incorreto (uid vs samaccountname vs email)

**Solução:**
```php
// Verificar qual campo seu LDAP usa
// OpenLDAP: uid
// Active Directory: samaccountname

// Ajustar em FortifyServiceProvider.php
$loginField = 'uid';  // ou 'samaccountname' ou 'email'
```

### Erro: "User not found in LDAP"

**Causa:** Base DN incorreto ou usuário não está nessa OU

**Solução:**
```env
# Verificar Base DN
LDAP_BASE_DN="dc=devnity,dc=com,dc=br"

# Se usuários estão em OU específica:
LDAP_BASE_DN="ou=usuarios,dc=devnity,dc=com,dc=br"
```

---

## ✅ Checklist Mínimo

- [ ] `composer require directorytree/ldaprecord-laravel`
- [ ] `composer require spatie/laravel-permission`
- [ ] Copiar `app/Ldap/User.php` (ajustar objectClasses/guidKey)
- [ ] Atualizar `app/Models/User.php` (implements + trait + syncRolesFromLdap)
- [ ] Atualizar `config/auth.php` (driver: 'ldap')
- [ ] Atualizar `app/Providers/FortifyServiceProvider.php` (authenticateUsing)
- [ ] Criar migration com colunas LDAP
- [ ] Configurar `.env` com credenciais e mapeamento
- [ ] `php artisan migrate`
- [ ] Testar login

---

## 🎯 Diferenças OpenLDAP vs Active Directory

| Aspecto | OpenLDAP (Devnity) | Active Directory |
|---------|-------------------|------------------|
| objectClass | `inetorgperson` | `user` |
| GUID | `entryuuid` | `objectguid` |
| Username | `uid` | `samaccountname` |
| Grupos | Busca manual (getGroups) | Atributo `memberOf` |
| Tipo Grupo | `posixGroup`, `groupOfNames` | `group` |

---

**Criado:** 23 de outubro de 2025  
**Projeto:** Devnity  
**Versão:** 1.0.0
