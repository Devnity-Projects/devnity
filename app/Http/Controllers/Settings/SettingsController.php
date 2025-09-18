<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Show the user's general settings page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $settings = $user->getOrCreateSettings();
        
        return Inertia::render('Settings/Index', [
            'settings' => [
                'theme' => $settings->theme,
                'language' => $settings->language,
                'timezone' => $settings->timezone,
                'date_format' => $settings->date_format,
                'time_format' => $settings->time_format,
                'email_notifications' => $settings->email_notifications,
                'browser_notifications' => $settings->browser_notifications,
                'task_reminders' => $settings->task_reminders,
                'project_updates' => $settings->project_updates,
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
        ]);
    }

    /**
     * Update the user's settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:light,dark,system'],
            'language' => ['required', 'string', 'in:pt-BR,en-US,es-ES'],
            'timezone' => ['required', 'string'],
            'date_format' => ['nullable', 'string', 'in:d/m/Y,m/d/Y,Y-m-d'],
            'time_format' => ['nullable', 'string', 'in:H:i,h:i A'],
            'email_notifications' => ['boolean'],
            'browser_notifications' => ['boolean'],
            'task_reminders' => ['boolean'],
            'project_updates' => ['boolean'],
        ]);

        $user = $request->user();
        $settings = $user->getOrCreateSettings();
        
        // Defaults
        $validated['date_format'] = $validated['date_format'] ?? 'd/m/Y';
        $validated['time_format'] = $validated['time_format'] ?? 'H:i';
        
        $settings->update($validated);

        return back()->with('status', 'Configurações atualizadas com sucesso!');
    }

    /**
     * Reset settings to default values.
     */
    public function reset(Request $request): RedirectResponse
    {
        $user = $request->user();
        $settings = $user->getOrCreateSettings();
        
        $settings->update(\App\Models\UserSettings::getDefaultSettings());

        return back()->with('status', 'Configurações resetadas para o padrão!');
    }

    /**
     * Admin: atribui ou remove uma role de um usuário selecionado.
     */
    public function adminToggleUserRole(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('manage users'), 403);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', 'string'],
            'granted' => ['required', 'boolean'],
        ]);

        $target = \App\Models\User::findOrFail($data['user_id']);
        $role = \Spatie\Permission\Models\Role::where('name', $data['role'])->first();
        if (!$role) {
            return back()->with('error', 'Papel (role) inválido.');
        }

        if ($data['granted']) {
            $target->assignRole($data['role']);
            $msg = "Role '{$data['role']}' atribuída a {$target->name}.";
        } else {
            // Safety: avoid removing 'admin' from the last remaining admin
            if ($data['role'] === 'admin') {
                // Optional: lock a specific super admin by email/ID
                $superEmail = env('SUPER_ADMIN_EMAIL');
                $superId = env('SUPER_ADMIN_ID');

                if (($superEmail && $target->email === $superEmail) || ($superId && (string)$target->id === (string)$superId)) {
                    return back()->with('error', 'Não é permitido remover a role admin deste usuário protegido.');
                }

                $adminCount = \App\Models\User::role('admin')->count();
                if ($adminCount <= 1) {
                    return back()->with('error', 'Você não pode remover a role admin do último administrador do sistema.');
                }
            }
            $target->removeRole($data['role']);
            $msg = "Role '{$data['role']}' removida de {$target->name}.";
        }

        return back()->with('status', $msg);
    }

    /**
     * Admin: concede ou revoga uma permissão direta para um usuário selecionado.
     */
    public function adminToggleUserPermission(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('manage users'), 403);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'permission' => ['required', 'string'],
            'granted' => ['required', 'boolean'],
        ]);

        $target = \App\Models\User::findOrFail($data['user_id']);
        $permName = $data['permission'];

        $perm = \Spatie\Permission\Models\Permission::where('name', $permName)->first();
        if (!$perm) {
            return back()->with('error', 'Permissão inválida.');
        }

        if ($data['granted']) {
            $target->givePermissionTo($permName);
            $msg = "Permissão '{$permName}' concedida a {$target->name}.";
        } else {
            $target->revokePermissionTo($permName);
            $msg = "Permissão '{$permName}' revogada de {$target->name}.";
        }

        return back()->with('status', $msg);
    }

    /**
     * Página dedicada para administração de permissões (apenas admin/manage users).
     */
    public function permissions(Request $request): Response
    {
        $user = $request->user();
        $allRoles = \Spatie\Permission\Models\Role::orderBy('name')->get(['id','name'])->pluck('name');

        // Friendly metadata for permissions
        $permMeta = [
            // Sistema
            'manage users' => ['label' => 'Gerenciar usuários', 'description' => 'Permite administrar usuários, roles e permissões.'],
            'manage projects' => ['label' => 'Gerenciar projetos', 'description' => 'Criar, editar e apagar projetos.'],
            'view reports' => ['label' => 'Ver relatórios', 'description' => 'Acessa relatórios e métricas do sistema.'],
            'manage finances' => ['label' => 'Gerenciar finanças', 'description' => 'Acessa e gerencia categorias e transações financeiras.'],

            // Clientes (módulo)
            'clients.view' => ['label' => 'Clientes: ver', 'description' => 'Pode acessar a lista e detalhes de clientes.'],
            'clients.create' => ['label' => 'Clientes: criar', 'description' => 'Pode criar novos clientes.'],
            'clients.update' => ['label' => 'Clientes: editar', 'description' => 'Pode editar clientes existentes.'],
            'clients.delete' => ['label' => 'Clientes: apagar', 'description' => 'Pode remover clientes.'],
            'clients.manage' => ['label' => 'Clientes: gerenciar (amplo)', 'description' => 'Acesso amplo ao módulo de clientes.'],
            'clients.export' => ['label' => 'Clientes: exportar', 'description' => 'Pode exportar dados de clientes.'],

            // Clientes (campos sensíveis)
            'clients.view_document' => ['label' => 'Clientes: ver documento', 'description' => 'Exibe CPF/CNPJ e documentos.'],
            'clients.view_contact' => ['label' => 'Clientes: ver contato', 'description' => 'Exibe e-mail e telefone.'],
            'clients.view_address' => ['label' => 'Clientes: ver endereço', 'description' => 'Exibe endereço completo.'],
            'clients.view_tax_info' => ['label' => 'Clientes: ver info fiscal', 'description' => 'Exibe dados fiscais (IE, regime, etc).'],
            'clients.view_notes' => ['label' => 'Clientes: ver anotações', 'description' => 'Exibe observações e notas internas.'],
        ];

        $adminControls = null;
        if ($user->can('manage users')) {
            $users = \App\Models\User::orderBy('name')->get(['id','name','email']);
            $targetId = (int) $request->query('admin_user_id', $user->id);
            $target = \App\Models\User::find($targetId) ?: $user;
            $allPermissions = \Spatie\Permission\Models\Permission::orderBy('name')->get(['id','name'])->pluck('name');
            $adminControls = [
                'enabled' => true,
                'users' => $users,
                'target' => [
                    'id' => $target->id,
                    'name' => $target->name,
                    'email' => $target->email,
                ],
                'targetRoles' => $target->getRoleNames(),
                'targetPermissions' => $target->getAllPermissions()->pluck('name'), // effective
                'targetDirectPermissions' => $target->getDirectPermissions()->pluck('name'),
                'allPermissions' => $allPermissions,
                'permissionsMeta' => $permMeta,
            ];
        }

        return Inertia::render('Settings/Permissions', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
            'roles' => $allRoles,
            'admin' => $adminControls,
        ]);
    }
}
