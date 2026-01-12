<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ligne_budget_id')->constrained('lignes_budget')->cascadeOnDelete();
            $table->decimal('montant', 15, 2);
            $table->date('date');
            $table->string('type')->default('engagement'); // engagement, paiement
            $table->text('description')->nullable();
            $table->string('reference_document')->nullable();
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
        Schema::dropIfExists('executions');
    }
};
