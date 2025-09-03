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
use App\Http\Controllers\FinancialDashboardController;
use App\Http\Controllers\FinancialCategoryController;
use App\Http\Controllers\FinancialTransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Auth/Login'))->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // API Routes
    Route::get('/api/cep/{zipcode}', [CepController::class, 'search'])->name('api.cep.search');
    
    // Rotas de clientes
    Route::resource('clients', ClientController::class);
    Route::post('clients/{client}/toggle-status', [ClientController::class, 'toggleStatus'])->name('clients.toggle-status');
    Route::delete('clients/bulk-destroy', [ClientController::class, 'bulkDestroy'])->name('clients.bulk-destroy');
    Route::patch('clients/bulk-toggle-status', [ClientController::class, 'bulkToggleStatus'])->name('clients.bulk-toggle-status');
    Route::get('clients-export', [ClientController::class, 'export'])->name('clients.export');
    
    // Rota para consulta de CEP
    Route::get('cep/{cep}', [CepController::class, 'search'])->name('cep.search');
    
    // Rotas de projetos
    Route::resource('projects', ProjectController::class);
    Route::delete('projects/bulk-destroy', [ProjectController::class, 'bulkDestroy'])->name('projects.bulk-destroy');
    Route::patch('projects/bulk-update-status', [ProjectController::class, 'bulkUpdateStatus'])->name('projects.bulk-update-status');
    Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');
    
    // Rotas de tarefas
    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::get('tasks-kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
    
    // Rotas de recursos avançados das tarefas
    Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
    Route::delete('tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('tasks.comments.destroy');
    
    Route::post('tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])->name('tasks.attachments.store');
    Route::get('tasks/{task}/attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])->name('tasks.attachments.download');
    Route::delete('tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])->name('tasks.attachments.destroy');
    
    Route::post('tasks/{task}/checklist', [TaskChecklistController::class, 'store'])->name('tasks.checklist.store');
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

    // Financial Routes
    Route::prefix('financial')->name('financial.')->group(function () {
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
    });

    // Financial Routes
    Route::prefix('financial')->name('financial.')->group(function () {
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
});
