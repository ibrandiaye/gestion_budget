<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pv_reception_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pv_reception_id')->constrained('pv_receptions')->onDelete('cascade');
            $table->foreignId('commande_article_id')->constrained('commande_articles')->onDelete('cascade');
            $table->integer('quantite_receptionnee');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pv_reception_articles');
    }
};
