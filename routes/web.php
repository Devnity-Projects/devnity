<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\CepController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
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
use App\Http\Controllers\Settings\ImpersonationController;
use App\Http\Controllers\FinancialDashboardController;
use App\Http\Controllers\FinancialCategoryController;
use App\Http\Controllers\FinancialTransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }

    return Inertia::render('Auth/Login');
})->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // API Routes with rate limiting
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/api/cep/{zipcode}', [CepController::class, 'search'])->name('api.cep.search');
        Route::get('/api/search', [SearchController::class, 'index'])->name('api.search');
        Route::get('cep/{cep}', [CepController::class, 'search'])->name('cep.search');
    });
    
    // Rotas de clientes com rate limiting para operações em massa
    Route::resource('clients', ClientController::class);
    Route::post('clients/{client}/toggle-status', [ClientController::class, 'toggleStatus'])->name('clients.toggle-status');
    
    // Rate limiting para operações em massa
    Route::middleware('throttle:20,1')->group(function () {
        Route::delete('clients/bulk-destroy', [ClientController::class, 'bulkDestroy'])->name('clients.bulk-destroy');
        Route::patch('clients/bulk-toggle-status', [ClientController::class, 'bulkToggleStatus'])->name('clients.bulk-toggle-status');
        Route::get('clients-export', [ClientController::class, 'export'])->name('clients.export');
    });
    
    // Rota para consulta de CEP foi movida para cima
    
    // Rotas de projetos com rate limiting para operações em massa
    Route::resource('projects', ProjectController::class);
    Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');
    
    Route::middleware('throttle:20,1')->group(function () {
        Route::delete('projects/bulk-destroy', [ProjectController::class, 'bulkDestroy'])->name('projects.bulk-destroy');
        Route::patch('projects/bulk-update-status', [ProjectController::class, 'bulkUpdateStatus'])->name('projects.bulk-update-status');
    });
    
    // Rotas de tarefas
    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::get('tasks-kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
    
    // Rotas de recursos avançados das tarefas com rate limiting
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
        Route::post('tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])->name('tasks.attachments.store');
        Route::post('tasks/{task}/checklist', [TaskChecklistController::class, 'store'])->name('tasks.checklist.store');
    });
    
    Route::delete('tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('tasks.comments.destroy');
    Route::get('tasks/{task}/attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])->name('tasks.attachments.download');
    Route::delete('tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])->name('tasks.attachments.destroy');
    Route::patch('tasks/{task}/checklist/{checklist}', [TaskChecklistController::class, 'update'])->name('tasks.checklist.update');
    Route::delete('tasks/{task}/checklist/{checklist}', [TaskChecklistController::class, 'destroy'])->name('tasks.checklist.destroy');
    
    // Rotas do Kanban
    Route::get('kanban', [KanbanController::class, 'index'])->name('kanban.index');
    Route::patch('kanban/{task}/status', [KanbanController::class, 'updateStatus'])->name('kanban.update-status');
    Route::patch('kanban/{task}/move', [KanbanController::class, 'moveTask'])->name('kanban.move-task');
    Route::post('kanban/reorder', [KanbanController::class, 'reorder'])->name('kanban.reorder');
    Route::post('kanban/quick-create', [KanbanController::class, 'quickCreate'])->name('kanban.quick-create');
    
    // Rotas do Sistema de Suporte
    Route::prefix('support')->name('support.')->group(function () {
        // Gerenciamento de tickets (admin)
        Route::get('admin', [SupportTicketController::class, 'admin'])->name('admin');
        Route::get('tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
        Route::get('tickets/create', [SupportTicketController::class, 'create'])->name('tickets.create');
        Route::post('tickets', [SupportTicketController::class, 'store'])->name('tickets.store');
        Route::get('tickets/{ticket}', [SupportTicketController::class, 'show'])->name('tickets.show');
        Route::patch('tickets/{ticket}', [SupportTicketController::class, 'update'])->name('tickets.update');
        Route::delete('tickets/{ticket}', [SupportTicketController::class, 'destroy'])->name('tickets.destroy');
        
        // Respostas de tickets
        Route::post('tickets/{ticket}/responses', [SupportResponseController::class, 'store'])->name('tickets.responses.store');
        Route::delete('tickets/{ticket}/responses/{response}', [SupportResponseController::class, 'destroy'])->name('tickets.responses.destroy');
        
        // Categorias de suporte
        Route::resource('categories', SupportCategoryController::class)->except(['show']);
    });

    // Financial Routes (separate access control via permissions)
    Route::prefix('financial')->name('financial.')->middleware('permission:financial.view|financial.manage')->group(function () {
        // Dashboard
        Route::get('/', [FinancialDashboardController::class, 'index'])->name('dashboard');
        
        Route::middleware('throttle:10,1')->group(function () {
            Route::get('/export', [FinancialDashboardController::class, 'export'])->name('export');
        });
        
        // Categories
        Route::resource('categories', FinancialCategoryController::class);
        Route::post('categories/{category}/toggle-status', [FinancialCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        
        Route::middleware('throttle:20,1')->group(function () {
            Route::delete('categories/bulk-destroy', [FinancialCategoryController::class, 'bulkDestroy'])->name('categories.bulk-destroy');
            Route::patch('categories/bulk-toggle-status', [FinancialCategoryController::class, 'bulkToggleStatus'])->name('categories.bulk-toggle-status');
        });
        
        // Transactions
        Route::resource('transactions', FinancialTransactionController::class);
        Route::patch('transactions/{transaction}/mark-as-paid', [FinancialTransactionController::class, 'markAsPaid'])->name('transactions.mark-as-paid');
        Route::patch('transactions/{transaction}/mark-as-pending', [FinancialTransactionController::class, 'markAsPending'])->name('transactions.mark-as-pending');
        Route::patch('transactions/{transaction}/cancel', [FinancialTransactionController::class, 'cancel'])->name('transactions.cancel');
        
        Route::middleware('throttle:20,1')->group(function () {
            Route::delete('transactions/bulk-destroy', [FinancialTransactionController::class, 'bulkDestroy'])->name('transactions.bulk-destroy');
            Route::patch('transactions/bulk-update-status', [FinancialTransactionController::class, 'bulkUpdateStatus'])->name('transactions.bulk-update-status');
        });
    });

    // Settings Routes
    Route::prefix('settings')->name('settings.')->middleware('ensure.user.settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::patch('/', [SettingsController::class, 'update'])->name('update');
        Route::post('/reset', [SettingsController::class, 'reset'])->name('reset');    // Removidos: toggles próprios de permissão/role
        // Admin toggles (manage other users)
        Route::middleware('permission:manage users')->group(function () {
            Route::post('/admin/roles/toggle', [SettingsController::class, 'adminToggleUserRole'])->name('admin.roles.toggle');
            Route::post('/admin/permissions/toggle', [SettingsController::class, 'adminToggleUserPermission'])->name('admin.permissions.toggle');
        });
        
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::match(['post', 'patch'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
        
        Route::get('/password', [PasswordController::class, 'edit'])->name('password.edit');
        Route::patch('/password', [PasswordController::class, 'update'])->name('password.update');

    // Admin permissions tab
    Route::get('/permissions', [SettingsController::class, 'permissions'])->name('permissions');

        // Impersonation (visualizar como)
        Route::middleware('permission:manage users')->group(function () {
            Route::post('/impersonate/start', [ImpersonationController::class, 'start'])->name('impersonate.start');
        });
        Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');
    });

    
    // Avatar route for clean URLs with enhanced security
    Route::get('/avatars/{filename}', function ($filename) {
        // Sanitize filename to prevent path traversal
        $filename = basename($filename);
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Validate filename format
        if (!preg_match('/^[a-zA-Z0-9._-]+\.(jpg|jpeg|png|gif|webp)$/i', $filename)) {
            abort(404);
        }
        
        $path = storage_path('app/public/avatars/' . $filename);
        
        // Additional security: ensure the resolved path is within the avatars directory
        $realPath = realpath($path);
        $avatarsDir = realpath(storage_path('app/public/avatars'));
        
        if (!$realPath || !$avatarsDir || !str_starts_with($realPath, $avatarsDir) || !file_exists($path)) {
            abort(404);
        }
        
        return response()->file($path, [
            'Cache-Control' => 'public, max-age=86400',
            'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 86400)
        ]);
    })->middleware('throttle:100,1')->name('avatar.show');
});
