<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('mandats', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50)->unique();
            $table->unsignedBigInteger('engagement_id');
            $table->date('date');
            $table->string('objet_depense', 191);
            $table->decimal('montant', 15, 2);
            $table->string('montant_lettres', 191)->nullable();
            $table->text('pieces_justificatives')->nullable();
            $table->string('beneficiaire', 191);
            // Imputation budgétaire
            $table->string('compte_principal', 50)->nullable();
            $table->string('compte_imputation', 50)->nullable();
            $table->string('libelle_imputation', 191)->nullable();
            // Règlement
            $table->string('mode_reglement', 50)->nullable();
            $table->string('etablissement', 100)->nullable();
            $table->string('numero_compte', 50)->nullable();
            $table->string('rib', 50)->nullable();
            // Double validation
            $table->string('statut', 30)->default('cree');
            $table->unsignedBigInteger('valide_directeur_par')->nullable();
            $table->timestamp('valide_directeur_at')->nullable();
            $table->unsignedBigInteger('valide_comptable_par')->nullable();
            $table->timestamp('valide_comptable_at')->nullable();
            $table->text('motif_refus')->nullable();
            $table->boolean('requisition')->default(false);
            $table->timestamps();

            $table->foreign('engagement_id')->references('id')->on('engagements');
            $table->foreign('valide_directeur_par')->references('id')->on('users');
            $table->foreign('valide_comptable_par')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mandats');
    }
};
