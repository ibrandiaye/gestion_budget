<?php

use Illuminate\Support\Facades\DB;
use App\Models\Permission;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$editPerm = DB::table('permissions')->where('name', 'commandes.edit')->first();
$deletePerm = DB::table('permissions')->where('name', 'commandes.delete')->first();

if ($editPerm && $deletePerm) {
    $now = now();
    
    // Add to service
    DB::table('role_permission')->insertOrIgnore([
        ['role' => 'service', 'permission_id' => $editPerm->id, 'created_at' => $now, 'updated_at' => $now],
        ['role' => 'service', 'permission_id' => $deletePerm->id, 'created_at' => $now, 'updated_at' => $now],
    ]);

    // Add delete to approvisionnement
    DB::table('role_permission')->insertOrIgnore([
        ['role' => 'approvisionnement', 'permission_id' => $deletePerm->id, 'created_at' => $now, 'updated_at' => $now],
    ]);
    
    echo "Permissions ajoutées aux rôles 'service' et 'approvisionnement'.\n";
} else {
    echo "Permissions introuvables.\n";
}
