<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->string('source')->default('Autre')->after('reference'); // Marché, Facture Pro-forma, Autre
            $table->foreignId('marche_id')->nullable()->after('source')->constrained('marches')->onDelete('set null');
            $table->string('reference_source')->nullable()->after('marche_id');
            // the status column might already exist, we will just use it differently
        });
    }

    public function down()
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropForeign(['marche_id']);
            $table->dropColumn(['source', 'marche_id', 'reference_source']);
        });
    }
};
