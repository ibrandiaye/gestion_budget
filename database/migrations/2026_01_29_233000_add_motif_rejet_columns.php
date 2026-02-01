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
        Schema::table('commandes', function (Blueprint $table) {
            $table->text('motif_rejet')->nullable()->after('statut');
        });

        Schema::table('engagements', function (Blueprint $table) {
            $table->text('motif_rejet')->nullable()->after('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn('motif_rejet');
        });

        Schema::table('engagements', function (Blueprint $table) {
            $table->dropColumn('motif_rejet');
        });
    }
};
