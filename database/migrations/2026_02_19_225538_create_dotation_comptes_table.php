<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Compte;
use App\Models\Budget;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dotations_comptes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->onDelete('cascade');
            $table->foreignId('compte_id')->constrained('comptes')->onDelete('cascade');
            $table->decimal('dotation_initiale', 15, 2)->default(0);
            $table->decimal('prix_unitaire_ref', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['budget_id', 'compte_id']);
        });

        // Migrate existing data
        $activeBudget = Budget::where('statut', 'actif')->orderByDesc('annee')->first();

        // If no active budget, try to find the latest one, or create one if absolutely necessary (but better to skip)
        if (!$activeBudget) {
            $activeBudget = Budget::orderByDesc('annee')->first();
        }

        if ($activeBudget) {
            $comptes = Compte::where('dotation_initiale', '>', 0)
                ->orWhere('prix_unitaire_ref', '>', 0)
                ->get();

            foreach ($comptes as $compte) {
                DB::table('dotations_comptes')->insert([
                    'budget_id' => $activeBudget->id,
                    'compte_id' => $compte->id,
                    'dotation_initiale' => $compte->dotation_initiale,
                    'prix_unitaire_ref' => $compte->prix_unitaire_ref,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dotations_comptes');
    }
};
