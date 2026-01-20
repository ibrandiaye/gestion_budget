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
        Schema::table('comptes', function (Blueprint $table) {
            $table->decimal('dotation_initiale', 15, 2)->default(0)->after('type');
            $table->decimal('prix_unitaire_ref', 15, 2)->default(0)->after('dotation_initiale');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropColumn(['dotation_initiale', 'prix_unitaire_ref']);
        });
    }
};
