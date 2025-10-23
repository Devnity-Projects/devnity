# Guia de Integração LDAP/Active Directory

## Passo 1: Instalar Dependências

```bash
composer require directorytree/ldaprecord-laravel
```

## Passo 2: Publicar Configurações

```bash
php artisan vendor:publish --provider="LdapRecord\Laravel\LdapServiceProvider"
```

## Passo 3: Configurar .env

Adicione as configurações do seu servidor AD:

```env
# LDAP Configuration
LDAP_LOGGING=true
LDAP_CONNECTION=default
LDAP_HOST=ldap://seu-servidor-ad.dominio.local
LDAP_PORT=389
LDAP_BASE_DN="DC=dominio,DC=local"
LDAP_USERNAME="CN=Service Account,CN=Users,DC=dominio,DC=local"
LDAP_PASSWORD="senha-service-account"
LDAP_USE_SSL=false
LDAP_USE_TLS=false

# Mapeamento de grupos AD para Roles
LDAP_ADMIN_GROUP="CN=Administradores,CN=Users,DC=dominio,DC=local"
LDAP_DEVELOPER_GROUP="CN=Desenvolvedores,CN=Users,DC=dominio,DC=local"
LDAP_MANAGER_GROUP="CN=Gerentes,CN=Users,DC=dominio,DC=local"
```

## Passo 4: Configurar config/ldap.php

```php
<?php

return [
    'default' => env('LDAP_CONNECTION', 'default'),

    'connections' => [
        'default' => [
            'hosts' => [env('LDAP_HOST', '127.0.0.1')],
            'username' => env('LDAP_USERNAME', 'cn=user,dc=local,dc=com'),
            'password' => env('LDAP_PASSWORD', 'secret'),
            'port' => env('LDAP_PORT', 389),
            'base_dn' => env('LDAP_BASE_DN', 'dc=local,dc=com'),
            'timeout' => env('LDAP_TIMEOUT', 5),
            'use_ssl' => env('LDAP_SSL', false),
            'use_tls' => env('LDAP_TLS', false),
            'options' => [
                LDAP_OPT_PROTOCOL_VERSION => 3,
                LDAP_OPT_REFERRALS => 0,
            ],
        ],
    ],
];
```

## Passo 5: Criar Model LDAP

```bash
php artisan make:ldap-model User
```

Arquivo: `app/Ldap/User.php`

```php
<?php

namespace App\Ldap;

use LdapRecord\Models\Model;

class User extends Model
{
    public static $objectClasses = [
        'top',
        'person',
        'organizationalperson',
        'user',
    ];

    protected $guidKey = 'objectguid';

    public function getGroups()
    {
        return $this->getAttribute('memberof');
    }
}
```

## Passo 6: Atualizar Model User

Arquivo: `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;

class User extends Authenticatable implements LdapAuthenticatable
{
    use HasFactory, Notifiable, HasRoles, AuthenticatesWithLdap;

    protected $fillable = [
        'name',
        'email',
        'password',
        'domain',
        'guid',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'guid',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Sincroniza roles baseado em grupos AD
     */
    public function syncRolesFromLdap(array $groups): void
    {
        $roleMappings = [
            env('LDAP_ADMIN_GROUP') => 'admin',
            env('LDAP_DEVELOPER_GROUP') => 'developer',
            env('LDAP_MANAGER_GROUP') => 'manager',
            'CN=Suporte,CN=Users,DC=dominio,DC=local' => 'support',
            'CN=Financeiro,CN=Users,DC=dominio,DC=local' => 'financial',
        ];

        $assignRoles = [];

        foreach ($groups as $group) {
            if (isset($roleMappings[$group])) {
                $assignRoles[] = $roleMappings[$group];
            }
        }

        if (!empty($assignRoles)) {
            $this->syncRoles($assignRoles);
        } else {
            // Role padrão se não encontrar grupo correspondente
            $this->assignRole('developer');
        }
    }
}
```

## Passo 7: Configurar Autenticação

Arquivo: `config/auth.php`

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'ldap',
        'model' => App\Ldap\User::class,
        'rules' => [],
        'database' => [
            'model' => App\Models\User::class,
            'sync_passwords' => false,
            'sync_attributes' => [
                'name' => 'cn',
                'email' => 'mail',
            ],
            'sync_existing' => [
                'email' => 'mail',
            ],
        ],
    ],
],
```

## Passo 8: Criar Middleware de Sincronização

```bash
php artisan make:middleware SyncLdapGroups
```

Arquivo: `app/Http/Middleware/SyncLdapGroups.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use LdapRecord\Container;

