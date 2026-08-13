<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'controleur')->first();
if (!$user) {
    echo "No controleur found\n";
} else {
    echo "hasPermission: " . ($user->hasPermission('commandes.view') ? 'YES' : 'NO') . "\n";
    echo "can: " . ($user->can('commandes.view') ? 'YES' : 'NO') . "\n";
}
