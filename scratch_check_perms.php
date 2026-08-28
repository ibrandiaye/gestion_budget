<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'admin')->first();
echo "Admin role: " . $user->role . "\n";
echo "Has commandes.edit: " . ($user->hasPermission('commandes.edit') ? 'yes' : 'no') . "\n";
echo "Has commandes.delete: " . ($user->hasPermission('commandes.delete') ? 'yes' : 'no') . "\n";

// Let's check how many permissions admin has
$adminPerms = \DB::table('role_permission')->where('role', 'admin')->count();
echo "Admin total perms: " . $adminPerms . "\n";

$allPerms = \DB::table('permissions')->count();
echo "Total perms in DB: " . $allPerms . "\n";

$editPerm = \DB::table('permissions')->where('name', 'commandes.edit')->first();
echo "Edit perm id: " . ($editPerm ? $editPerm->id : 'null') . "\n";
