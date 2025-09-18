<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Task;
use App\Policies\ClientPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Client::class => ClientPolicy::class,
        Task::class => TaskPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Definir gates personalizados se necessário
        Gate::define('bulk-operations', function ($user) {
            // Adicione lógica para verificar se o usuário pode fazer operações em massa
            return true; // Por enquanto, todos usuários autenticados podem
        });

        Gate::define('export-data', function ($user) {
            // Adicione lógica para verificar se o usuário pode exportar dados
            return true; // Por enquanto, todos usuários autenticados podem
        });
    }
}