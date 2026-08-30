<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$compte = App\Models\Compte::whereHas('articles')->first();
$controller = new App\Http\Controllers\FournisseurController();
$response = $controller->pourCompte($compte);
echo json_encode($response->getData());
