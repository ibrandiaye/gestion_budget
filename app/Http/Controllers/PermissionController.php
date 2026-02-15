<?php

namespace App\Http\Controllers;

use App\Models\Permission;
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
        
        // Get all roles
        $roles = ['admin', 'service', 'controleur'];
        
        // Get current role permissions
        $rolePermissions = [];
        foreach ($roles as $role) {
            $rolePermissions[$role] = DB::table('role_permission')
                ->where('role', $role)
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
}
