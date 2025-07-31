<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\CepController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
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
    
    // Rotas de tarefas
    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::get('tasks-kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
});
