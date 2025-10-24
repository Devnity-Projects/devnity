<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\Task;
use App\Models\SystemSetting;
use App\Observers\TaskObserver;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            // Força o uso de HTTPS em URLs geradas pelo Laravel
            URL::forceScheme('https');
        }

        // Registrar observers
        Task::observe(TaskObserver::class);

        // Compartilhar configurações de menu com todas as views Inertia
        Inertia::share([
            'menuVisibility' => function () {
                return SystemSetting::get('menu_visibility', [
                    'tasks' => true,
                    'projects' => true,
                    'clients' => true,
                    'financial' => false,
                    'support' => false,
                ]);
            },
        ]);
    }
}
