<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * AuthorizesFields
 *
 * Tiny helper to filter arrays (eg. Resource output) based on user permissions.
 * Define a $fieldPermissions map in your Resource using this trait.
 * Keys are field names; values are permission strings or arrays of permissions (any matches).
 */
trait AuthorizesFields
{
    /**
     * Filter an associative array of fields by permission map.
     *
     * @param  array<string,mixed>  $data
     * @param  array<string,string|array<int,string>>  $fieldPermissions
     * @param  Request|null $request
     * @return array<string,mixed>
     */
    protected function filterFieldsByPermissions(array $data, array $fieldPermissions, ?Request $request = null): array
    {
        $user = ($request?->user()) ?? auth()->user();
        if (!$user) {
            // If unauthenticated, drop all fields that require permissions
            foreach ($fieldPermissions as $field => $perm) {
                unset($data[$field]);
            }
            return $data;
        }

        foreach ($fieldPermissions as $field => $permissions) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $permissions = (array) $permissions; // accept string or array
            $allowed = false;
            foreach ($permissions as $permission) {
                if ($user->can($permission)) {
                    $allowed = true;
                    break;
                }
            }

            if (!$allowed) {
                unset($data[$field]);
            }
        }

        return $data;
    }
}
