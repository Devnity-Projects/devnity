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

            // Projetos
            'projects.view' => ['label' => 'Projetos: ver', 'description' => 'Pode listar e ver detalhes de projetos.'],
            'projects.create' => ['label' => 'Projetos: criar', 'description' => 'Pode criar novos projetos.'],
            'projects.update' => ['label' => 'Projetos: editar', 'description' => 'Pode editar projetos existentes.'],
            'projects.delete' => ['label' => 'Projetos: apagar', 'description' => 'Pode remover projetos.'],
            'projects.manage' => ['label' => 'Projetos: gerenciar (amplo)', 'description' => 'Acesso amplo ao módulo de projetos.'],
            'projects.update_status' => ['label' => 'Projetos: alterar status', 'description' => 'Pode alterar o status de projetos.'],

            // Tarefas
            'tasks.view' => ['label' => 'Tarefas: ver', 'description' => 'Pode listar e ver detalhes de tarefas.'],
            'tasks.create' => ['label' => 'Tarefas: criar', 'description' => 'Pode criar novas tarefas.'],
            'tasks.update' => ['label' => 'Tarefas: editar', 'description' => 'Pode editar tarefas.'],
            'tasks.delete' => ['label' => 'Tarefas: apagar', 'description' => 'Pode remover tarefas.'],
            'tasks.manage' => ['label' => 'Tarefas: gerenciar (amplo)', 'description' => 'Acesso amplo ao módulo de tarefas.'],
            'tasks.update_status' => ['label' => 'Tarefas: alterar status', 'description' => 'Pode alterar o status de tarefas.'],
            'tasks.comments.add' => ['label' => 'Tarefas: adicionar comentário', 'description' => 'Pode adicionar comentários a tarefas.'],
            'tasks.comments.delete' => ['label' => 'Tarefas: apagar comentário', 'description' => 'Pode remover comentários.'],
            'tasks.attachments.upload' => ['label' => 'Tarefas: enviar anexo', 'description' => 'Pode enviar anexos.'],
            'tasks.attachments.download' => ['label' => 'Tarefas: baixar anexo', 'description' => 'Pode baixar anexos.'],
            'tasks.attachments.delete' => ['label' => 'Tarefas: apagar anexo', 'description' => 'Pode remover anexos.'],
            'tasks.checklist.add' => ['label' => 'Tarefas: adicionar checklist', 'description' => 'Pode adicionar itens de checklist.'],
            'tasks.checklist.update' => ['label' => 'Tarefas: editar checklist', 'description' => 'Pode editar itens de checklist.'],
            'tasks.checklist.delete' => ['label' => 'Tarefas: apagar checklist', 'description' => 'Pode remover itens de checklist.'],

            // Kanban
            'kanban.view' => ['label' => 'Kanban: ver', 'description' => 'Pode acessar o quadro Kanban.'],
            'kanban.update_status' => ['label' => 'Kanban: alterar status', 'description' => 'Pode alterar status de tarefas via Kanban.'],
            'kanban.move' => ['label' => 'Kanban: mover cartões', 'description' => 'Pode mover cartões entre colunas.'],
            'kanban.reorder' => ['label' => 'Kanban: reordenar cartões', 'description' => 'Pode reordenar a posição dos cartões.'],
            'kanban.quick_create' => ['label' => 'Kanban: criação rápida', 'description' => 'Pode criar tarefas rapidamente pelo Kanban.'],

            // Suporte
            'support.admin' => ['label' => 'Suporte: admin', 'description' => 'Acessa a área administrativa do suporte.'],
            'support.tickets.view' => ['label' => 'Suporte: ver tickets', 'description' => 'Pode listar e ver tickets.'],
            'support.tickets.create' => ['label' => 'Suporte: criar ticket', 'description' => 'Pode abrir tickets.'],
            'support.tickets.update' => ['label' => 'Suporte: editar ticket', 'description' => 'Pode editar tickets.'],
            'support.tickets.delete' => ['label' => 'Suporte: apagar ticket', 'description' => 'Pode remover tickets.'],
            'support.responses.create' => ['label' => 'Suporte: responder ticket', 'description' => 'Pode adicionar respostas a tickets.'],
            'support.responses.delete' => ['label' => 'Suporte: apagar resposta', 'description' => 'Pode apagar respostas.'],
            'support.categories.view' => ['label' => 'Suporte: ver categorias', 'description' => 'Pode listar categorias.'],
            'support.categories.create' => ['label' => 'Suporte: criar categoria', 'description' => 'Pode criar categorias.'],
            'support.categories.update' => ['label' => 'Suporte: editar categoria', 'description' => 'Pode editar categorias.'],
            'support.categories.delete' => ['label' => 'Suporte: apagar categoria', 'description' => 'Pode remover categorias.'],

            // Financeiro
            'financial.view' => ['label' => 'Financeiro: ver dashboard', 'description' => 'Acessa o dashboard financeiro.'],
            'financial.export' => ['label' => 'Financeiro: exportar', 'description' => 'Pode exportar dados financeiros.'],
            'financial.categories.view' => ['label' => 'Financeiro: ver categorias', 'description' => 'Pode listar categorias financeiras.'],
            'financial.categories.create' => ['label' => 'Financeiro: criar categoria', 'description' => 'Pode criar categorias financeiras.'],
            'financial.categories.update' => ['label' => 'Financeiro: editar categoria', 'description' => 'Pode editar categorias financeiras.'],
            'financial.categories.delete' => ['label' => 'Financeiro: apagar categoria', 'description' => 'Pode remover categorias financeiras.'],
            'financial.categories.manage' => ['label' => 'Financeiro: gerenciar categorias (amplo)', 'description' => 'Acesso amplo às operações em massa/toggle de categorias.'],
            'financial.transactions.view' => ['label' => 'Financeiro: ver transações', 'description' => 'Pode listar e ver transações financeiras.'],
            'financial.transactions.create' => ['label' => 'Financeiro: criar transação', 'description' => 'Pode criar transações financeiras.'],
            'financial.transactions.update' => ['label' => 'Financeiro: editar transação', 'description' => 'Pode editar transações financeiras.'],
            'financial.transactions.delete' => ['label' => 'Financeiro: apagar transação', 'description' => 'Pode remover transações financeiras.'],
            'financial.transactions.mark_paid' => ['label' => 'Financeiro: marcar como pago', 'description' => 'Pode marcar transações como pagas.'],
            'financial.transactions.mark_pending' => ['label' => 'Financeiro: marcar como pendente', 'description' => 'Pode marcar transações como pendentes.'],
            'financial.transactions.cancel' => ['label' => 'Financeiro: cancelar transação', 'description' => 'Pode cancelar transações.'],
            'financial.transactions.manage' => ['label' => 'Financeiro: gerenciar transações (amplo)', 'description' => 'Acesso amplo às operações em massa de transações.'],
        ];

        // Grouping structure for UI sections
        $groups = [
            [
                'key' => 'system',
                'label' => 'Sistema',
                'permissions' => [
                    'manage users', 'view reports',
                ],
            ],
            [
                'key' => 'clients',
                'label' => 'Clientes',
                'permissions' => [
                    'clients.view','clients.create','clients.update','clients.delete','clients.manage','clients.export',
                    'clients.view_document','clients.view_contact','clients.view_address','clients.view_tax_info','clients.view_notes',
                ],
            ],
            [
                'key' => 'projects',
                'label' => 'Projetos',
                'permissions' => [
                    'projects.view','projects.create','projects.update','projects.delete','projects.manage','projects.update_status',
                ],
            ],
            [
                'key' => 'tasks',
                'label' => 'Tarefas',
                'permissions' => [
                    'tasks.view','tasks.create','tasks.update','tasks.delete','tasks.manage','tasks.update_status',
                    'tasks.comments.add','tasks.comments.delete',
                    'tasks.attachments.upload','tasks.attachments.download','tasks.attachments.delete',
                    'tasks.checklist.add','tasks.checklist.update','tasks.checklist.delete',
                ],
            ],
            [
                'key' => 'kanban',
                'label' => 'Kanban',
                'permissions' => [
                    'kanban.view','kanban.update_status','kanban.move','kanban.reorder','kanban.quick_create',
                ],
            ],
            [
                'key' => 'support',
                'label' => 'Suporte',
                'permissions' => [
                    'support.admin',
                    'support.tickets.view','support.tickets.create','support.tickets.update','support.tickets.delete',
                    'support.responses.create','support.responses.delete',
                    'support.categories.view','support.categories.create','support.categories.update','support.categories.delete',
                ],
            ],
            [
                'key' => 'financial',
                'label' => 'Financeiro',
                'permissions' => [
                    'manage finances', 'financial.view', 'financial.export',
                    'financial.categories.view','financial.categories.create','financial.categories.update','financial.categories.delete','financial.categories.manage',
                    'financial.transactions.view','financial.transactions.create','financial.transactions.update','financial.transactions.delete','financial.transactions.mark_paid','financial.transactions.mark_pending','financial.transactions.cancel','financial.transactions.manage',
                ],
            ],
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
                'permissionGroups' => $groups,
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
