<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pv_reception_signataire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pv_reception_id')->constrained('pv_receptions')->cascadeOnDelete();
            $table->foreignId('signataire_id')->constrained('signataires')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pv_reception_signataire');
    }
};
