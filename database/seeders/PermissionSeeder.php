<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions
        $permissions = [
            // Commandes
            [
                'name' => 'commandes.view',
                'display_name' => 'Voir les commandes',
                'module' => 'commandes',
                'action' => 'view',
                'description' => 'Permet de consulter la liste des commandes'
            ],
            [
                'name' => 'commandes.create',
                'display_name' => 'Créer des commandes',
                'module' => 'commandes',
                'action' => 'create',
                'description' => 'Permet de créer de nouvelles commandes'
            ],
            [
                'name' => 'commandes.edit',
                'display_name' => 'Modifier des commandes',
                'module' => 'commandes',
                'action' => 'edit',
                'description' => 'Permet de modifier les commandes existantes'
            ],
            [
                'name' => 'commandes.delete',
                'display_name' => 'Supprimer des commandes',
                'module' => 'commandes',
                'action' => 'delete',
                'description' => 'Permet de supprimer des commandes'
            ],
            
            // Engagements
            [
                'name' => 'engagements.view',
                'display_name' => 'Voir les engagements',
                'module' => 'engagements',
                'action' => 'view',
                'description' => 'Permet de consulter la liste des engagements'
            ],
            [
                'name' => 'engagements.create',
                'display_name' => 'Créer des engagements',
                'module' => 'engagements',
                'action' => 'create',
                'description' => 'Permet de créer de nouveaux engagements'
            ],
            [
                'name' => 'engagements.edit',
                'display_name' => 'Modifier des engagements',
                'module' => 'engagements',
                'action' => 'edit',
                'description' => 'Permet de modifier les engagements existants'
            ],
            [
                'name' => 'engagements.delete',
                'display_name' => 'Supprimer des engagements',
                'module' => 'engagements',
                'action' => 'delete',
                'description' => 'Permet de supprimer des engagements'
            ],
            
            // Mandats
            [
                'name' => 'mandats.view',
                'display_name' => 'Voir les mandats',
                'module' => 'mandats',
                'action' => 'view',
                'description' => 'Permet de consulter la liste des mandats'
            ],
            [
                'name' => 'mandats.create',
                'display_name' => 'Créer des mandats',
                'module' => 'mandats',
                'action' => 'create',
                'description' => 'Permet de créer de nouveaux mandats'
            ],
            [
                'name' => 'mandats.edit',
                'display_name' => 'Modifier des mandats',
                'module' => 'mandats',
                'action' => 'edit',
                'description' => 'Permet de modifier les mandats existants'
            ],
            [
                'name' => 'mandats.delete',
                'display_name' => 'Supprimer des mandats',
                'module' => 'mandats',
                'action' => 'delete',
                'description' => 'Permet de supprimer des mandats'
            ],
        ];

        // Insert permissions
        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Assign all permissions to admin role by default
        $adminPermissions = Permission::all();
        foreach ($adminPermissions as $permission) {
            DB::table('role_permission')->insert([
                'role' => 'admin',
                'permission_id' => $permission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Permissions created and assigned to admin role successfully!');
    }
}
