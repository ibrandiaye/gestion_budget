<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 191);
            $table->string('telephone', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('adresse', 191)->nullable();
            $table->string('ninea', 50)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fournisseurs');
    }
};
