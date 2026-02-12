<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compte_parents', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 5)->comment('Numéro (ex: 10, 20)');
            $table->string('libelle')->comment('Nom du compte parent');
            $table->string('type')->nullable()->comment('Type technique (fonctionnement/investissement)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compte_parents');
    }
};
