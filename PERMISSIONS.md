# Permissões e Roles (Spatie)

Este projeto usa o pacote spatie/laravel-permission para gerenciar roles e permissions.

O que foi feito:

- Pacote instalado via Composer.
- Configuração publicada em `config/permission.php`.
- Migrations publicadas e executadas (tabelas `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`).
- Trait `HasRoles` adicionada ao `app/Models/User.php`.
- Middleware `role`, `permission` e `role_or_permission` registrados em `app/Http/Kernel.php`.
- Seeder `database/seeders/PermissionSeeder.php` criado e executado (roles: `admin`, `manager`, `user`).

Como usar

- Verificar se o usuário tem role:

  - Em rotas (route middleware):

    Route::get('/admin', function () {
        // ...
    })->middleware('role:admin');

  - Em controllers (middleware no construtor):

    public function __construct()
    {
        $this->middleware(['role:admin|manager']);
    }

  - Em métodos/ações (em runtime):

    if (auth()->user()->hasRole('admin')) {
        // ...
    }

- Verificar permissões:

  if (auth()->user()->can('manage users')) {
      // ...
  }

- Atribuir role/permissões (exemplo em tinker ou seeders):

  $user = App\Models\User::first();
  $user->assignRole('admin');
  $user->givePermissionTo('manage users');

Notas

- O seeder `PermissionSeeder` cria roles e permissões básicas. Ajuste conforme necessário.
- Se o seu `DatabaseSeeder` tentar recriar um usuário já existente, a seeding pode falhar; use `firstOrCreate` em produção para evitar Unique violations.
- Para revogar roles/permissões, use `removeRole`, `revokePermissionTo`.

Próximos passos recomendados

- Adicionar proteção de rotas sensíveis com middleware `role` ou `permission`.
- Criar UI para gerenciar roles/permissions dentro da aplicação (controllers, views e testes).
- Usar policies (Gate) para lógica mais fina quando necessário.
