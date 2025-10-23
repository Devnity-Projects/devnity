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
        
        // Admin controls (only if user can manage users)
        $admin = null;
        if ($user->can('manage users')) {
            $targetUserId = $request->query('admin_user_id');
            $targetUser = $targetUserId ? User::find($targetUserId) : $user;
            
            if (!$targetUser) {
                $targetUser = $user;
            }
            
            // Get all users for selection
            $allUsers = User::orderBy('name')->get(['id', 'name', 'email']);
            
            // Get all permissions and roles
            $allPermissions = Permission::orderBy('name')->pluck('name')->toArray();
            $allRoles = Role::orderBy('name')->pluck('name')->toArray();
            
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
        
        return Inertia::render('Settings/Permissions', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
            'roles' => $roles,
            'admin' => $admin,
        ]);
    }
    
    /**
     * Toggle a role for a user (admin only).
     */
    public function toggleRole(Request $request)
    {
        abort_unless($request->user()->can('manage users'), 403);
        
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
        abort_unless($request->user()->can('manage users'), 403);
        
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
     * Get permission groups for organization.
     */
    protected function getPermissionGroups(): array
    {
        return [
            [
                'key' => 'users',
                'label' => 'Usuários',
                'permissions' => ['manage users', 'view users'],
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
                'permissions' => ['tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete'],
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
        ];
    }
    
    /**
     * Get permissions metadata.
     */
    protected function getPermissionsMeta(): array
    {
        return [
            'manage users' => [
                'label' => 'Gerenciar Usuários',
                'description' => 'Permite gerenciar usuários, roles e permissões',
            ],
            'view users' => [
                'label' => 'Visualizar Usuários',
                'description' => 'Permite visualizar lista de usuários',
            ],
            'clients.view' => [
                'label' => 'Visualizar Clientes',
                'description' => 'Permite visualizar clientes',
            ],
            'clients.create' => [
                'label' => 'Criar Clientes',
                'description' => 'Permite criar novos clientes',
            ],
            'clients.edit' => [
                'label' => 'Editar Clientes',
                'description' => 'Permite editar clientes existentes',
            ],
            'clients.delete' => [
                'label' => 'Excluir Clientes',
                'description' => 'Permite excluir clientes',
            ],
            'financial.view' => [
                'label' => 'Visualizar Financeiro',
                'description' => 'Permite visualizar dados financeiros',
            ],
            'financial.manage' => [
                'label' => 'Gerenciar Financeiro',
                'description' => 'Permite gerenciar completamente o módulo financeiro',
            ],
        ];
    }
}
