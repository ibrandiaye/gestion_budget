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
            $table->string('libelle')->after('compte_id');
            $table->integer('quantite')->default(1)->after('libelle');
            $table->decimal('montant_demande', 15, 2)->nullable()->change();
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
            $table->dropColumn(['libelle', 'quantite']);
            $table->decimal('montant_demande', 15, 2)->nullable(false)->change();
        });
    }
};
