<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fournisseurs', function (Blueprint $table) {
            $table->string('telephone2', 50)->nullable()->after('telephone');
            $table->string('banque', 191)->nullable()->after('ninea');
            $table->string('numero_compte', 50)->nullable()->after('banque');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fournisseurs', function (Blueprint $table) {
            $table->dropColumn(['telephone2', 'banque', 'numero_compte']);
        });
    }
};
