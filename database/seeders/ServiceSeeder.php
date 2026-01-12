<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $services = [
            ['nom' => 'Direction Générale', 'code' => 'DG', 'departement' => 'Administration'],
            ['nom' => 'Chirurgie Générale', 'code' => 'CHIR', 'departement' => 'Médical'],
            ['nom' => 'Pédiatrie', 'code' => 'PED', 'departement' => 'Médical'],
            ['nom' => 'Cardiologie', 'code' => 'CARD', 'departement' => 'Médical'],
            ['nom' => 'Direction Financière', 'code' => 'FIN', 'departement' => 'Administration'],
            ['nom' => 'Ressources Humaines', 'code' => 'RH', 'departement' => 'Administration'],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
