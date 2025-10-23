# Guia Avançado: LDAP/AD - Troubleshooting e Casos de Uso

Este documento complementa o guia principal com soluções para problemas comuns, casos de uso avançados e otimizações.

## 📋 Índice

1. [Diagnóstico de Problemas](#diagnóstico-de-problemas)
2. [Casos de Uso Avançados](#casos-de-uso-avançados)
3. [Múltiplos Domínios](#múltiplos-domínios)
4. [Performance e Otimização](#performance-e-otimização)
5. [Testes Automatizados](#testes-automatizados)
6. [FAQ](#faq)

---

## 🔍 Diagnóstico de Problemas

### Ferramentas de Diagnóstico

#### 1. Comando Artisan para Testar LDAP

```bash
php artisan ldap:test
```

**Saída esperada:**
```
Connection [default]:
✔ Connected successfully
✔ Bind successful
✔ Search successful
```

#### 2. Script de Teste Manual

Criar arquivo `test-ldap.php` na raiz do projeto:

```php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use LdapRecord\Connection;
use App\Ldap\User as LdapUser;

try {
    // Testar conexão
    $connection = new Connection(config('ldap.connections.default'));
    $connection->connect();
    echo "✅ Conexão estabelecida com sucesso!\n\n";
    
    // Testar bind
    if ($connection->auth()->attempt(
        config('ldap.connections.default.username'),
        config('ldap.connections.default.password')
    )) {
        echo "✅ Autenticação (bind) bem-sucedida!\n\n";
    } else {
        echo "❌ Falha na autenticação (bind)\n\n";
        exit(1);
    }
    
    // Testar busca de usuários
    $users = LdapUser::get()->take(5);
    echo "✅ Encontrados " . $users->count() . " usuários:\n";
    
    foreach ($users as $user) {
        echo "  - {$user->displayname} ({$user->username})\n";
        echo "    Email: {$user->email}\n";
        echo "    Grupos: " . count($user->groups) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
```

Execute:
```bash
php test-ldap.php
```

#### 3. Verificar Conectividade de Rede

```bash
# Windows
Test-NetConnection -ComputerName 192.168.1.100 -Port 389

# Linux/Mac
nc -zv 192.168.1.100 389
telnet 192.168.1.100 389
```

#### 4. Query LDAP Direta

```bash
# Windows (ldp.exe)
# Linux
ldapsearch -x -H ldap://192.168.1.100 -D "CN=ldap_user,CN=Users,DC=empresa,DC=local" -w "senha" -b "CN=Users,DC=empresa,DC=local" "(objectClass=user)"
```

---

## 🔧 Soluções para Problemas Comuns

### Erro: "Unable to connect to LDAP server"

**Causas possíveis:**

1. **Firewall bloqueando porta 389/636**
```bash
# Verificar se porta está aberta
telnet 192.168.1.100 389
```

**Solução:** Liberar porta no firewall

2. **Host incorreto**
```env
# Verificar se host está correto
LDAP_HOST=192.168.1.100  # ou nome.dominio.local
```

3. **SSL/TLS mal configurado**
```env
# Para testes, desabilitar SSL
LDAP_SSL=false
LDAP_TLS=false
```

### Erro: "Invalid credentials"

**Causas:**

1. **Username incorreto**
```env
# Distinguished Name completo é necessário
LDAP_USERNAME="CN=ldap_service,CN=Users,DC=empresa,DC=local"
# Não funciona: LDAP_USERNAME="ldap_service"
```

2. **Password com caracteres especiais**
```env
# Use aspas duplas
LDAP_PASSWORD="S3nh@C0mpl3x@123"
```

3. **Conta bloqueada/expirada no AD**
```bash
# Verificar no servidor AD se conta está ativa
```

### Erro: "No users found"

**Soluções:**

1. **Base DN incorreto**
```env
# Base DN deve apontar para onde os usuários estão
LDAP_BASE_DN="CN=Users,DC=empresa,DC=local"
# ou
LDAP_BASE_DN="OU=Usuarios,DC=empresa,DC=local"
```

2. **Filtro muito restritivo**
```php
// Em app/Ldap/User.php, simplifique o filtro
public static function query()
{
    return parent::query()
        ->where('objectclass', '=', 'user')
        ->where('objectcategory', '=', 'person');
}
```

3. **Permissões insuficientes**
```
Usuário de serviço precisa permissão de leitura no AD
```

### Erro: "Groups not syncing"

**Debug passo a passo:**

1. **Verificar se usuário tem grupos no AD**
```php
// Em tinker
$user = App\Models\User::find(1);
dd($user->ldap->groups);
```

2. **Verificar mapeamento**
```php
// Em tinker
dd(env('LDAP_GROUP_MAPPING'));
```

3. **Verificar se roles existem**
```php
// Em tinker
Spatie\Permission\Models\Role::all()->pluck('name');
```

4. **Adicionar logs na sincronização**
```php
// Em app/Models/User.php -> syncLdapGroups()
\Log::info('Syncing groups', [
    'ldap_groups' => $ldapGroups,
    'mapping' => $this->getLdapGroupMapping(),
    'roles_to_sync' => $rolesToSync,
]);
```

### Erro: "Authentication works but user not created"

**Verificar:**

1. **Migration executada**
```bash
php artisan migrate:status
```

2. **Colunas LDAP existem**
```bash
php artisan tinker
>>> Schema::hasColumn('users', 'guid')
>>> Schema::hasColumn('users', 'username')
```

3. **Provider configurado corretamente**
```php
// Em config/auth.php
'providers' => [
    'users' => [
        'driver' => 'ldap',  // Deve ser 'ldap', não 'eloquent'
        'model' => App\Models\User::class,
    ],
],
```

---

## 🚀 Casos de Uso Avançados

### 1. Sincronização Seletiva (Apenas Alguns Usuários)

**Cenário:** Nem todos os usuários do AD devem ter acesso ao sistema.

**Solução 1: Filtrar por grupo específico**

```php
// Em app/Ldap/User.php
public static function query()
{
    return parent::query()
        ->where('objectclass', '=', 'user')
        ->where('objectcategory', '=', 'person')
        ->where('memberof', 'contains', 'CN=Sistema_Usuarios,CN=Users,DC=empresa,DC=local');
}
```

**Solução 2: Filtrar por OU (Organizational Unit)**

```env
# Base DN apontando para OU específica
LDAP_BASE_DN="OU=Colaboradores,DC=empresa,DC=local"
```

**Solução 3: Filtrar programaticamente**

```php
// Em FortifyServiceProvider.php
Fortify::authenticateUsing(function (Request $request) {
    $credentials = [
        'samaccountname' => $request->email,
        'password' => $request->password,
    ];

    if (auth()->attempt($credentials)) {
        $user = auth()->user();
        
        // Verificar se usuário tem grupo permitido
        $allowedGroups = [
            'CN=Administradores,CN=Users,DC=empresa,DC=local',
            'CN=Usuarios_Sistema,CN=Users,DC=empresa,DC=local',
        ];
        
        $userGroups = $user->ldap->groups ?? [];
        $hasAccess = collect($userGroups)->intersect($allowedGroups)->isNotEmpty();
        
        if (!$hasAccess) {
            auth()->logout();
            throw new \Exception('Você não tem permissão para acessar este sistema.');
        }
        
        return $user;
    }

    return null;
});
```

### 2. Múltiplos Domínios/Florestas

**Cenário:** Empresa tem múltiplos domínios AD.

**Configuração:**

```php
// Em config/ldap.php
return [
    'default' => env('LDAP_CONNECTION', 'primary'),
    
    'connections' => [
        'primary' => [
            'hosts' => [env('LDAP_PRIMARY_HOST', '192.168.1.100')],
            'base_dn' => env('LDAP_PRIMARY_BASE_DN', 'dc=empresa,dc=local'),
            'username' => env('LDAP_PRIMARY_USERNAME'),
            'password' => env('LDAP_PRIMARY_PASSWORD'),
            // ...
        ],
        
        'secondary' => [
            'hosts' => [env('LDAP_SECONDARY_HOST', '192.168.2.100')],
            'base_dn' => env('LDAP_SECONDARY_BASE_DN', 'dc=filial,dc=local'),
            'username' => env('LDAP_SECONDARY_USERNAME'),
            'password' => env('LDAP_SECONDARY_PASSWORD'),
            // ...
        ],
    ],
];
```

**Autenticação em múltiplos domínios:**

```php
// Em FortifyServiceProvider.php
Fortify::authenticateUsing(function (Request $request) {
    $connections = ['primary', 'secondary'];
    
    foreach ($connections as $connection) {
        config(['ldap.default' => $connection]);
        
        $credentials = [
            'samaccountname' => $request->email,
            'password' => $request->password,
        ];
        
        if (auth()->attempt($credentials)) {
            $user = auth()->user();
            $user->update(['domain' => $connection]);
            return $user;
        }
    }
    
    return null;
});
```

### 3. Sincronização de Atributos Adicionais

**Cenário:** Precisa sincronizar telefone, departamento, etc.

**Passo 1: Adicionar colunas na migration**

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('phone')->nullable();
    $table->string('department')->nullable();
    $table->string('job_title')->nullable();
    $table->string('manager')->nullable();
});
```

**Passo 2: Atualizar modelo LDAP**

```php
// Em app/Ldap/User.php
protected array $attributes = [
    'cn',
    'samaccountname',
    'mail',
    'displayname',
    'memberof',
    'telephonenumber',
    'department',
    'title',
    'manager',
];
```

**Passo 3: Configurar sincronização**

```php
// Em config/auth.php
'database' => [
    'model' => App\Models\User::class,
    'sync_passwords' => false,
    'sync_attributes' => [
        'name' => 'displayname',
        'email' => 'mail',
        'username' => 'samaccountname',
        'phone' => 'telephonenumber',
        'department' => 'department',
        'job_title' => 'title',
    ],
    // ...
],
```

### 4. Cache de Queries LDAP

**Cenário:** Melhorar performance em consultas frequentes.

```php
// Em app/Ldap/User.php
use Illuminate\Support\Facades\Cache;

public static function findByUsername(string $username)
{
    return Cache::remember(
        "ldap.user.{$username}",
        now()->addMinutes(30),
        function () use ($username) {
            return static::query()
                ->where('samaccountname', '=', $username)
                ->first();
        }
    );
}

// Limpar cache quando necessário
public static function clearUserCache(string $username): void
{
    Cache::forget("ldap.user.{$username}");
}
```

### 5. Importação em Massa de Usuários

**Criar comando Artisan:**

```bash
php artisan make:command SyncLdapUsers
```

**Conteúdo do comando:**

```php
<?php

namespace App\Console\Commands;

use App\Ldap\User as LdapUser;
use App\Models\User;
use Illuminate\Console\Command;

class SyncLdapUsers extends Command
{
    protected $signature = 'ldap:sync-users {--limit=100}';
    protected $description = 'Sync users from LDAP to database';

    public function handle()
    {
        $this->info('Starting LDAP user sync...');
        
        $limit = $this->option('limit');
        $ldapUsers = LdapUser::get()->take($limit);
        
        $bar = $this->output->createProgressBar($ldapUsers->count());
        $bar->start();
        
        $synced = 0;
        $errors = 0;
        
        foreach ($ldapUsers as $ldapUser) {
            try {
                User::createOrUpdateFromLdap($ldapUser);
                $synced++;
            } catch (\Exception $e) {
                $errors++;
                $this->error("\nError syncing {$ldapUser->username}: {$e->getMessage()}");
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        
        $this->newLine(2);
        $this->info("Sync completed!");
        $this->info("Synced: {$synced}");
        $this->info("Errors: {$errors}");
    }
}
```

**Uso:**

```bash
# Sincronizar até 100 usuários
php artisan ldap:sync-users

# Sincronizar até 500 usuários
php artisan ldap:sync-users --limit=500
```

---

## ⚡ Performance e Otimização

### 1. Indexes no Banco de Dados

```php
// Em migration
Schema::table('users', function (Blueprint $table) {
    $table->index('guid');
    $table->index('username');
    $table->index(['email', 'domain']);
});
```

### 2. Lazy Loading vs Eager Loading

```php
// ❌ Ruim (N+1 queries)
$users = User::all();
foreach ($users as $user) {
    echo $user->ldap->displayname;
}

// ✅ Bom
$users = User::with('ldap')->get();
foreach ($users as $user) {
    echo $user->ldap->displayname;
}
```

### 3. Limitar Sincronização de Grupos

```php
// Sincronizar apenas no login, não em toda requisição
// Remover middleware sync.ldap.groups de rotas gerais
// Adicionar apenas em LoginController
```

### 4. Connection Pooling

```php
// Em config/ldap.php
'options' => [
    LDAP_OPT_PROTOCOL_VERSION => 3,
    LDAP_OPT_REFERRALS => 0,
    LDAP_OPT_NETWORK_TIMEOUT => 3,
],
```

### 5. Queue para Sincronização

```php
// Criar job
php artisan make:job SyncUserLdapData

// Job
class SyncUserLdapData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function handle(): void
    {
        if ($this->user->ldap) {
            $ldapUser = $this->user->ldap;
            $ldapUser->refresh();
            
            $this->user->syncLdapGroups($ldapUser->groups ?? []);
        }
    }
}

// Dispatch no login
SyncUserLdapData::dispatch($user);
```

---

## 🧪 Testes Automatizados

### Setup de Testes

```php
// tests/Feature/LdapAuthenticationTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LdapAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_ldap_credentials(): void
    {
        // Mock LDAP connection
        // ...

        $response = $this->post('/login', [
            'email' => 'testuser',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_ldap_groups_sync_correctly(): void
    {
        // ...
    }
}
```

---

## ❓ FAQ

### Q: Posso usar autenticação local como fallback?

**R:** Sim, configure no FortifyServiceProvider:

```php
Fortify::authenticateUsing(function (Request $request) {
    // Tenta LDAP primeiro
    // Se falhar, tenta local
    // Ver exemplo completo no guia principal
});
```

### Q: Como desabilitar LDAP temporariamente?

**R:** Use variável de ambiente:

```env
LDAP_ENABLED=false
```

```php
// Em FortifyServiceProvider
if (config('ldap.enabled', true)) {
    // Lógica LDAP
}
```

### Q: Posso ter usuários locais e LDAP simultaneamente?

**R:** Sim, usando coluna 'domain':

```php
// Usuários LDAP têm domain preenchido
// Usuários locais têm domain = null
```

### Q: Como resetar senha de usuário LDAP?

**R:** Não é possível pela aplicação. Usuário deve resetar no AD.

```php
// Desabilitar reset para usuários LDAP
if ($user->domain) {
    throw new \Exception('Usuários do AD devem resetar senha no sistema corporativo.');
}
```

---

## 📝 Logs Úteis

### Exemplo de Log Bem Estruturado

```php
// Em app/Models/User.php
public function syncLdapGroups(array $ldapGroups): void
{
    $startTime = microtime(true);
    
    Log::info('LDAP group sync started', [
        'user_id' => $this->id,
        'user_email' => $this->email,
        'ldap_groups_count' => count($ldapGroups),
    ]);
    
    try {
        // ... lógica de sincronização ...
        
        Log::info('LDAP group sync completed', [
            'user_id' => $this->id,
            'roles_synced' => $rolesToSync,
            'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
        ]);
    } catch (\Exception $e) {
        Log::error('LDAP group sync failed', [
            'user_id' => $this->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        throw $e;
    }
}
```

---

**Desenvolvido por:** Devnity Team  
**Última atualização:** 23 de outubro de 2025  
**Versão:** 1.0.0
