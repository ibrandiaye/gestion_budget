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
        Schema::table('besoins', function (Blueprint $table) {
            $table->decimal('prix_unitaire', 15, 2)->nullable()->after('quantite');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('besoins', function (Blueprint $table) {
            $table->dropColumn('prix_unitaire');
        });
    }
};
