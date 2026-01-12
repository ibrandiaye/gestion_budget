<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compte;

class CompteSeeder extends Seeder
{
    public function run()
    {
        $comptes = [
            // Fonctionnement
            ['numero' => '6000', 'libelle' => 'Charges de personnel', 'type' => 'Fonctionnement'],
            ['numero' => '6011', 'libelle' => 'Salaires du personnel permanent', 'type' => 'Fonctionnement', 'parent_num' => '6000'],
            ['numero' => '6012', 'libelle' => 'Primes et indemnités', 'type' => 'Fonctionnement', 'parent_num' => '6000'],

            ['numero' => '6100', 'libelle' => 'Fournitures et Entretien', 'type' => 'Fonctionnement'],
            ['numero' => '6111', 'libelle' => 'Fournitures de bureau', 'type' => 'Fonctionnement', 'parent_num' => '6100'],
            ['numero' => '6112', 'libelle' => 'Produits pharmaceutiques', 'type' => 'Fonctionnement', 'parent_num' => '6100'],
            ['numero' => '6113', 'libelle' => 'Entretien locaux', 'type' => 'Fonctionnement', 'parent_num' => '6100'],

            // Investissement
            ['numero' => '2000', 'libelle' => 'Investissements', 'type' => 'Investissement'],
            ['numero' => '2111', 'libelle' => 'Matériel médical', 'type' => 'Investissement', 'parent_num' => '2000'],
            ['numero' => '2112', 'libelle' => 'Matériel informatique', 'type' => 'Investissement', 'parent_num' => '2000'],
        ];

        foreach ($comptes as $data) {
            $parent = null;
            if (isset($data['parent_num'])) {
                $parent = Compte::where('numero', $data['parent_num'])->first();
                unset($data['parent_num']);
            }

            $compte = Compte::create(array_merge($data, ['parent_id' => $parent ? $parent->id : null]));
        }
    }
}
