<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('marche_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marche_id')->constrained('marches')->onDelete('cascade');
            $table->string('designation');
            $table->string('conditionnement')->nullable();
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 15, 2);
            $table->decimal('prix_total', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('marche_articles');
    }
};
