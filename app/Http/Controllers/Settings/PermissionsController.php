<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsController extends Controller
{
    /**
     * Display the permissions management page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        
        // Current user's roles
        $roles = $user->getRoleNames()->toArray();
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
        
        // Admin controls (only if user can manage users)
        $admin = null;
        $rolesManagement = null;
        
        if ($user->can('users.manage')) {
            $targetUserId = $request->query('admin_user_id');
            $targetUser = $targetUserId ? User::find($targetUserId) : $user;
            
            if (!$targetUser) {
                $targetUser = $user;
            }
            
            // Get all users for selection
            $allUsers = User::with('roles')->orderBy('name')->get()->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'roles' => $u->getRoleNames()->toArray(),
                ];
            });
            
            // Get all permissions and roles
            $allPermissions = Permission::orderBy('name')->pluck('name')->toArray();
            $allRoles = Role::orderBy('name')->get()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions_count' => $role->permissions()->count(),
                ];
            });
            
            // Get target user's roles and permissions
            $targetRoles = $targetUser->getRoleNames()->toArray();
            $targetPermissions = $targetUser->getAllPermissions()->pluck('name')->toArray();
            $targetDirectPermissions = $targetUser->getDirectPermissions()->pluck('name')->toArray();
            
            // Permission metadata and grouping
            $permissionGroups = $this->getPermissionGroups();
            $permissionsMeta = $this->getPermissionsMeta();
            
            $admin = [
                'enabled' => true,
                'users' => $allUsers,
                'target' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->name,
                    'email' => $targetUser->email,
                ],
                'targetRoles' => $targetRoles,
                'targetPermissions' => $targetPermissions,
                'targetDirectPermissions' => $targetDirectPermissions,
                'allPermissions' => $allPermissions,
                'allRoles' => $allRoles,
                'permissionGroups' => $permissionGroups,
                'permissionsMeta' => $permissionsMeta,
            ];
        }
        
        // Role management (only if user can manage roles)
        if ($user->can('roles.manage')) {
            $rolesManagement = [
                'enabled' => true,
                'roles' => Role::with('permissions')->orderBy('name')->get()->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'permissions' => $role->permissions->pluck('name')->toArray(),
                        'users_count' => $role->users()->count(),
                    ];
                }),
                'allPermissions' => Permission::orderBy('name')->pluck('name')->toArray(),
                'permissionGroups' => $this->getPermissionGroups(),
                'permissionsMeta' => $this->getPermissionsMeta(),
            ];
        }
        
        return Inertia::render('Settings/Permissions', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
            'roles' => $roles,
            'permissions' => $userPermissions,
            'admin' => $admin,
            'rolesManagement' => $rolesManagement,
        ]);
    }
    
    /**
     * Toggle a role for a user (admin only).
     */
    public function toggleRole(Request $request)
    {
        abort_unless($request->user()->can('users.manage'), 403);
        
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'granted' => ['required', 'boolean'],
        ]);
        
        $targetUser = User::findOrFail($data['user_id']);
        
        if ($data['granted']) {
            $targetUser->assignRole($data['role']);
        } else {
            $targetUser->removeRole($data['role']);
        }
        
        return back()->with('success', 'Role atualizada com sucesso.');
    }
    
    /**
     * Toggle a permission for a user (admin only).
     */
    public function togglePermission(Request $request)
    {
        abort_unless($request->user()->can('users.manage'), 403);
        
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'permission' => ['required', 'string', 'exists:permissions,name'],
            'granted' => ['required', 'boolean'],
        ]);
        
        $targetUser = User::findOrFail($data['user_id']);
        
        if ($data['granted']) {
            $targetUser->givePermissionTo($data['permission']);
        } else {
            $targetUser->revokePermissionTo($data['permission']);
        }
        
        return back()->with('success', 'Permissão atualizada com sucesso.');
    }
    
    /**
     * Create a new role.
     */
    public function createRole(Request $request)
    {
        abort_unless($request->user()->can('roles.manage'), 403);
        
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);
        
        $role = Role::create(['name' => $data['name']]);
        
        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }
        
        return back()->with('success', 'Role criada com sucesso.');
    }
    
    /**
     * Update a role's permissions.
     */
    public function updateRole(Request $request, Role $role)
    {
        abort_unless($request->user()->can('roles.manage'), 403);
        
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);
        
        if (isset($data['name'])) {
            $role->update(['name' => $data['name']]);
        }
        
        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }
        
        return back()->with('success', 'Role atualizada com sucesso.');
    }
    
    /**
     * Delete a role.
     */
    public function deleteRole(Role $role)
    {
        abort_unless(request()->user()->can('roles.manage'), 403);
        
        // Prevent deletion of system roles
        if (in_array($role->name, ['Super Admin', 'Admin'])) {
            return back()->with('error', 'Roles do sistema não podem ser excluídas.');
        }
        
        $role->delete();
        
        return back()->with('success', 'Role excluída com sucesso.');
    }
    
    /**
     * Get permission groups for organization.
     */
    protected function getPermissionGroups(): array
    {
        return [
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'permissions' => ['dashboard.view'],
            ],
            [
                'key' => 'clients',
                'label' => 'Clientes',
                'permissions' => ['clients.view', 'clients.create', 'clients.edit', 'clients.delete'],
            ],
            [
                'key' => 'projects',
                'label' => 'Projetos',
                'permissions' => ['projects.view', 'projects.create', 'projects.edit', 'projects.delete'],
            ],
            [
                'key' => 'tasks',
                'label' => 'Tarefas',
                'permissions' => ['tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete', 'tasks.assign'],
            ],
            [
                'key' => 'support',
                'label' => 'Suporte',
                'permissions' => ['support.view', 'support.create', 'support.edit', 'support.delete', 'support.admin'],
            ],
            [
                'key' => 'financial',
                'label' => 'Financeiro',
                'permissions' => ['financial.view', 'financial.manage'],
            ],
            [
                'key' => 'users',
                'label' => 'Usuários',
                'permissions' => ['users.view', 'users.manage', 'users.impersonate'],
            ],
            [
                'key' => 'roles',
                'label' => 'Roles',
                'permissions' => ['roles.view', 'roles.manage'],
            ],
            [
                'key' => 'settings',
                'label' => 'Configurações',
                'permissions' => ['settings.view', 'settings.manage'],
            ],
            [
                'key' => 'system',
                'label' => 'Sistema',
                'permissions' => ['system.logs', 'system.manage'],
            ],
        ];
    }
    
    /**
     * Get permissions metadata.
     */
    protected function getPermissionsMeta(): array
    {
        return [
            // Dashboard
            'dashboard.view' => ['label' => 'Visualizar Dashboard', 'description' => 'Acesso ao dashboard'],
            
            // Clientes
            'clients.view' => ['label' => 'Visualizar Clientes', 'description' => 'Ver lista e detalhes de clientes'],
            'clients.create' => ['label' => 'Criar Clientes', 'description' => 'Criar novos clientes'],
            'clients.edit' => ['label' => 'Editar Clientes', 'description' => 'Modificar clientes existentes'],
            'clients.delete' => ['label' => 'Excluir Clientes', 'description' => 'Remover clientes'],
            
            // Projetos
            'projects.view' => ['label' => 'Visualizar Projetos', 'description' => 'Ver lista e detalhes de projetos'],
            'projects.create' => ['label' => 'Criar Projetos', 'description' => 'Criar novos projetos'],
            'projects.edit' => ['label' => 'Editar Projetos', 'description' => 'Modificar projetos existentes'],
            'projects.delete' => ['label' => 'Excluir Projetos', 'description' => 'Remover projetos'],
            
            // Tarefas
            'tasks.view' => ['label' => 'Visualizar Tarefas', 'description' => 'Ver lista e detalhes de tarefas'],
            'tasks.create' => ['label' => 'Criar Tarefas', 'description' => 'Criar novas tarefas'],
            'tasks.edit' => ['label' => 'Editar Tarefas', 'description' => 'Modificar tarefas existentes'],
            'tasks.delete' => ['label' => 'Excluir Tarefas', 'description' => 'Remover tarefas'],
            'tasks.assign' => ['label' => 'Atribuir Tarefas', 'description' => 'Atribuir tarefas a usuários'],
            
            // Financeiro
            'financial.view' => ['label' => 'Visualizar Financeiro', 'description' => 'Ver dados e relatórios financeiros'],
            'financial.manage' => ['label' => 'Gerenciar Financeiro', 'description' => 'Criar, editar e excluir transações'],
            
            // Suporte
            'support.view' => ['label' => 'Visualizar Suporte', 'description' => 'Ver tickets de suporte'],
            'support.create' => ['label' => 'Criar Tickets', 'description' => 'Abrir novos tickets'],
            'support.edit' => ['label' => 'Editar Tickets', 'description' => 'Modificar tickets existentes'],
            'support.delete' => ['label' => 'Excluir Tickets', 'description' => 'Remover tickets'],
            'support.admin' => ['label' => 'Administrar Suporte', 'description' => 'Acesso total ao sistema de suporte'],
            
            // Usuários
            'users.view' => ['label' => 'Visualizar Usuários', 'description' => 'Ver lista de usuários'],
            'users.manage' => ['label' => 'Gerenciar Usuários', 'description' => 'Criar, editar e gerenciar usuários'],
            'users.impersonate' => ['label' => 'Visualizar como Usuário', 'description' => 'Impersonar outros usuários'],
            
            // Roles
            'roles.view' => ['label' => 'Visualizar Roles', 'description' => 'Ver lista de roles'],
            'roles.manage' => ['label' => 'Gerenciar Roles', 'description' => 'Criar, editar e excluir roles'],
            
            // Configurações
            'settings.view' => ['label' => 'Visualizar Configurações', 'description' => 'Acesso às configurações'],
            'settings.manage' => ['label' => 'Gerenciar Configurações', 'description' => 'Modificar configurações do sistema'],
            
            // Sistema
            'system.logs' => ['label' => 'Visualizar Logs', 'description' => 'Acesso aos logs do sistema'],
            'system.manage' => ['label' => 'Gerenciar Sistema', 'description' => 'Controle total do sistema'],
        ];
    }
}
