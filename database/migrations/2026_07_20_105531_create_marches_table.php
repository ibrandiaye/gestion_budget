<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('marches', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('intitule');
            $table->foreignId('compte_id')->constrained('comptes')->onDelete('cascade');
            $table->foreignId('budget_id')->constrained('budgets')->onDelete('cascade'); // Representing Annee Exercice
            $table->string('nature')->default('Fonctionnement'); // Fonctionnement / Investissement
            $table->string('type'); // Fourniture / Travaux / Prestation de service / Prestation Intellectuelle
            $table->decimal('montant', 15, 2);
            $table->string('mode_passation')->nullable();
            $table->foreignId('fournisseur_id')->constrained('fournisseurs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('marches');
    }
};
