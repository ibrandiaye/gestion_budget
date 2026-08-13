<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Display the permission management interface
     */
    public function index()
    {
        $permissions = Permission::orderBy('module')->orderBy('action')->get();
        
        // Group permissions by module
        $groupedPermissions = $permissions->groupBy('module');
        
        // Get all roles dynamically from DB
        $roles = Role::orderBy('id')->get();
        
        // Get current role permissions
        $rolePermissions = [];
        foreach ($roles as $role) {
            $rolePermissions[$role->name] = DB::table('role_permission')
                ->where('role', $role->name)
                ->pluck('permission_id')
                ->toArray();
        }
        
        return view('permissions.index', compact('groupedPermissions', 'roles', 'rolePermissions'));
    }

    /**
     * Update permissions for a role
     */
    public function updateRolePermissions(Request $request, $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        // Remove all existing permissions for this role
        DB::table('role_permission')->where('role', $role)->delete();

        // Add new permissions
        if ($request->has('permissions')) {
            foreach ($request->permissions as $permissionId) {
                DB::table('role_permission')->insert([
                    'role' => $role,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return back()->with('success', 'Permissions mises à jour avec succès pour le rôle ' . $role);
    }

    /**
     * Store a new role
     */
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        Role::create([
            'name' => strtolower(str_replace(' ', '_', $request->name)),
            'display_name' => $request->display_name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Rôle "' . $request->display_name . '" créé avec succès.');
    }

    /**
     * Delete a role
     */
    public function destroyRole(Role $role)
    {
        // Don't allow deletion of admin role
        if ($role->name === 'admin') {
            return back()->withErrors(['role' => 'Le rôle Administrateur ne peut pas être supprimé.']);
        }

        // Remove all permissions for this role
        DB::table('role_permission')->where('role', $role->name)->delete();
        
        $role->delete();

        return back()->with('success', 'Rôle supprimé avec succès.');
    }

    /**
     * Store a new permission
     */
    public function storePermission(Request $request)
    {
        $request->validate([
            'module' => 'required|string|max:50',
            'action' => 'required|string|max:50',
            'display_name' => 'required|string|max:150',
            'description' => 'nullable|string|max:255',
        ]);

        $name = strtolower($request->module) . '.' . strtolower($request->action);

        if (Permission::where('name', $name)->exists()) {
            return back()->withErrors(['permission' => "La permission \"{$name}\" existe déjà."]);
        }

        Permission::create([
            'name' => $name,
            'display_name' => $request->display_name,
            'module' => strtolower($request->module),
            'action' => strtolower($request->action),
            'description' => $request->description,
        ]);

        return back()->with('success', 'Permission "' . $request->display_name . '" créée avec succès.');
    }

    /**
     * Delete a permission
     */
    public function destroyPermission(Permission $permission)
    {
        // Remove from all roles
        DB::table('role_permission')->where('permission_id', $permission->id)->delete();
        
        $permission->delete();

        return back()->with('success', 'Permission supprimée avec succès.');
    }
}
