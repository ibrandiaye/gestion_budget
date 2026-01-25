<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('engagements', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50)->unique();
            $table->unsignedBigInteger('commande_id');
            $table->date('date');
            $table->string('objet', 191);
            $table->string('nature', 100)->nullable();
            $table->decimal('montant', 15, 2);
            $table->string('nom_fournisseur', 191);
            $table->string('sonafel', 100)->nullable();
            // Situation des engagements
            $table->decimal('credits_ouverts', 15, 2)->default(0);
            $table->decimal('modifications_credits', 15, 2)->default(0);
            $table->decimal('engagements_anterieurs', 15, 2)->default(0);
            $table->decimal('disponible_avant', 15, 2)->default(0);
            $table->decimal('depenses_jour', 15, 2)->default(0);
            $table->decimal('nouveau_disponible', 15, 2)->default(0);
            $table->string('statut', 20)->default('cree');
            $table->unsignedBigInteger('valide_par')->nullable();
            $table->timestamp('valide_at')->nullable();
            $table->timestamps();

            $table->foreign('commande_id')->references('id')->on('commandes');
            $table->foreign('valide_par')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('engagements');
    }
};
