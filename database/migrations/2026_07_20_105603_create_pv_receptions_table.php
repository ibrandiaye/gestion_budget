<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pv_receptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->onDelete('cascade');
            $table->date('date_reception');
            $table->decimal('montant_total', 15, 2);
            $table->decimal('tva', 5, 2)->default(0);
            $table->decimal('montant_ttc', 15, 2);
            $table->json('signataires')->nullable();
            $table->json('pieces_justificatives')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pv_receptions');
    }
};
