<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $roles = Role::with('permissions')->get();
        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role->load('permissions'),
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
        ]);

        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.roles.index')->with('success', 'Permissões atualizadas.');
    }
}
