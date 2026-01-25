<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('fournisseur_article_prix', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fournisseur_id');
            $table->unsignedBigInteger('article_id');
            $table->decimal('prix', 15, 2);
            $table->timestamps();
            
            $table->foreign('fournisseur_id')->references('id')->on('fournisseurs')->onDelete('cascade');
            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            $table->unique(['fournisseur_id', 'article_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('fournisseur_article_prix');
    }
};
