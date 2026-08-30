<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Permissions à accorder
$permsToGrant = ['pv_receptions.edit', 'pv_receptions.delete'];

// S'assurer que les permissions existent
foreach ($permsToGrant as $permName) {
    $exists = DB::table('permissions')->where('name', $permName)->exists();
    if (!$exists) {
        DB::table('permissions')->insert(['name' => $permName, 'created_at' => now(), 'updated_at' => now()]);
        echo "Créé permission: $permName\n";
    }
}

// Récupérer les IDs des permissions
$permIds = DB::table('permissions')->whereIn('name', $permsToGrant)->pluck('id', 'name');

// Rôles concernés
$rolesToGrant = ['admin', 'approvisionnement', 'service'];

foreach ($rolesToGrant as $role) {
    foreach ($permsToGrant as $permName) {
        $permId = $permIds[$permName];
        $exists = DB::table('role_permission')
            ->where('role', $role)
            ->where('permission_id', $permId)
            ->exists();

        if (!$exists) {
            DB::table('role_permission')->insert([
                'role'          => $role,
                'permission_id' => $permId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            echo "  ✅ $permName ajoutée au rôle '$role'\n";
        } else {
            echo "  ℹ️  $permName déjà présente sur '$role'\n";
        }
    }
}

// Afficher le résultat
echo "\n=== Vérification finale ===\n";
foreach ($rolesToGrant as $role) {
    $perms = DB::table('role_permission')
        ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
        ->where('role_permission.role', $role)
        ->where('permissions.name', 'like', 'pv_receptions.%')
        ->pluck('permissions.name')
        ->join(', ');
    echo "  [$role] : $perms\n";
}
echo "\nDone.\n";
