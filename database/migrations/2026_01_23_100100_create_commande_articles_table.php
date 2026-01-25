<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('commande_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commande_id');
            $table->unsignedBigInteger('article_id')->nullable();
            $table->string('designation', 191);
            $table->string('conditionnement', 100)->nullable();
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 15, 2);
            $table->decimal('montant_ht', 15, 2);
            $table->boolean('valide')->default(false);
            $table->timestamps();

            $table->foreign('commande_id')->references('id')->on('commandes')->onDelete('cascade');
            $table->foreign('article_id')->references('id')->on('articles');
        });
    }

    public function down()
    {
        Schema::dropIfExists('commande_articles');
    }
};
