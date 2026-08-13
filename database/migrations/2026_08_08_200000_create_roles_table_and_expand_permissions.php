<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();         // ex: 'admin'
            $table->string('display_name');            // ex: 'Administrateur'
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Insert all roles
        $roles = [
            ['name' => 'admin',                         'display_name' => 'Administrateur',               'description' => 'Accès complet à toutes les fonctionnalités'],
            ['name' => 'service',                       'display_name' => 'Chef de Service',               'description' => 'Gestion des besoins et commandes du service'],
            ['name' => 'controleur',                    'display_name' => 'Contrôleur',                    'description' => 'Contrôle et validation des engagements'],
            ['name' => 'approvisionnement',             'display_name' => 'Agent Approvisionnement',       'description' => 'Gestion des approvisionnements et commandes'],
            ['name' => 'saf',                           'display_name' => 'Chef SAF',                      'description' => 'Validation SAF des commandes et engagements'],
            ['name' => 'comptable_matiere_principal',   'display_name' => 'Comptable Matière Principal',   'description' => 'Réception et gestion des matières (principal)'],
            ['name' => 'comptable_matiere_auxiliaire',  'display_name' => 'Comptable Matière Auxiliaire',  'description' => 'Réception et gestion des matières (auxiliaire)'],
            ['name' => 'directeur',                     'display_name' => 'Directeur',                     'description' => 'Validation finale et supervision'],
            ['name' => 'comptable',                     'display_name' => 'Comptable',                     'description' => 'Gestion comptable et mandats'],
        ];

        $now = now();
        foreach ($roles as $role) {
            DB::table('roles')->insert(array_merge($role, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // 3. Add new permissions (keep existing ones, add missing modules + actions)
        $newPermissions = [
            // Marchés
            ['name' => 'marches.view',       'display_name' => 'Voir les marchés',            'module' => 'marches',        'action' => 'view',       'description' => 'Consulter la liste des marchés'],
            ['name' => 'marches.create',     'display_name' => 'Créer des marchés',           'module' => 'marches',        'action' => 'create',     'description' => 'Créer de nouveaux marchés'],
            ['name' => 'marches.edit',       'display_name' => 'Modifier des marchés',        'module' => 'marches',        'action' => 'edit',       'description' => 'Modifier les marchés existants'],
            ['name' => 'marches.delete',     'display_name' => 'Supprimer des marchés',       'module' => 'marches',        'action' => 'delete',     'description' => 'Supprimer des marchés'],
            
            // PV Réception
            ['name' => 'pv_receptions.view',    'display_name' => 'Voir les PV de réception',      'module' => 'pv_receptions',  'action' => 'view',    'description' => 'Consulter les PV de réception'],
            ['name' => 'pv_receptions.create',  'display_name' => 'Créer des PV de réception',     'module' => 'pv_receptions',  'action' => 'create',  'description' => 'Créer des PV de réception'],
            ['name' => 'pv_receptions.edit',    'display_name' => 'Modifier des PV de réception',  'module' => 'pv_receptions',  'action' => 'edit',    'description' => 'Modifier les PV de réception'],
            ['name' => 'pv_receptions.delete',  'display_name' => 'Supprimer des PV de réception', 'module' => 'pv_receptions',  'action' => 'delete',  'description' => 'Supprimer des PV de réception'],
            
            // Actions de validation - Commandes
            ['name' => 'commandes.soumettre',       'display_name' => 'Soumettre les commandes',              'module' => 'commandes',     'action' => 'soumettre',       'description' => 'Soumettre un bon de commande pour validation'],
            ['name' => 'commandes.valider',         'display_name' => 'Valider les commandes (SAF)',          'module' => 'commandes',     'action' => 'valider',         'description' => 'Valider ou rejeter les articles d\'une commande'],
            ['name' => 'commandes.finaliser',       'display_name' => 'Finaliser la validation des commandes','module' => 'commandes',     'action' => 'finaliser',       'description' => 'Finaliser la validation et créer l\'engagement'],
            
            // Actions de validation - Engagements
            ['name' => 'engagements.valider',       'display_name' => 'Valider les engagements',             'module' => 'engagements',   'action' => 'valider',         'description' => 'Valider un engagement budgétaire'],
            ['name' => 'engagements.rejeter',       'display_name' => 'Rejeter les engagements',             'module' => 'engagements',   'action' => 'rejeter',         'description' => 'Rejeter un engagement budgétaire'],
            
            // Actions de validation - Mandats
            ['name' => 'mandats.valider',           'display_name' => 'Valider les mandats (Directeur)',     'module' => 'mandats',       'action' => 'valider',         'description' => 'Valider un mandat de paiement'],
            ['name' => 'mandats.rejeter',           'display_name' => 'Rejeter les mandats',                 'module' => 'mandats',       'action' => 'rejeter',         'description' => 'Rejeter un mandat de paiement'],
            ['name' => 'mandats.valider_comptable', 'display_name' => 'Valider les mandats (Comptable)',     'module' => 'mandats',       'action' => 'valider_comptable','description' => 'Validation comptable d\'un mandat'],
            
            // Besoins
            ['name' => 'besoins.view',       'display_name' => 'Voir les besoins',            'module' => 'besoins',       'action' => 'view',       'description' => 'Consulter la liste des besoins'],
            ['name' => 'besoins.create',     'display_name' => 'Créer des besoins',           'module' => 'besoins',       'action' => 'create',     'description' => 'Créer des expressions de besoins'],
            ['name' => 'besoins.edit',       'display_name' => 'Modifier des besoins',        'module' => 'besoins',       'action' => 'edit',       'description' => 'Modifier les besoins existants'],
            
            // Budget
            ['name' => 'budget.view',        'display_name' => 'Voir le budget',              'module' => 'budget',        'action' => 'view',       'description' => 'Consulter le budget'],
            ['name' => 'budget.arbitrage',   'display_name' => 'Arbitrer le budget',          'module' => 'budget',        'action' => 'arbitrage',  'description' => 'Arbitrer et valider le budget'],
            ['name' => 'budget.ajustement',  'display_name' => 'Faire des ajustements',       'module' => 'budget',        'action' => 'ajustement', 'description' => 'Effectuer des virements de crédits'],
            
            // Exécution
            ['name' => 'execution.view',     'display_name' => 'Voir les exécutions',         'module' => 'execution',     'action' => 'view',       'description' => 'Consulter les exécutions budgétaires'],
            ['name' => 'execution.create',   'display_name' => 'Saisir des exécutions',       'module' => 'execution',     'action' => 'create',     'description' => 'Saisir de nouvelles exécutions'],
            
            // Reporting
            ['name' => 'reporting.view',     'display_name' => 'Voir les rapports',           'module' => 'reporting',     'action' => 'view',       'description' => 'Consulter les rapports et statistiques'],
            ['name' => 'reporting.export',   'display_name' => 'Exporter les rapports',       'module' => 'reporting',     'action' => 'export',     'description' => 'Exporter les rapports en fichiers'],
            
            // Paramètres
            ['name' => 'users.manage',       'display_name' => 'Gérer les utilisateurs',      'module' => 'parametres',    'action' => 'users',      'description' => 'Créer et gérer les utilisateurs'],
            ['name' => 'services.manage',    'display_name' => 'Gérer les services',          'module' => 'parametres',    'action' => 'services',   'description' => 'Créer et gérer les services'],
            ['name' => 'comptes.manage',     'display_name' => 'Gérer les comptes',           'module' => 'parametres',    'action' => 'comptes',    'description' => 'Créer et gérer les comptes budgétaires'],
            ['name' => 'articles.manage',    'display_name' => 'Gérer les articles',          'module' => 'parametres',    'action' => 'articles',   'description' => 'Créer et gérer les articles'],
            ['name' => 'fournisseurs.manage','display_name' => 'Gérer les fournisseurs',      'module' => 'parametres',    'action' => 'fournisseurs','description' => 'Créer et gérer les fournisseurs'],
            ['name' => 'permissions.manage', 'display_name' => 'Gérer les permissions',       'module' => 'parametres',    'action' => 'permissions', 'description' => 'Configurer les rôles et permissions'],
        ];

        foreach ($newPermissions as $perm) {
            // Only insert if not already existing
            $exists = DB::table('permissions')->where('name', $perm['name'])->exists();
            if (!$exists) {
                DB::table('permissions')->insert(array_merge($perm, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        // 4. Give admin ALL permissions
        $allPermissionIds = DB::table('permissions')->pluck('id')->toArray();
        foreach ($allPermissionIds as $permId) {
            $exists = DB::table('role_permission')
                ->where('role', 'admin')
                ->where('permission_id', $permId)
                ->exists();
            if (!$exists) {
                DB::table('role_permission')->insert([
                    'role' => 'admin',
                    'permission_id' => $permId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 5. Give default permissions to other roles
        $roleDefaults = [
            'service' => [
                'commandes.view', 'commandes.create', 'commandes.soumettre',
                'besoins.view', 'besoins.create', 'besoins.edit',
                'engagements.view', 'mandats.view',
                'marches.view', 'pv_receptions.view',
                'reporting.view',
            ],
            'controleur' => [
                'commandes.view', 'engagements.view', 'engagements.valider', 'engagements.rejeter',
                'mandats.view', 'mandats.create',
                'budget.view', 'reporting.view', 'reporting.export',
            ],
            'saf' => [
                'commandes.view', 'commandes.valider', 'commandes.finaliser',
                'engagements.view', 'mandats.view',
                'marches.view', 'pv_receptions.view',
                'budget.view', 'reporting.view',
            ],
            'approvisionnement' => [
                'commandes.view', 'commandes.create', 'commandes.edit', 'commandes.soumettre',
                'marches.view', 'marches.create', 'marches.edit',
                'pv_receptions.view', 'pv_receptions.create',
                'besoins.view',
            ],
            'comptable_matiere_principal' => [
                'pv_receptions.view', 'pv_receptions.create', 'pv_receptions.edit',
                'commandes.view', 'marches.view',
                'reporting.view',
            ],
            'comptable_matiere_auxiliaire' => [
                'pv_receptions.view', 'pv_receptions.create',
                'commandes.view', 'marches.view',
            ],
            'directeur' => [
                'commandes.view', 'engagements.view', 'engagements.valider', 'engagements.rejeter',
                'mandats.view', 'mandats.valider', 'mandats.rejeter',
                'marches.view', 'pv_receptions.view',
                'budget.view', 'budget.arbitrage',
                'reporting.view', 'reporting.export',
            ],
            'comptable' => [
                'mandats.view', 'mandats.valider_comptable',
                'engagements.view',
                'execution.view', 'execution.create',
                'reporting.view', 'reporting.export',
            ],
        ];

        foreach ($roleDefaults as $role => $permNames) {
            foreach ($permNames as $permName) {
                $permId = DB::table('permissions')->where('name', $permName)->value('id');
                if ($permId) {
                    $exists = DB::table('role_permission')
                        ->where('role', $role)
                        ->where('permission_id', $permId)
                        ->exists();
                    if (!$exists) {
                        DB::table('role_permission')->insert([
                            'role' => $role,
                            'permission_id' => $permId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
        // Don't remove the permissions/role_permission that were added
    }
};
