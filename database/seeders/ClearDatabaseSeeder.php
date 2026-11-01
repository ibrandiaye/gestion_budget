<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearDatabaseSeeder extends Seeder
{
    /**
     * Vide les tables : comptes (et dotations/ajustements liés), commandes,
     * marchés, réceptions, signataires, engagements et mandats.
     *
     * Commande : php artisan db:seed --class=ClearDatabaseSeeder
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        $tables = [
            // 1. Mandats & Engagements
            'mandats',
            'engagements',

            // 2. Réceptions (PV de réception) & Signataires
            'pv_reception_signataire',
            'pv_reception_articles',
            'pv_receptions',
            'signataires',

            // 3. Commandes
            'commande_articles',
            'commandes',

            // 4. Marchés
            'marche_articles',
            'marches',

            // 5. Comptes & dotations / ajustements liés
            'dotations_comptes',
            'ajustements',
            'lignes_budget',
            'comptes',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->command?->info("Table '{$table}' vidée avec succès.");
            }
        }

        Schema::enableForeignKeyConstraints();

        $this->command?->info('Nettoyage des tables terminé avec succès.');
    }
}
