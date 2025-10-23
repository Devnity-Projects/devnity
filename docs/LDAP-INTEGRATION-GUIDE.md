# Guia Completo: Integração LDAP/Active Directory em Laravel

Este documento fornece um guia passo a passo para implementar autenticação e sincronização com Active Directory (LDAP) em projetos Laravel, baseado na implementação do projeto Devnity.

> **⚠️ ATENÇÃO:** Este guia documenta a implementação **REAL** do projeto Devnity. As seções marcadas com 🔧 mostram as customizações específicas que fizemos e que você deve adaptar ao seu contexto.

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Instalação e Configuração](#instalação-e-configuração)
3. [Estrutura de Arquivos](#estrutura-de-arquivos)
4. [Implementação Passo a Passo](#implementação-passo-a-passo)
5. [Sincronização de Grupos/Roles](#sincronização-de-gruposroles)
6. [Troubleshooting](#troubleshooting)
7. [Boas Práticas](#boas-práticas)

---

## 🎯 Visão Geral

### O que será implementado:

- ✅ Autenticação via Active Directory (LDAP)
- ✅ Sincronização automática de grupos do AD com roles do Laravel
- ✅ Criação automática de usuários na primeira autenticação
- ✅ Atualização de informações do usuário em cada login
- ✅ Suporte a múltiplos domínios (se necessário)
- ✅ Fallback para autenticação local (opcional)

### Fluxo de Funcionamento:

```
1. Usuário tenta fazer login
2. Sistema tenta autenticar via LDAP
3. Se sucesso:
   - Cria/atualiza usuário no banco de dados local
   - Sincroniza grupos do AD com roles do Laravel
   - Autentica usuário na aplicação
4. Se falha LDAP (opcional):
   - Tenta autenticação local (se configurado)
```

---

## � CUSTOMIZAÇÕES ESPECÍFICAS DO DEVNITY

**Esta seção documenta as mudanças EXATAS que fizemos no projeto. Adapte conforme sua necessidade.**

### 1. Suporte ao OpenLDAP (em vez de apenas Active Directory)

**Mudança no `app/Ldap/User.php`:**
```php
// ✅ Usamos objectClasses do OpenLDAP
public static array $objectClasses = [
    'top',
    'person',
    'organizationalperson',
    'inetorgperson', // OpenLDAP específico
];

// ✅ Usamos entryuuid do OpenLDAP (AD usa objectguid)
protected string $guidKey = 'entryuuid';
```

**Por quê:** Nosso servidor é OpenLDAP, não Windows Active Directory.

### 2. Login com Username (não email)

**Mudança no `app/Providers/FortifyServiceProvider.php`:**
```php
// ✅ Detecta se é email ou username
$loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'uid';
$credentials[$loginField] = $request->email;
```

**Por quê:** Nossos usuários fazem login com `uid` (username), não email.

### 3. Busca Manual de Grupos (OpenLDAP não tem memberOf)

**Mudança no `app/Ldap/User.php` - método `getGroups()`:**
```php
// ✅ OpenLDAP: buscar grupos manualmente (posixGroup + groupOfNames)
$allGroups = $connection->query()
    ->where('objectclass', '=', 'posixGroup')
    ->orWhere('objectclass', '=', 'groupOfNames')
    ->get();

// Filtrar grupos que contém este usuário
foreach ($allGroups as $group) {
    // posixGroup: usa memberUid (apenas username)
    // groupOfNames: usa member (DN completo)
}
```

**Por quê:** OpenLDAP não popula automaticamente `memberOf`. Precisamos buscar ativamente.

### 4. Comparação Case-Insensitive de Grupos

**Mudança no `app/Models/User.php` - método `syncRolesFromLdap()`:**
```php
// ✅ Comparação case-insensitive dos DNs
foreach ($groups as $group) {
    foreach ($roleMappings as $ldapGroup => $role) {
        if (strcasecmp($group, $ldapGroup) === 0) {  // ← IMPORTANTE
            $assignRoles[] = $role;
            break;
        }
    }
}
```

**Por quê:** DNs podem vir com case diferente (`CN` vs `cn`), causando falha no mapeamento.

### 5. Logs Detalhados de Depuração

**Mudança no `app/Models/User.php`:**
```php
\Log::info("Mapeamento de grupos LDAP para roles", [
    'user' => $this->email ?? $this->samaccountname,
    'ldap_groups' => $groups,
    'mapped_roles' => $assignRoles
]);
```

**Por quê:** Essencial para debug. Mostra exatamente quais grupos foram encontrados e quais roles foram atribuídas.

### 6. Campos Customizados no User Model

**Mudança na `Migration` e `app/Models/User.php`:**
```php
// ✅ Adicionamos campos LDAP específicos
$table->string('domain')->nullable();
$table->string('guid')->unique()->nullable();
$table->string('samaccountname')->unique()->nullable();

// ✅ Fillable inclui campos LDAP
protected $fillable = [
    'name', 'email', 'password', 'phone', 'bio',
    'domain', 'guid', 'samaccountname',  // ← LDAP
];
```

**Por quê:** `samaccountname` armazena o username original do LDAP.

### 7. Sincronização Apenas no Login (não em toda requisição)

**Mudança:** NÃO usamos middleware global `sync.ldap.groups` em todas as rotas.

```php
// ❌ Evitamos isso (muitas queries LDAP)
Route::middleware(['auth', 'sync.ldap.groups'])->group(function () {});

// ✅ Sincronizamos apenas no login
// Ver FortifyServiceProvider - authenticated event
```

**Por quê:** Sincronizar em toda requisição sobrecarrega o servidor LDAP.

---

## �📦 Instalação e Configuração

### 1. Instalar Pacotes Necessários

```bash
# Pacote LDAP para Laravel
composer require directorytree/ldaprecord-laravel

# Pacote de permissões (opcional, mas recomendado)
composer require spatie/laravel-permission
```

### 2. Publicar Configurações

```bash
# Publicar configuração do LDAP
php artisan vendor:publish --provider="LdapRecord\Laravel\LdapServiceProvider"

# Publicar configuração de permissões
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 3. Executar Migrations

```bash
# Criar tabelas de permissões
php artisan migrate

# Ou se precisar recriar tudo
php artisan migrate:fresh
```

---

## 📁 Estrutura de Arquivos

```
app/
├── Ldap/
│   └── User.php                          # Modelo LDAP
├── Models/
│   └── User.php                          # Modelo Eloquent (atualizado)
├── Http/
│   ├── Middleware/
│   │   └── SyncLdapGroups.php           # Middleware de sincronização
│   └── Controllers/
│       └── Auth/
│           └── LoginController.php       # Controller de login (se personalizado)
├── Providers/
│   ├── AuthServiceProvider.php          # Provider de autenticação
│   └── FortifyServiceProvider.php       # Provider Fortify (se usar)
config/
├── ldap.php                              # Configuração LDAP
├── auth.php                              # Configuração de autenticação
└── permission.php                        # Configuração de permissões
.env                                      # Variáveis de ambiente
```

---

## 🔧 Implementação Passo a Passo

### Passo 1: Configurar Variáveis de Ambiente

Adicione no arquivo `.env`:

```env
# Configurações LDAP/Active Directory
LDAP_LOGGING=true
LDAP_CONNECTION=default

# Informações do servidor AD
LDAP_HOST=192.168.1.100
LDAP_PORT=389
LDAP_BASE_DN="dc=empresa,dc=local"
LDAP_USERNAME="CN=ldap_user,CN=Users,DC=empresa,DC=local"
LDAP_PASSWORD="senha_segura_aqui"

# Timeout e SSL
LDAP_TIMEOUT=5
LDAP_SSL=false
LDAP_TLS=false

# Configuração de busca de usuários
LDAP_USER_BASE_DN="CN=Users,DC=empresa,DC=local"
LDAP_USER_FILTER="(&(objectClass=user)(objectCategory=person))"

# Mapeamento de atributos
LDAP_USER_ATTRIBUTE_USERNAME=samaccountname
LDAP_USER_ATTRIBUTE_EMAIL=mail
LDAP_USER_ATTRIBUTE_NAME=displayname
LDAP_USER_ATTRIBUTE_MEMBEROF=memberof

# Mapeamento de grupos (separados por vírgula)
# Formato: "CN do Grupo no AD:Nome da Role no Laravel"
LDAP_GROUP_MAPPING="CN=Administradores,CN=Users,DC=empresa,DC=local:Admin,CN=Desenvolvedores,CN=Users,DC=empresa,DC=local:Developer,CN=Usuarios,CN=Users,DC=empresa,DC=local:User"
```

### Passo 2: Configurar `config/ldap.php`

```php
<?php

return [
    'default' => env('LDAP_CONNECTION', 'default'),
    
    'connections' => [
        'default' => [
            'hosts' => [env('LDAP_HOST', '192.168.1.100')],
            'port' => env('LDAP_PORT', 389),
            'base_dn' => env('LDAP_BASE_DN', 'dc=empresa,dc=local'),
            'username' => env('LDAP_USERNAME', ''),
            'password' => env('LDAP_PASSWORD', ''),
            'timeout' => env('LDAP_TIMEOUT', 5),
            'use_ssl' => env('LDAP_SSL', false),
            'use_tls' => env('LDAP_TLS', false),
            
            'options' => [
                // Opções adicionais do LDAP
                LDAP_OPT_PROTOCOL_VERSION => 3,
                LDAP_OPT_REFERRALS => 0,
            ],
        ],
    ],
    
    'logging' => env('LDAP_LOGGING', true),
    
    'cache' => [
        'enabled' => env('LDAP_CACHE', false),
        'driver' => env('CACHE_DRIVER', 'file'),
    ],
];
```

### Passo 3: Criar Modelo LDAP

**Arquivo:** `app/Ldap/User.php`

```php
<?php

namespace App\Ldap;

use LdapRecord\Models\Model;

class User extends Model
{
    /**
     * The object classes of the LDAP model.
     */
    public static array $objectClasses = [
        'top',
        'person',
        'organizationalperson',
        'user',
    ];

    /**
     * The attributes that should be returned in queries.
     */
    protected array $attributes = [
        'cn',
        'samaccountname',
        'mail',
        'displayname',
        'memberof',
        'userprincipalname',
        'distinguishedname',
    ];

    /**
     * Get the user's email address.
     */
    public function getEmailAttribute(): ?string
    {
        return $this->getFirstAttribute('mail');
    }

    /**
     * Get the user's display name.
     */
    public function getDisplayNameAttribute(): ?string
    {
        return $this->getFirstAttribute('displayname') 
            ?? $this->getFirstAttribute('cn');
    }

    /**
     * Get the user's username (sAMAccountName).
     */
    public function getUsernameAttribute(): ?string
    {
        return $this->getFirstAttribute('samaccountname');
    }

    /**
     * Get the groups the user belongs to.
     */
    public function getGroupsAttribute(): array
    {
        $memberOf = $this->getFirstAttribute('memberof');
        
        if (is_string($memberOf)) {
            return [$memberOf];
        }
        
        return is_array($memberOf) ? $memberOf : [];
    }

    /**
     * Extract CN from a distinguished name.
     */
    public static function extractCN(string $dn): ?string
    {
        if (preg_match('/^CN=([^,]+)/i', $dn, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}
```

### Passo 4: Atualizar Modelo Eloquent User

**Arquivo:** `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements LdapAuthenticatable
{
    use HasFactory, Notifiable, AuthenticatesWithLdap, HasRoles;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'guid',           // GUID do LDAP
        'domain',         // Domínio do LDAP
        'username',       // sAMAccountName
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Sync LDAP groups to Laravel roles.
     */
    public function syncLdapGroups(array $ldapGroups): void
    {
        // Mapeamento de grupos LDAP para roles Laravel
        $groupMapping = $this->getLdapGroupMapping();
        
        $rolesToSync = [];
        
        foreach ($ldapGroups as $groupDN) {
            // Extrair CN do Distinguished Name
            if (preg_match('/^CN=([^,]+)/i', $groupDN, $matches)) {
                $groupCN = $matches[1];
                
                // Verificar se há mapeamento para este grupo
                foreach ($groupMapping as $ldapGroup => $laravelRole) {
                    if (stripos($ldapGroup, "CN={$groupCN}") === 0) {
                        $rolesToSync[] = $laravelRole;
                    }
                }
            }
        }
        
        // Sincronizar roles (remove antigas e adiciona novas)
        if (!empty($rolesToSync)) {
            $this->syncRoles($rolesToSync);
        }
    }

    /**
     * Get LDAP group to Laravel role mapping from env.
     */
    protected function getLdapGroupMapping(): array
    {
        $mapping = [];
        $envMapping = env('LDAP_GROUP_MAPPING', '');
        
        if (empty($envMapping)) {
            return $mapping;
        }
        
        $pairs = explode(',', $envMapping);
        
        foreach ($pairs as $pair) {
            $parts = explode(':', $pair);
            if (count($parts) === 2) {
                $mapping[trim($parts[0])] = trim($parts[1]);
            }
        }
        
        return $mapping;
    }

    /**
     * Create or update user from LDAP.
     */
    public static function createOrUpdateFromLdap(\App\Ldap\User $ldapUser): self
    {
        $eloquentUser = static::updateOrCreate(
            [
                'guid' => $ldapUser->getConvertedGuid(),
            ],
            [
                'name' => $ldapUser->displayname ?? $ldapUser->cn,
                'email' => $ldapUser->email ?? '',
                'username' => $ldapUser->username ?? '',
                'domain' => $ldapUser->getConnectionName(),
            ]
        );
        
        // Sincronizar grupos
        $eloquentUser->syncLdapGroups($ldapUser->groups);
        
        return $eloquentUser;
    }
}
```

### Passo 5: Criar Middleware de Sincronização

**Arquivo:** `app/Http/Middleware/SyncLdapGroups.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SyncLdapGroups
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se usuário está autenticado
        if ($request->user()) {
            $user = $request->user();
            
            // Verificar se usuário tem LDAP model associado
            if ($user->ldap) {
                // Buscar grupos atualizados do LDAP
                $ldapUser = $user->ldap;
                $ldapUser->refresh(); // Atualizar dados do LDAP
                
                // Sincronizar grupos
                $user->syncLdapGroups($ldapUser->groups ?? []);
            }
        }
        
        return $next($request);
    }
}
```

**Registrar middleware em:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        // ... outros middlewares
    ]);
    
    $middleware->alias([
        'sync.ldap.groups' => \App\Http\Middleware\SyncLdapGroups::class,
    ]);
})
```

### Passo 6: Configurar Autenticação

**Arquivo:** `config/auth.php`

```php
<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'ldap',
            'model' => App\Models\User::class,
            'rules' => [],
            'database' => [
                'model' => App\Models\User::class,
                'sync_passwords' => false,
                'sync_attributes' => [
                    'name' => 'displayname',
                    'email' => 'mail',
                    'username' => 'samaccountname',
                ],
                'sync_existing' => [
                    'guid' => 'objectguid',
                ],
            ],
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
```

### Passo 7: Configurar Laravel Fortify (Se usar)

**Arquivo:** `app/Providers/FortifyServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Custom login response
        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    return redirect()->intended(route('dashboard'));
                }
            };
        });

        // Custom logout response
        $this->app->singleton(LogoutResponse::class, function () {
            return new class implements LogoutResponse {
                public function toResponse($request)
                {
                    return redirect()->route('login');
                }
            };
        });
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Custom authentication logic
        Fortify::authenticateUsing(function (Request $request) {
            // Primeiro tenta autenticar via LDAP
            if (config('ldap.enabled', true)) {
                try {
                    $credentials = [
                        'samaccountname' => $request->email,
                        'password' => $request->password,
                    ];

                    if (auth()->attempt($credentials, $request->boolean('remember'))) {
                        return auth()->user();
                    }
                } catch (\Exception $e) {
                    \Log::error('LDAP authentication failed: ' . $e->getMessage());
                }
            }

            // Fallback para autenticação local (opcional)
            $credentials = [
                'email' => $request->email,
                'password' => $request->password,
            ];

            if (auth()->attempt($credentials, $request->boolean('remember'))) {
                return auth()->user();
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        Fortify::loginView(fn () => inertia('auth/Login'));
    }
}
```

### Passo 8: Adicionar Colunas no Banco de Dados

Criar migration para adicionar colunas LDAP:

```bash
php artisan make:migration add_ldap_columns_to_users_table
```

**Conteúdo da migration:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('guid')->unique()->nullable()->after('id');
            $table->string('domain')->nullable()->after('guid');
            $table->string('username')->nullable()->after('domain');
            
            // Tornar password nullable para usuários LDAP
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['guid', 'domain', 'username']);
        });
    }
};
```

Executar migration:

```bash
php artisan migrate
```

---

## 🔄 Sincronização de Grupos/Roles

### Mapeamento de Grupos

O mapeamento é feito via variável de ambiente `LDAP_GROUP_MAPPING`:

```env
LDAP_GROUP_MAPPING="CN=Administradores,CN=Users,DC=empresa,DC=local:Admin,CN=Desenvolvedores,CN=Users,DC=empresa,DC=local:Developer"
```

**Formato:** `Distinguished Name do Grupo no AD:Nome da Role no Laravel`

### Criar Roles no Laravel

```bash
php artisan tinker
```

```php
use Spatie\Permission\Models\Role;

// Criar roles
Role::create(['name' => 'Admin']);
Role::create(['name' => 'Developer']);
Role::create(['name' => 'Manager']);
Role::create(['name' => 'User']);
```

Ou criar um seeder:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Criar roles
        $roles = ['Admin', 'Developer', 'Manager', 'User'];
        
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }
}
```

### Como Funciona a Sincronização

1. **No Login:**
   - Usuário autentica via LDAP
   - Sistema busca grupos do AD que o usuário pertence
   - Compara com o mapeamento configurado
   - Sincroniza roles no Laravel

2. **Durante a Sessão:**
   - Middleware `sync.ldap.groups` executa em cada requisição
   - Atualiza roles se houver mudanças no AD

3. **Exemplo de Fluxo:**
```
Usuário no AD: john.doe
Grupos no AD: 
  - CN=Administradores,CN=Users,DC=empresa,DC=local
  - CN=Desenvolvedores,CN=Users,DC=empresa,DC=local

Mapeamento:
  CN=Administradores... -> Admin
  CN=Desenvolvedores... -> Developer

Resultado no Laravel:
  john.doe terá roles: [Admin, Developer]
```

---

## 🔍 Troubleshooting

### Problema: Não consegue conectar ao LDAP

**Solução:**
```bash
# Testar conexão
php artisan ldap:test

# Verificar logs
tail -f storage/logs/laravel.log

# Verificar se porta está aberta
telnet 192.168.1.100 389
```

### Problema: Usuários não são criados automaticamente

**Verificar:**
1. `config/auth.php` - provider deve ser 'ldap'
2. `config/ldap.php` - conexão configurada corretamente
3. Modelo User implementa `LdapAuthenticatable`
4. Migration executada com colunas LDAP

### Problema: Grupos não sincronizam

**Verificar:**
1. `LDAP_GROUP_MAPPING` está configurado corretamente
2. Distinguished Names estão completos e corretos
3. Roles existem no banco de dados Laravel
4. Middleware `sync.ldap.groups` está registrado
5. Logs: `storage/logs/laravel.log`

### Problema: Autenticação lenta

**Otimizações:**
1. Habilitar cache LDAP:
```env
LDAP_CACHE=true
```

2. Reduzir timeout:
```env
LDAP_TIMEOUT=3
```

3. Limitar sincronização apenas no login:
```php
// Remover middleware de todas as rotas
// Adicionar apenas no login
```

---

## ✅ Boas Práticas

### 1. Segurança

- ✅ Use LDAPS (porta 636) em produção
- ✅ Nunca versione credenciais no Git
- ✅ Use usuário de serviço com permissões mínimas
- ✅ Implemente rate limiting no login
- ✅ Registre tentativas de autenticação

### 2. Performance

- ✅ Habilite cache para consultas LDAP
- ✅ Limite sincronização de grupos
- ✅ Use indexes no banco de dados (guid, username)
- ✅ Implemente timeout adequado

### 3. Manutenção

- ✅ Documente mapeamento de grupos
- ✅ Crie seeders para roles
- ✅ Implemente logs detalhados
- ✅ Teste regularmente a conexão
- ✅ Mantenha backup das configurações

### 4. Testes

```php
// Testar conexão LDAP
php artisan ldap:test

// Testar autenticação
php artisan tinker
>>> auth()->attempt(['samaccountname' => 'usuario', 'password' => 'senha'])

// Verificar grupos de um usuário
>>> $user = User::find(1);
>>> $user->ldap->groups;
>>> $user->roles->pluck('name');
```

### 5. Monitoramento

Adicione logs em pontos críticos:

```php
use Illuminate\Support\Facades\Log;

// No middleware
Log::info('LDAP sync started', ['user_id' => $user->id]);

// No modelo
Log::info('User synced from LDAP', [
    'user_id' => $user->id,
    'roles' => $user->roles->pluck('name'),
]);

// Em exceções
Log::error('LDAP connection failed', [
    'error' => $e->getMessage(),
    'host' => config('ldap.connections.default.hosts'),
]);
```

---

## 📚 Recursos Adicionais

- **LdapRecord Laravel:** https://ldaprecord.com/docs/laravel/v3
- **Spatie Permission:** https://spatie.be/docs/laravel-permission/v6
- **Laravel Fortify:** https://laravel.com/docs/11.x/fortify
- **Active Directory:** https://learn.microsoft.com/en-us/windows-server/identity/ad-ds/

---

## 📝 CHECKLIST DE IMPLEMENTAÇÃO COMPLETA

Use esta lista para garantir que implementou tudo corretamente:

### Instalação Base
- [ ] `composer require directorytree/ldaprecord-laravel`
- [ ] `composer require spatie/laravel-permission`
- [ ] `php artisan vendor:publish --provider="LdapRecord\Laravel\LdapServiceProvider"`
- [ ] `php artisan migrate`

### Configuração
- [ ] Variáveis `.env` configuradas (LDAP_HOST, LDAP_USERNAME, etc.)
- [ ] `config/ldap.php` ajustado para seu servidor
- [ ] `config/auth.php` - provider alterado para 'ldap'

### Modelos
- [ ] `app/Ldap/User.php` criado
- [ ] `app/Ldap/User.php` - objectClasses corretos (OpenLDAP vs AD)
- [ ] `app/Ldap/User.php` - guidKey correto (entryuuid vs objectguid)
- [ ] `app/Ldap/User.php` - método `getGroups()` implementado
- [ ] `app/Models/User.php` - implements `LdapAuthenticatable`
- [ ] `app/Models/User.php` - trait `AuthenticatesWithLdap`
- [ ] `app/Models/User.php` - método `syncRolesFromLdap()` implementado
- [ ] `app/Models/User.php` - fillable com campos LDAP

### Banco de Dados
- [ ] Migration com colunas: `domain`, `guid`, `samaccountname`
- [ ] Migration executada: `php artisan migrate`
- [ ] Seeder de roles criado e executado

### Autenticação
- [ ] `app/Providers/FortifyServiceProvider.php` customizado
- [ ] Login aceita username ou email
- [ ] Sincronização de grupos no login implementada

### Middleware (Opcional)
- [ ] `app/Http/Middleware/SyncLdapGroups.php` criado
- [ ] Middleware registrado em `bootstrap/app.php`
- [ ] Middleware aplicado em rotas específicas

### Testes
- [ ] `php artisan ldap:test` funciona
- [ ] Login com usuário LDAP funciona
- [ ] Grupos são sincronizados corretamente
- [ ] Roles aparecem no usuário após login
- [ ] Logs mostram sincronização: `tail -f storage/logs/laravel.log`

---

## 🎯 RESUMO EXECUTIVO: MUDANÇAS CRÍTICAS DO DEVNITY

**Se você está replicando este código em outro projeto, estas são as mudanças OBRIGATÓRIAS que fizemos:**

### 1. Escolha: OpenLDAP ou Active Directory?

**OpenLDAP (nosso caso):**
```php
// app/Ldap/User.php
public static array $objectClasses = [
    'top', 'person', 'organizationalperson', 'inetorgperson'
];
protected string $guidKey = 'entryuuid';  // NÃO objectguid

// Implementar busca manual de grupos (OpenLDAP não tem memberOf)
public function getGroups(): array {
    // Buscar em posixGroup e groupOfNames
    // Ver código completo no arquivo
}
```

**Active Directory (se for o seu caso):**
```php
// app/Ldap/User.php
public static array $objectClasses = ['user'];
protected string $guidKey = 'objectguid';  // NÃO entryuuid

// Usar atributo memberOf diretamente
public function getGroups(): array {
    return $this->getAttribute('memberof') ?? [];
}
```

### 2. Campo de Login (CRÍTICO)

**No Devnity usamos username (uid):**
```php
// app/Providers/FortifyServiceProvider.php
$loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'uid';
$credentials[$loginField] = $request->email;
```

**Se usar Active Directory com samaccountname:**
```php
$credentials['samaccountname'] = $request->email;
```

**Se usar apenas email:**
```php
$credentials['email'] = $request->email;
```

### 3. Mapeamento de Grupos (SUPER IMPORTANTE)

**✅ SEMPRE use DN completo e comparação case-insensitive:**

```php
// app/Models/User.php
public function syncRolesFromLdap(array $groups): void
{
    $roleMappings = [
        env('LDAP_ADMIN_GROUP', 'cn=Administradores,ou=groups,dc=devnity,dc=com,dc=br') => 'admin',
        // ...
    ];

    foreach ($groups as $group) {
        foreach ($roleMappings as $ldapGroup => $role) {
            // CRÍTICO: usar strcasecmp para case-insensitive
            if (strcasecmp($group, $ldapGroup) === 0) {
                $assignRoles[] = $role;
                break;
            }
        }
    }
}
```

**❌ ERRO COMUM:**
```php
// NÃO FAÇA ISSO - não funciona por causa do case
if ($group === $ldapGroup) { }

// NÃO USE GRUPO CURTO - precisa ser DN completo
LDAP_ADMIN_GROUP="Administradores"  // ❌ Errado
```

### 4. Sincronização de Grupos - Performance

**❌ EVITE fazer em toda requisição:**
```php
// Isso gera centenas de queries LDAP por dia
Route::middleware(['auth', 'sync.ldap.groups'])->group(function () {
    // todas as rotas
});
```

**✅ FAÇA apenas no login:**
```php
// No FortifyServiceProvider ou no evento 'authenticated'
// Sincronizar apenas quando usuário faz login
```

### 5. Logs Detalhados (ESSENCIAL para debug)

**Adicione logs na sincronização:**
```php
// app/Models/User.php
\Log::info("Mapeamento de grupos LDAP para roles", [
    'user' => $this->email ?? $this->samaccountname,
    'ldap_groups' => $groups,              // Ver grupos que vieram
    'mapped_roles' => $assignRoles         // Ver roles que foram atribuídas
]);
```

**Monitore durante implementação:**
```bash
# Windows PowerShell
Get-Content storage/logs/laravel.log -Wait | Select-String -Pattern "ldap" -CaseSensitive:$false

# Linux/Mac
tail -f storage/logs/laravel.log | grep -i ldap
```

### 6. Campos LDAP no User Model

**Migration obrigatória:**
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('domain')->nullable();           // Identifica usuário LDAP
    $table->string('guid')->unique()->nullable();   // GUID do LDAP
    $table->string('samaccountname')->unique()->nullable(); // Username original
});
```

**Fillable no modelo:**
```php
protected $fillable = [
    'name', 'email', 'password', 'phone', 'bio',
    'domain', 'guid', 'samaccountname',  // ← Campos LDAP
];
```

---

## 🔗 ARQUIVOS EXATOS DO DEVNITY

Estas são as referências exatas dos arquivos que implementamos:

| Arquivo | Propósito | Mudanças Principais |
|---------|-----------|---------------------|
| `app/Ldap/User.php` | Modelo LDAP | objectClasses OpenLDAP, guidKey entryuuid, getGroups() manual |
| `app/Models/User.php` | Modelo Eloquent | AuthenticatesWithLdap, syncRolesFromLdap(), fillable LDAP |
| `config/auth.php` | Configuração Auth | Provider 'ldap', sync_attributes |
| `config/ldap.php` | Configuração LDAP | Conexão default, timeouts, opções |
| `app/Providers/FortifyServiceProvider.php` | Autenticação | Detecção uid/email, autenticação customizada |
| `app/Http/Middleware/SyncLdapGroups.php` | Sincronização | Middleware opcional (não usamos globalmente) |
| `database/migrations/*_add_ldap_columns_to_users_table.php` | Migration | Colunas: domain, guid, samaccountname |

---

**Desenvolvido por:** Devnity Team  
**Última atualização:** 23 de outubro de 2025  
**Versão:** 1.0.0
