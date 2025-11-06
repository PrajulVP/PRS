<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\PermissionCategory;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('admin.permissions.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        if ($role->name === 'superadmin') {
            return redirect()->route('admin.permissions.index')->with('error', 'Cannot edit permissions for the superadmin role.');
        }

        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode(' ', $permission->name)[0];
        });

        return view('admin.permissions.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'superadmin') {
            return redirect()->route('admin.permissions.index')->with('error', 'Cannot edit permissions for the superadmin role.');
        }

        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);

        return redirect()->route('admin.permissions.edit', $role)->with('success', 'Permissions updated successfully!');
    }
}
