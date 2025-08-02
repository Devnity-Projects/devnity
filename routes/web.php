<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\CepController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\PasswordController;
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
    
    // Settings Routes
    Route::prefix('settings')->name('settings.')->middleware('ensure.user.settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::patch('/', [SettingsController::class, 'update'])->name('update');
        Route::post('/reset', [SettingsController::class, 'reset'])->name('reset');
        
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
        
        Route::get('/password', [PasswordController::class, 'edit'])->name('password.edit');
        Route::patch('/password', [PasswordController::class, 'update'])->name('password.update');
    });
});
