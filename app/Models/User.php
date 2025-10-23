<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;

class User extends Authenticatable implements LdapAuthenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, AuthenticatesWithLdap;

    /**
     * The attributes that are mass assignable.
     * Limitado apenas aos campos seguros para mass assignment
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'bio',
        'domain',
        'guid',
        'samaccountname',
        // Removido 'avatar' do fillable por segurança - deve ser atualizado separadamente
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Campos sensíveis que não devem aparecer em responses
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be guarded against mass assignment.
     * Campos críticos que nunca devem ser mass assigned
     */
    protected $guarded = [
        'id',
        'email_verified_at',
        'avatar', // Avatar deve ser atualizado através de processo específico
        'created_at',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSettings::class);
    }

    public function getOrCreateSettings(): UserSettings
    {
        if (!$this->settings) {
            $this->settings()->create(UserSettings::getDefaultSettings());
            $this->load('settings');
        }
        
        return $this->settings;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            return route('avatar.show', ['filename' => $this->avatar]);
        }
        
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&color=7F9CF5&background=EBF4FF";
    }

    /**
     * Sincroniza roles baseado em grupos do Active Directory
     */
    public function syncRolesFromLdap(array $groups): void
    {
        // Mapeamento de grupos AD para roles do sistema
        $roleMappings = [
            env('LDAP_ADMIN_GROUP', 'CN=Administradores,CN=Users,DC=dominio,DC=local') => 'admin',
            env('LDAP_MANAGER_GROUP', 'CN=Gerentes,CN=Users,DC=dominio,DC=local') => 'manager',
            env('LDAP_DEVELOPER_GROUP', 'CN=Desenvolvedores,CN=Users,DC=dominio,DC=local') => 'developer',
            env('LDAP_SUPPORT_GROUP', 'CN=Suporte,CN=Users,DC=dominio,DC=local') => 'support',
            env('LDAP_FINANCIAL_GROUP', 'CN=Financeiro,CN=Users,DC=dominio,DC=local') => 'financial',
            env('LDAP_CLIENT_GROUP', 'CN=Clientes,CN=Users,DC=dominio,DC=local') => 'client',
        ];

        $assignRoles = [];

        foreach ($groups as $group) {
            if (isset($roleMappings[$group])) {
                $assignRoles[] = $roleMappings[$group];
            }
        }

        if (!empty($assignRoles)) {
            // Sincroniza roles (remove antigas e adiciona novas)
            $this->syncRoles($assignRoles);
            \Log::info("Roles sincronizadas para usuário {$this->email}: " . implode(', ', $assignRoles));
        } else {
            // Role padrão se não encontrar grupo correspondente
            if (!$this->hasAnyRole(['developer', 'client', 'admin', 'manager', 'support', 'financial'])) {
                $this->assignRole('developer');
                \Log::info("Role padrão 'developer' atribuída ao usuário {$this->email}");
            }
        }
    }
}