class SyncLdapGroups
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->domain) {
            // Usuário autenticado via LDAP
            try {
                $ldap = Container::getDefaultConnection();
                $ldapUser = \App\Ldap\User::findByGuid($user->guid);

                if ($ldapUser) {
                    $groups = $ldapUser->getGroups() ?? [];
                    $user->syncRolesFromLdap($groups);
                }
            } catch (\Exception $e) {
                \Log::warning('Erro ao sincronizar grupos LDAP: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
}
```

## Passo 9: Registrar Middleware

Arquivo: `bootstrap/app.php`

```php
$middleware->alias([
    'ensure.user.settings' => EnsureUserSettings::class,
    'sync.ldap.groups' => \App\Http\Middleware\SyncLdapGroups::class,
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
]);
```

Adicione nas rotas web:

```php
Route::middleware(['auth', 'sync.ldap.groups'])->group(function () {
    // Suas rotas
});
```

## Passo 10: Criar Migration para LDAP

```bash
php artisan make:migration add_ldap_columns_to_users_table
```

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
            $table->string('domain')->nullable()->after('email');
            $table->string('guid')->unique()->nullable()->after('domain');
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['domain', 'guid']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
```

## Passo 11: Testar Conexão LDAP

Criar comando de teste:

```bash
php artisan make:command TestLdap
```

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LdapRecord\Container;

class TestLdap extends Command
{
    protected $signature = 'ldap:test {username}';
    protected $description = 'Testa conexão LDAP e busca usuário';

    public function handle()
    {
        $username = $this->argument('username');

        try {
            $connection = Container::getDefaultConnection();
            
            $this->info('Conectando ao LDAP...');
            $connection->connect();
            
            $this->info('✓ Conexão estabelecida!');
            
            $user = \App\Ldap\User::where('samaccountname', '=', $username)->first();
            
            if ($user) {
                $this->info('✓ Usuário encontrado:');
                $this->line('Nome: ' . $user->getFirstAttribute('cn'));
                $this->line('Email: ' . $user->getFirstAttribute('mail'));
                
                $groups = $user->getGroups() ?? [];
                $this->info('Grupos:');
                foreach ($groups as $group) {
                    $this->line('  - ' . $group);
                }
            } else {
                $this->error('✗ Usuário não encontrado');
            }
            
        } catch (\Exception $e) {
            $this->error('✗ Erro: ' . $e->getMessage());
        }
    }
}
```

Testar:
```bash
php artisan ldap:test nome.usuario
```

## Passo 12: Login Misto (LDAP e Local)

Para permitir login LDAP e local simultâneo, modifique o `LoginController`:

```php
public function authenticate(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Tentar autenticar via LDAP primeiro
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        
        // Sincronizar grupos se for usuário LDAP
        if (auth()->user()->domain) {
            $this->syncLdapGroups(auth()->user());
        }
        
        return redirect()->intended('dashboard');
    }

    return back()->withErrors([
        'email' => 'Credenciais inválidas.',
    ])->onlyInput('email');
}

private function syncLdapGroups($user)
{
    try {
        $ldapUser = \App\Ldap\User::findByGuid($user->guid);
        if ($ldapUser) {
            $groups = $ldapUser->getGroups() ?? [];
            $user->syncRolesFromLdap($groups);
        }
    } catch (\Exception $e) {
        \Log::warning('Erro ao sincronizar grupos: ' . $e->getMessage());
    }
}
```

## Troubleshooting

### Erro de Conexão
- Verificar firewall entre servidor e AD
- Testar com `telnet seu-servidor-ad.local 389`
- Verificar credenciais da service account

### Usuários não encontrados
- Verificar Base DN correto
- Service account precisa ter permissão de leitura
- Verificar filtros de busca

### Grupos não sincronizando
- Verificar atributo `memberOf` está preenchido
- Usar DN completo dos grupos no mapeamento
- Verificar logs: `tail -f storage/logs/laravel.log`

## Comandos Úteis

```bash
# Testar conexão LDAP
php artisan ldap:test username

# Ver logs LDAP
tail -f storage/logs/laravel.log | grep -i ldap

# Limpar cache de autenticação
php artisan cache:clear
php artisan config:clear

# Atribuir role manualmente
php artisan tinker
$user = User::find(1);
$user->assignRole('admin');
```
