<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Todos usuários autenticados podem ver clientes
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Client $client): bool
    {
        // Por enquanto, todos usuários autenticados podem ver qualquer cliente
        // Você pode implementar lógica mais específica aqui se necessário
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Todos usuários autenticados podem criar clientes
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Client $client): bool
    {
        // Por enquanto, todos usuários autenticados podem editar qualquer cliente
        // Você pode implementar lógica baseada em roles/permissions aqui
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        // Por enquanto, todos usuários autenticados podem deletar qualquer cliente
        // Você pode implementar lógica baseada em roles/permissions aqui
        return true;
    }

    /**
     * Determine whether the user can perform bulk operations.
     */
    public function bulkOperations(User $user): bool
    {
        // Apenas usuários autenticados podem fazer operações em massa
        // Você pode adicionar verificação de roles aqui se necessário
        return true;
    }

    /**
     * Determine whether the user can export data.
     */
    public function export(User $user): bool
    {
        // Apenas usuários autenticados podem exportar dados
        return true;
    }
}