<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\CepController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskTimerController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\TaskChecklistController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\SupportCategoryController;
use App\Http\Controllers\SupportResponseController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\PermissionsController;
use App\Http\Controllers\Settings\ImpersonationController;
use App\Http\Controllers\FinancialDashboardController;
use App\Http\Controllers\FinancialCategoryController;
use App\Http\Controllers\FinancialTransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('auth/Login'))->name('login');

Route::middleware(['auth', 'sync.ldap.groups'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');
    
    // API Routes
    Route::get('/api/cep/{zipcode}', [CepController::class, 'search'])->name('api.cep.search');
    
    // Rotas de clientes
    Route::middleware('permission:clients.view')->group(function () {
        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    });
    
    Route::middleware('permission:clients.create')->group(function () {
        Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    });
    
    Route::middleware('permission:clients.edit')->group(function () {
        Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::patch('clients/{client}', [ClientController::class, 'update']);
        Route::post('clients/{client}/toggle-status', [ClientController::class, 'toggleStatus'])->name('clients.toggle-status');
        Route::patch('clients/bulk-toggle-status', [ClientController::class, 'bulkToggleStatus'])->name('clients.bulk-toggle-status');
    });
    
    Route::middleware('permission:clients.delete')->group(function () {
        Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::delete('clients/bulk-destroy', [ClientController::class, 'bulkDestroy'])->name('clients.bulk-destroy');
    });
    
    Route::get('clients-export', [ClientController::class, 'export'])
        ->middleware('permission:clients.view')
        ->name('clients.export');
    
    // Rota para consulta de CEP
    Route::get('cep/{cep}', [CepController::class, 'search'])->name('cep.search');
    
    // Rotas de projetos
    Route::middleware('permission:projects.view')->group(function () {
        Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    });
    
    Route::middleware('permission:projects.create')->group(function () {
        Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    });
    
    Route::middleware('permission:projects.edit')->group(function () {
        Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::patch('projects/{project}', [ProjectController::class, 'update']);
        Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');
        Route::patch('projects/bulk-update-status', [ProjectController::class, 'bulkUpdateStatus'])->name('projects.bulk-update-status');
    });
    
    Route::middleware('permission:projects.delete')->group(function () {
        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::delete('projects/bulk-destroy', [ProjectController::class, 'bulkDestroy'])->name('projects.bulk-destroy');
    });
    
    // Rotas de tarefas
    Route::middleware('permission:tasks.view')->group(function () {
        Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::get('tasks-kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
    });
    
    Route::middleware('permission:tasks.create')->group(function () {
        Route::get('tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
    });
    
    Route::middleware('permission:tasks.edit')->group(function () {
        Route::get('tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::patch('tasks/{task}', [TaskController::class, 'update']);
        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    });
    
    Route::middleware('permission:tasks.delete')->group(function () {
        Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    });
    
    // Rotas do Timer de Tarefas (requer permissão de tarefas)
    Route::middleware('permission:tasks.view')->group(function () {
        Route::post('tasks/{task}/timer/start', [TaskTimerController::class, 'start'])->name('tasks.timer.start');
        Route::post('tasks/{task}/timer/stop', [TaskTimerController::class, 'stop'])->name('tasks.timer.stop');
        Route::get('tasks/{task}/timer/status', [TaskTimerController::class, 'status'])->name('tasks.timer.status');
        Route::get('tasks/{task}/timer/sessions', [TaskTimerController::class, 'sessions'])->name('tasks.timer.sessions');
        Route::delete('tasks/{task}/timer/sessions/{entry}', [TaskTimerController::class, 'deleteSession'])->name('tasks.timer.delete-session');
    });
    
    // Rotas de recursos avançados das tarefas
    Route::middleware('permission:tasks.view')->group(function () {
        Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
        Route::delete('tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('tasks.comments.destroy');
        
        Route::post('tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])->name('tasks.attachments.store');
        Route::get('tasks/{task}/attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])->name('tasks.attachments.download');
        Route::delete('tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])->name('tasks.attachments.destroy');
        
        Route::post('tasks/{task}/checklist', [TaskChecklistController::class, 'store'])->name('tasks.checklist.store');
        Route::patch('tasks/{task}/checklist/{checklist}', [TaskChecklistController::class, 'update'])->name('tasks.checklist.update');
        Route::delete('tasks/{task}/checklist/{checklist}', [TaskChecklistController::class, 'destroy'])->name('tasks.checklist.destroy');
    });
    
    // Rotas do Kanban
    Route::middleware('permission:tasks.view')->group(function () {
        Route::get('kanban', [KanbanController::class, 'index'])->name('kanban.index');
        Route::patch('kanban/{task}/status', [KanbanController::class, 'updateStatus'])->name('kanban.update-status');
        Route::patch('kanban/{task}/move', [KanbanController::class, 'moveTask'])->name('kanban.move-task');
        Route::post('kanban/reorder', [KanbanController::class, 'reorder'])->name('kanban.reorder');
        Route::post('kanban/quick-create', [KanbanController::class, 'quickCreate'])->name('kanban.quick-create');
    });
    
    // Rotas do Sistema de Suporte
    Route::prefix('support')->name('support.')->group(function () {
        // Visualização de tickets
        Route::middleware('permission:support.view')->group(function () {
            Route::get('tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
            Route::get('tickets/{ticket}', [SupportTicketController::class, 'show'])->name('tickets.show');
        });
        
        // Criação de tickets
        Route::middleware('permission:support.create')->group(function () {
            Route::get('tickets/create', [SupportTicketController::class, 'create'])->name('tickets.create');
            Route::post('tickets', [SupportTicketController::class, 'store'])->name('tickets.store');
        });
        
        // Edição de tickets
        Route::middleware('permission:support.edit')->group(function () {
            Route::patch('tickets/{ticket}', [SupportTicketController::class, 'update'])->name('tickets.update');
        });
        
        // Exclusão de tickets
        Route::middleware('permission:support.delete')->group(function () {
            Route::delete('tickets/{ticket}', [SupportTicketController::class, 'destroy'])->name('tickets.destroy');
        });
        
        // Admin de suporte
        Route::middleware('permission:support.admin')->group(function () {
            Route::get('admin', [SupportTicketController::class, 'admin'])->name('admin');
        });
        
        // Respostas de tickets
        Route::middleware('permission:support.view')->group(function () {
            Route::post('tickets/{ticket}/responses', [SupportResponseController::class, 'store'])->name('tickets.responses.store');
            Route::delete('tickets/{ticket}/responses/{response}', [SupportResponseController::class, 'destroy'])->name('tickets.responses.destroy');
        });
        
        // Categorias de suporte
        Route::middleware('permission:support.admin')->group(function () {
            Route::resource('categories', SupportCategoryController::class)->except(['show']);
        });
    });

    // Financial Routes (separate access control via permissions)
    Route::prefix('financial')->name('financial.')->middleware('permission:financial.view|financial.manage')->group(function () {
        // Dashboard
        Route::get('/', [FinancialDashboardController::class, 'index'])->name('dashboard');
        Route::get('/export', [FinancialDashboardController::class, 'export'])->name('export');
        
        // Categories
        Route::resource('categories', FinancialCategoryController::class);
        Route::post('categories/{category}/toggle-status', [FinancialCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::delete('categories/bulk-destroy', [FinancialCategoryController::class, 'bulkDestroy'])->name('categories.bulk-destroy');
        Route::patch('categories/bulk-toggle-status', [FinancialCategoryController::class, 'bulkToggleStatus'])->name('categories.bulk-toggle-status');
        
        // Transactions
        Route::resource('transactions', FinancialTransactionController::class);
        Route::patch('transactions/{transaction}/mark-as-paid', [FinancialTransactionController::class, 'markAsPaid'])->name('transactions.mark-as-paid');
        Route::patch('transactions/{transaction}/mark-as-pending', [FinancialTransactionController::class, 'markAsPending'])->name('transactions.mark-as-pending');
        Route::patch('transactions/{transaction}/cancel', [FinancialTransactionController::class, 'cancel'])->name('transactions.cancel');
        Route::delete('transactions/bulk-destroy', [FinancialTransactionController::class, 'bulkDestroy'])->name('transactions.bulk-destroy');
        Route::patch('transactions/bulk-update-status', [FinancialTransactionController::class, 'bulkUpdateStatus'])->name('transactions.bulk-update-status');
    });

    // Settings Routes
    Route::prefix('settings')->name('settings.')->middleware('ensure.user.settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::patch('/', [SettingsController::class, 'update'])->name('update');
        Route::post('/reset', [SettingsController::class, 'reset'])->name('reset');
        
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::match(['post', 'patch'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
        
        Route::get('/password', [PasswordController::class, 'edit'])->name('password.edit');
        Route::patch('/password', [PasswordController::class, 'update'])->name('password.update');
        
        Route::get('/permissions', [PermissionsController::class, 'edit'])->name('permissions');
        Route::post('/admin/roles/toggle', [PermissionsController::class, 'toggleRole'])->name('admin.roles.toggle');
        Route::post('/admin/permissions/toggle', [PermissionsController::class, 'togglePermission'])->name('admin.permissions.toggle');
        
        // Role management routes
        Route::post('/roles', [PermissionsController::class, 'createRole'])->name('roles.create');
        Route::patch('/roles/{role}', [PermissionsController::class, 'updateRole'])->name('roles.update');
        Route::delete('/roles/{role}', [PermissionsController::class, 'deleteRole'])->name('roles.delete');
        
        Route::post('/impersonate/start', [ImpersonationController::class, 'start'])->name('impersonate.start');
        Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');
    });



    // API Routes
    Route::get('/api/search', [SearchController::class, 'index'])->name('api.search');
    
    // Avatar route for clean URLs
    Route::get('/avatars/{filename}', function ($filename) {
        $path = storage_path('app/public/avatars/' . $filename);
        
        if (!file_exists($path)) {
            abort(404);
        }
        
        return response()->file($path);
    })->name('avatar.show');
    
    // Rotas de teste para páginas de erro (apenas em desenvolvimento)
    if (config('app.debug')) {
        Route::prefix('test-errors')->group(function () {
            Route::get('/404', fn() => abort(404))->name('test.404');
            Route::get('/500', fn() => abort(500))->name('test.500');
            Route::get('/503', fn() => abort(503))->name('test.503');
            Route::get('/403', fn() => abort(403))->name('test.403');
            Route::get('/401', fn() => abort(401))->name('test.401');
            Route::get('/maintenance', function() {
                // Simular modo de manutenção
                app()->make(\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class);
                return abort(503);
            })->name('test.maintenance');
        });
    }
});
