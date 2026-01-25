<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50)->unique();
            $table->date('date');
            $table->unsignedBigInteger('compte_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('budget_id');
            $table->string('fournisseur', 191)->nullable();
            $table->string('reference', 191)->nullable();
            $table->string('caisse', 100)->default('Caisse avance');
            $table->decimal('montant_tva', 15, 2)->default(0);
            $table->decimal('montant_ttc', 15, 2)->default(0);
            $table->string('arrete_somme', 191)->nullable();
            $table->string('statut', 20)->default('brouillon');
            $table->unsignedBigInteger('valide_par')->nullable();
            $table->timestamp('valide_at')->nullable();
            $table->timestamps();

            $table->foreign('compte_id')->references('id')->on('comptes');
            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('budget_id')->references('id')->on('budgets');
            $table->foreign('valide_par')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('commandes');
    }
};
