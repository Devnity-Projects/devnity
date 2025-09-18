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
        return $user->can('clients.view') || $user->can('clients.manage');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Client $client): bool
    {
        return $user->can('clients.view') || $user->can('clients.manage');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('clients.create') || $user->can('clients.manage');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Client $client): bool
    {
        return $user->can('clients.update') || $user->can('clients.manage');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        return $user->can('clients.delete') || $user->can('clients.manage');
    }

    /**
     * Determine whether the user can perform bulk operations.
     */
    public function bulkOperations(User $user): bool
    {
        return $user->can('clients.manage');
    }

    /**
     * Determine whether the user can export data.
     */
    public function export(User $user): bool
    {
        return $user->can('clients.export') || $user->can('clients.manage');
    }
}